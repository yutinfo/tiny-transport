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
            'type' => ['nullable', Rule::in(['sender', 'receiver'])],
        ]);

        $preferredType = $request->type ?? '';
        $contacts = Contact::where('mobile', 'like', $request->mobile . '%')
            ->orderByRaw("case when type = ? then 0 when type = 'both' then 1 else 2 end", [$preferredType])
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
        $request->validate([
            'mobile' => ['required', 'string', 'regex:/^\d{9,10}$/'],
            'type' => ['nullable', Rule::in(['sender', 'receiver'])],
        ]);

        $query = Contact::where('mobile', $request->mobile);
        $preferredType = $request->type ?? '';

        $contact = $query->orderByRaw("case when type = ? then 0 when type = 'both' then 1 else 2 end", [$preferredType])
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
}
