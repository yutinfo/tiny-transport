<?php

namespace App\Http\Controllers;

use App\Models\Amphure;
use App\Models\Contact;
use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::query()->orderByDesc('id');

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'like', "%{$keyword}%")
                    ->orWhere('mobile', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        return view('admin.contact.list', [
            'data' => $query->paginate(20)->appends($request->query()),
            'typeLabels' => Contact::typeLabels(),
            'selected' => $request->only(['keyword', 'type']),
        ]);
    }

    public function create()
    {
        return view('admin.contact.create', [
            'data' => new Contact(),
            'typeLabels' => Contact::typeLabels(),
            'provinces' => Province::orderBy('name_th')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['created_by'] = Auth::user()->name ?? null;
        $data['updated_by'] = Auth::user()->name ?? null;

        Contact::create($data);

        return redirect()->route('admin.contacts.create')->with('success', 'Contact created.');
    }

    public function edit($id)
    {
        return view('admin.contact.edit', [
            'data' => Contact::findOrFail($id),
            'typeLabels' => Contact::typeLabels(),
            'provinces' => Province::orderBy('name_th')->get(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $data = $this->validatedData($request, $contact->id);
        $data['updated_by'] = Auth::user()->name ?? null;

        $contact->update($data);

        return redirect()->route('admin.contacts.edit', $contact->id)->with('success', 'Contact updated.');
    }

    public function destroy($id)
    {
        Contact::findOrFail($id)->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'Contact deleted.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $mobileUniqueRule = Rule::unique('contacts')
            ->where(fn ($query) => $query->where('type', $request->type));

        if ($ignoreId !== null) {
            $mobileUniqueRule->ignore($ignoreId);
        }

        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in(array_keys(Contact::typeLabels()))],
            'name' => ['required', 'string', 'max:100'],
            'mobile' => [
                'required',
                'string',
                'max:15',
                'regex:/^\d{9,10}$/',
                $mobileUniqueRule,
            ],
            'address' => ['nullable', 'string'],
            'province_id' => ['nullable', 'integer', 'exists:provinces,id'],
            'amphure_id' => ['nullable', 'integer', 'exists:amphures,id'],
            'district_id' => ['nullable', 'integer', 'exists:districts,id'],
            'zip_code' => ['nullable', 'digits_between:5,10'],
        ], [], [
            'type' => 'ประเภท',
            'name' => 'ชื่อ-นามสกุล',
            'mobile' => 'เบอร์โทรศัพท์',
            'address' => 'ที่อยู่',
            'province_id' => 'จังหวัด',
            'amphure_id' => 'อำเภอ',
            'district_id' => 'ตำบล',
            'zip_code' => 'รหัสไปรษณีย์',
        ]);

        $validator->validate();

        $data = $request->only([
            'type',
            'name',
            'mobile',
            'address',
            'province_id',
            'amphure_id',
            'district_id',
            'zip_code',
        ]);

        return array_merge($data, $this->locationNames($data));
    }

    private function locationNames(array $data): array
    {
        $province = filled(Arr::get($data, 'province_id')) ? Province::find(Arr::get($data, 'province_id')) : null;
        $amphure = filled(Arr::get($data, 'amphure_id')) ? Amphure::find(Arr::get($data, 'amphure_id')) : null;
        $district = filled(Arr::get($data, 'district_id')) ? District::find(Arr::get($data, 'district_id')) : null;

        return [
            'province_name' => $province->name_th ?? null,
            'amphure_name' => $amphure->name_th ?? null,
            'district_name' => $district->name_th ?? null,
        ];
    }
}
