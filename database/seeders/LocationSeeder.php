<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LocationSeeder extends Seeder
{
    private const SOURCES = [
        'geographies' => 'https://raw.githubusercontent.com/Cerberus/Thailand-Address/master/geography.json',
        'provinces' => 'https://raw.githubusercontent.com/Cerberus/Thailand-Address/master/provinces.json',
        'amphures' => 'https://raw.githubusercontent.com/Cerberus/Thailand-Address/master/districts.json',
        'districts' => 'https://raw.githubusercontent.com/Cerberus/Thailand-Address/master/subDistricts.json',
        'zipcodes' => 'https://raw.githubusercontent.com/Cerberus/Thailand-Address/master/zipcodes.json',
    ];

    public function run()
    {
        $geographies = $this->fetch('geographies');
        $provinces = $this->fetch('provinces');
        $amphures = $this->fetch('amphures');
        $districts = $this->fetch('districts');
        $zipcodes = $this->fetch('zipcodes');

        $geographyRows = collect($geographies)
            ->map(fn ($row) => [
                'id' => (int) $row['GEO_ID'],
                'name' => $row['GEO_NAME'],
            ])
            ->values()
            ->all();

        $provinceRows = collect($provinces)
            ->filter(fn ($row) => $this->isActiveName($row['PROVINCE_NAME']))
            ->map(fn ($row) => [
                'id' => (int) $row['PROVINCE_ID'],
                'code' => (int) $row['PROVINCE_CODE'],
                'name_th' => $row['PROVINCE_NAME'],
                'name_en' => $row['PROVINCE_NAME'],
                'geography_id' => (int) $row['GEO_ID'],
            ])
            ->values()
            ->all();

        $validProvinceIds = collect($provinceRows)->pluck('id')->flip();

        $amphureRows = collect($amphures)
            ->filter(fn ($row) => $this->isActiveName($row['DISTRICT_NAME']))
            ->filter(fn ($row) => $validProvinceIds->has((int) $row['PROVINCE_ID']))
            ->map(fn ($row) => [
                'id' => (int) $row['DISTRICT_ID'],
                'code' => (int) $row['DISTRICT_CODE'],
                'name_th' => $row['DISTRICT_NAME'],
                'name_en' => $row['DISTRICT_NAME'],
                'province_id' => (int) $row['PROVINCE_ID'],
            ])
            ->values()
            ->all();

        $validAmphureIds = collect($amphureRows)->pluck('id')->flip();
        $zipcodeBySubDistrictId = collect($zipcodes)->keyBy(fn ($row) => (int) $row['SUB_DISTRICT_ID']);

        $districtRows = collect($districts)
            ->filter(fn ($row) => $this->isActiveName($row['SUB_DISTRICT_NAME']))
            ->filter(fn ($row) => $validAmphureIds->has((int) $row['DISTRICT_ID']))
            ->filter(fn ($row) => $zipcodeBySubDistrictId->has((int) $row['SUB_DISTRICT_ID']))
            ->map(function ($row) use ($zipcodeBySubDistrictId) {
                $zipcode = $zipcodeBySubDistrictId->get((int) $row['SUB_DISTRICT_ID']);

                return [
                    'id' => (int) $row['SUB_DISTRICT_ID'],
                    'zip_code' => (int) $zipcode['ZIPCODE'],
                    'name_th' => $row['SUB_DISTRICT_NAME'],
                    'name_en' => $row['SUB_DISTRICT_NAME'],
                    'amphure_id' => (int) $row['DISTRICT_ID'],
                ];
            })
            ->values()
            ->all();

        DB::transaction(function () use ($geographyRows, $provinceRows, $amphureRows, $districtRows) {
            $this->clearLocationTables();

            $this->insertRows('geographies', $geographyRows);
            $this->insertRows('provinces', $provinceRows);
            $this->insertRows('amphures', $amphureRows);
            $this->insertRows('districts', $districtRows);
        });

        Cache::forget('province');

        $this->command?->info(sprintf(
            'Seeded %d geographies, %d provinces, %d amphures, %d districts.',
            count($geographyRows),
            count($provinceRows),
            count($amphureRows),
            count($districtRows)
        ));
    }

    private function fetch(string $key): array
    {
        $response = Http::timeout(60)
            ->retry(3, 500)
            ->get(self::SOURCES[$key]);

        if (! $response->successful()) {
            throw new RuntimeException("Unable to download Thailand address dataset: {$key}");
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException("Invalid Thailand address dataset response: {$key}");
        }

        return $data;
    }

    private function isActiveName(string $name): bool
    {
        return ! str_contains($name, '*');
    }

    private function clearLocationTables(): void
    {
        foreach (['districts', 'amphures', 'provinces', 'geographies'] as $table) {
            DB::table($table)->delete();
        }
    }

    private function insertRows(string $table, array $rows): void
    {
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }
}
