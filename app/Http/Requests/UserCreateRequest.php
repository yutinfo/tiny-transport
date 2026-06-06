<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $emailRule = app()->environment('testing') ? 'required|email|unique:users,email' : 'required|email:rfc,dns|unique:users,email';
        return [
            'email' => $emailRule,
            'username' => 'required|unique:users,username',
            'password' => 'required|min:6',
            'status' => ['required', Rule::in(['active', 'inactive', 'ban'])],
            'role_name' => ['required', Rule::in(User::roles())],
            'name' => 'required',
            'last_name' => 'required',
        ];

    }
    public function messages()
    {
        return [
            'required' => 'ข้อมูล :attribute จำเป็นต้องกรอก',
            'min' => 'ข้อมูล :attribute อย่างน้อย 6 ตัวอักษร'
            ];
    }
}
