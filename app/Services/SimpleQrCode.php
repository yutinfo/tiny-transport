<?php

namespace App\Services;

class SimpleQrCode
{
    private const ALPHANUMERIC_CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';

    private const VERSION_CONFIG = [
        1 => ['data' => 19, 'ecc' => 7, 'align' => []],
        2 => ['data' => 34, 'ecc' => 10, 'align' => [6, 18]],
        3 => ['data' => 55, 'ecc' => 15, 'align' => [6, 22]],
        4 => ['data' => 80, 'ecc' => 20, 'align' => [6, 26]],
    ];

    private static array $gfExp = [];
    private static array $gfLog = [];

    public function svg(string $value, int $scale = 5, int $margin = 2): string
    {
        $matrix = $this->matrix($value);
        $moduleCount = count($matrix);
        $size = ($moduleCount + ($margin * 2)) * $scale;
        $rects = [];

        foreach ($matrix as $row => $columns) {
            foreach ($columns as $column => $dark) {
                if (! $dark) {
                    continue;
                }

                $x = ($column + $margin) * $scale;
                $y = ($row + $margin) * $scale;
                $rects[] = '<rect x="' . $x . '" y="' . $y . '" width="' . $scale . '" height="' . $scale . '"/>';
            }
        }

        return '<svg class="qr-code" xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '" role="img" aria-label="QR ' . e($value) . '" data-qr-value="' . e($value) . '"><rect width="100%" height="100%" fill="#fff"/><g fill="#000">' . implode('', $rects) . '</g></svg>';
    }

    private function matrix(string $value): array
    {
        $mode = $this->isAlphanumeric($value) ? 'alphanumeric' : 'byte';
        $version = $this->chooseVersion($value, $mode);
        $config = self::VERSION_CONFIG[$version];
        $dataCodewords = $this->dataCodewords($value, $mode, $version, $config['data']);
        $eccCodewords = $this->reedSolomonRemainder($dataCodewords, $config['ecc']);
        $codewords = array_merge($dataCodewords, $eccCodewords);
        $bits = [];

        foreach ($codewords as $codeword) {
            $this->appendBits($bits, $codeword, 8);
        }

        return $this->drawMatrix($version, $config['align'], $bits);
    }

    private function chooseVersion(string $value, string $mode): int
    {
        foreach (self::VERSION_CONFIG as $version => $config) {
            $capacityBits = $config['data'] * 8;
            $requiredBits = $mode === 'alphanumeric'
                ? 4 + 9 + (int) (floor(strlen($value) / 2) * 11) + ((strlen($value) % 2) * 6)
                : 4 + 8 + (strlen($value) * 8);

            if ($requiredBits <= $capacityBits) {
                return $version;
            }
        }

        throw new \InvalidArgumentException('Parcel code is too long for the local QR generator.');
    }

    private function dataCodewords(string $value, string $mode, int $version, int $capacity): array
    {
        $bits = [];

        if ($mode === 'alphanumeric') {
            $this->appendBits($bits, 0b0010, 4);
            $this->appendBits($bits, strlen($value), 9);

            for ($index = 0; $index < strlen($value); $index += 2) {
                $first = strpos(self::ALPHANUMERIC_CHARS, $value[$index]);

                if ($index + 1 < strlen($value)) {
                    $second = strpos(self::ALPHANUMERIC_CHARS, $value[$index + 1]);
                    $this->appendBits($bits, ($first * 45) + $second, 11);
                    continue;
                }

                $this->appendBits($bits, $first, 6);
            }
        } else {
            $this->appendBits($bits, 0b0100, 4);
            $this->appendBits($bits, strlen($value), $version < 10 ? 8 : 16);

            foreach (array_values(unpack('C*', $value)) as $byte) {
                $this->appendBits($bits, $byte, 8);
            }
        }

        $capacityBits = $capacity * 8;
        $terminator = min(4, $capacityBits - count($bits));
        $this->appendBits($bits, 0, $terminator);

        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $codewords = [];
        foreach (array_chunk($bits, 8) as $chunk) {
            $codeword = 0;
            foreach ($chunk as $bit) {
                $codeword = ($codeword << 1) | $bit;
            }
            $codewords[] = $codeword;
        }

        $pads = [0xEC, 0x11];
        $padIndex = 0;

        while (count($codewords) < $capacity) {
            $codewords[] = $pads[$padIndex % 2];
            $padIndex++;
        }

        return $codewords;
    }

