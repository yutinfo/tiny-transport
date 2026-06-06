<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function suggest(Request $request)
    {
        $request->validate([
            'mobile' => ['required', 'string', 'min:3', 'max:10', 'regex:/^\d+$/'],
            'type' => ['nullable', Rule::in(['sender', 'receiver', 'both'])],
        ]);

        $preferredType = $request->type ?? 'both';
        $contacts = $this->applyTypePreference(Contact::where('mobile', 'like', $request->mobile . '%'), $preferredType)
            ->orderBy('mobile')
            ->latest('id')
            ->limit(8)
            ->get();

        return response()->json([
            'data' => $contacts->map(fn ($contact) => $this->formatContact($contact))->values(),
        ]);
    }

    public function search(Request $request)
    {
        if ($request->filled('q')) {
            $request->validate([
                'q' => ['required', 'string', 'min:2', 'max:100'],
                'type' => ['nullable', Rule::in(['sender', 'receiver', 'both'])],
            ]);

            $keyword = trim((string) $request->q);
            $normalizedKeyword = $this->normalizeMobile($keyword);
            $preferredType = $request->type ?? 'both';

            $contacts = $this->applyTypePreference(Contact::query(), $preferredType)
                ->where(function ($query) use ($keyword, $normalizedKeyword) {
                    $query->where('name', 'like', '%' . $keyword . '%');

                    if ($normalizedKeyword !== '') {
                        $query->orWhere('mobile', 'like', '%' . $normalizedKeyword . '%');
                    }
                })
                ->orderBy('name')
                ->latest('id')
                ->limit(10)
                ->get();

            return response()->json([
                'data' => $contacts->map(fn ($contact) => $this->formatContact($contact))->values(),
            ]);
        }

        $request->merge([
            'mobile' => $this->normalizeMobile((string) $request->mobile),
        ]);

        $request->validate([
            'mobile' => ['required', 'string', 'regex:/^\d{9,10}$/'],
            'type' => ['nullable', Rule::in(['sender', 'receiver', 'both'])],
        ]);

        $preferredType = $request->type ?? 'both';

        $contact = $this->applyTypePreference(Contact::where('mobile', $request->mobile), $preferredType)
            ->latest('id')
            ->first();

        if (! $contact) {
            return response()->json([
                'data' => null,
            ], 404);
        }

        return response()->json([
            'data' => $this->formatContact($contact),
        ]);
    }

    private function formatContact(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'type' => $contact->type,
            'name' => $contact->name,
            'mobile' => $contact->mobile,
            'address' => $contact->address,
            'province_id' => $contact->province_id,
            'amphure_id' => $contact->amphure_id,
            'district_id' => $contact->district_id,
            'province_name' => $contact->province_name,
            'amphure_name' => $contact->amphure_name,
            'district_name' => $contact->district_name,
            'zip_code' => $contact->zip_code,
        ];
    }

    private function applyTypePreference($query, string $preferredType)
    {
        if (in_array($preferredType, ['sender', 'receiver'], true)) {
            $query->whereIn('type', [$preferredType, 'both']);
        }

        return $query->orderByRaw("case when type = ? then 0 when type = 'both' then 1 else 2 end", [$preferredType]);
    }

    private function normalizeMobile(string $mobile): string
    {
        return preg_replace('/\D/', '', $mobile) ?: '';
    }
}