    private function drawMatrix(int $version, array $alignmentCenters, array $dataBits): array
    {
        $size = 17 + ($version * 4);
        $modules = array_fill(0, $size, array_fill(0, $size, false));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        $this->drawFinder($modules, $reserved, 0, 0);
        $this->drawFinder($modules, $reserved, $size - 7, 0);
        $this->drawFinder($modules, $reserved, 0, $size - 7);
        $this->drawAlignmentPatterns($modules, $reserved, $alignmentCenters);
        $this->drawTimingPatterns($modules, $reserved);
        $this->setModule($modules, $reserved, $size - 8, 8, true);
        $this->drawFormatBits($modules, $reserved, 0);

        $bitIndex = 0;
        $upward = true;

        for ($right = $size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right = 5;
            }

            for ($vertical = 0; $vertical < $size; $vertical++) {
                $row = $upward ? $size - 1 - $vertical : $vertical;

                for ($columnOffset = 0; $columnOffset < 2; $columnOffset++) {
                    $column = $right - $columnOffset;

                    if ($reserved[$row][$column]) {
                        continue;
                    }

                    $bit = $dataBits[$bitIndex] ?? 0;
                    $bitIndex++;

                    if (($row + $column) % 2 === 0) {
                        $bit ^= 1;
                    }

                    $modules[$row][$column] = (bool) $bit;
                }
            }

            $upward = ! $upward;
        }

        $this->drawFormatBits($modules, $reserved, 0);

        return $modules;
    }

    private function drawFinder(array &$modules, array &$reserved, int $left, int $top): void
    {
        $size = count($modules);

        for ($row = -1; $row <= 7; $row++) {
            for ($column = -1; $column <= 7; $column++) {
                $matrixRow = $top + $row;
                $matrixColumn = $left + $column;

                if ($matrixRow < 0 || $matrixRow >= $size || $matrixColumn < 0 || $matrixColumn >= $size) {
                    continue;
                }

                $dark = $row >= 0 && $row <= 6 && $column >= 0 && $column <= 6
                    && ($row === 0 || $row === 6 || $column === 0 || $column === 6 || ($row >= 2 && $row <= 4 && $column >= 2 && $column <= 4));

                $this->setModule($modules, $reserved, $matrixRow, $matrixColumn, $dark);
            }
        }
    }

    private function drawAlignmentPatterns(array &$modules, array &$reserved, array $centers): void
    {
        foreach ($centers as $rowCenter) {
            foreach ($centers as $columnCenter) {
                if ($reserved[$rowCenter][$columnCenter]) {
                    continue;
                }

                for ($row = -2; $row <= 2; $row++) {
                    for ($column = -2; $column <= 2; $column++) {
                        $dark = max(abs($row), abs($column)) === 2 || ($row === 0 && $column === 0);
                        $this->setModule($modules, $reserved, $rowCenter + $row, $columnCenter + $column, $dark);
                    }
                }
            }
        }
    }

    private function drawTimingPatterns(array &$modules, array &$reserved): void
    {
        $size = count($modules);

        for ($index = 0; $index < $size; $index++) {
            $dark = $index % 2 === 0;

            if (! $reserved[6][$index]) {
                $this->setModule($modules, $reserved, 6, $index, $dark);
            }

            if (! $reserved[$index][6]) {
                $this->setModule($modules, $reserved, $index, 6, $dark);
            }
        }
    }

    private function drawFormatBits(array &$modules, array &$reserved, int $mask): void
    {
        $size = count($modules);
        $data = (0b01 << 3) | $mask;
        $remainder = $data << 10;

        for ($bit = 14; $bit >= 10; $bit--) {
            if ((($remainder >> $bit) & 1) !== 0) {
                $remainder ^= 0x537 << ($bit - 10);
            }
        }

        $format = (($data << 10) | $remainder) ^ 0x5412;

        for ($index = 0; $index <= 5; $index++) {
            $this->setModule($modules, $reserved, 8, $index, $this->bit($format, $index));
        }

        $this->setModule($modules, $reserved, 8, 7, $this->bit($format, 6));
        $this->setModule($modules, $reserved, 8, 8, $this->bit($format, 7));
        $this->setModule($modules, $reserved, 7, 8, $this->bit($format, 8));

        for ($index = 9; $index <= 14; $index++) {
            $this->setModule($modules, $reserved, 14 - $index, 8, $this->bit($format, $index));
        }

        for ($index = 0; $index <= 7; $index++) {
            $this->setModule($modules, $reserved, $size - 1 - $index, 8, $this->bit($format, $index));
        }

        for ($index = 8; $index <= 14; $index++) {
            $this->setModule($modules, $reserved, 8, $size - 15 + $index, $this->bit($format, $index));
        }

        $this->setModule($modules, $reserved, $size - 8, 8, true);
    }

    private function setModule(array &$modules, array &$reserved, int $row, int $column, bool $dark): void
    {
        $modules[$row][$column] = $dark;
        $reserved[$row][$column] = true;
    }

    private function appendBits(array &$bits, int $value, int $length): void
    {
        for ($index = $length - 1; $index >= 0; $index--) {
            $bits[] = ($value >> $index) & 1;
        }
    }

    private function isAlphanumeric(string $value): bool
    {
        return $value !== '' && strspn($value, self::ALPHANUMERIC_CHARS) === strlen($value);
    }

    private function bit(int $value, int $index): bool
    {
        return (($value >> $index) & 1) !== 0;
    }

    private function reedSolomonRemainder(array $data, int $degree): array
    {
        $this->initGalois();
        $generator = $this->generatorPolynomial($degree);
        $result = array_fill(0, $degree, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ $result[0];
            array_shift($result);
            $result[] = 0;

            for ($index = 0; $index < $degree; $index++) {
                $result[$index] ^= $this->gfMultiply($generator[$index + 1], $factor);
            }
        }

        return $result;
    }

    private function generatorPolynomial(int $degree): array
    {
        $result = [1];

        for ($degreeIndex = 0; $degreeIndex < $degree; $degreeIndex++) {
            $next = array_fill(0, count($result) + 1, 0);

            foreach ($result as $index => $coefficient) {
                $next[$index] ^= $this->gfMultiply($coefficient, 1);
                $next[$index + 1] ^= $this->gfMultiply($coefficient, self::$gfExp[$degreeIndex]);
            }

            $result = $next;
        }

        return $result;
    }

    private function initGalois(): void
    {
        if (self::$gfExp) {
            return;
        }

        self::$gfExp = array_fill(0, 512, 0);
        self::$gfLog = array_fill(0, 256, 0);
        $value = 1;

        for ($index = 0; $index < 255; $index++) {
            self::$gfExp[$index] = $value;
            self::$gfLog[$value] = $index;
            $value <<= 1;

            if (($value & 0x100) !== 0) {
                $value ^= 0x11D;
            }
        }

        for ($index = 255; $index < 512; $index++) {
            self::$gfExp[$index] = self::$gfExp[$index - 255];
        }
    }

    private function gfMultiply(int $left, int $right): int
    {
        if ($left === 0 || $right === 0) {
            return 0;
        }

        return self::$gfExp[self::$gfLog[$left] + self::$gfLog[$right]];
    }
}
