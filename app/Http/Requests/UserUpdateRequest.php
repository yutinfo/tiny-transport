<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
        $rule = [

            'status' => ['required', Rule::in(['active', 'inactive', 'ban'])],
            'role_name' => ['required', Rule::in(['admin', 'staff'])],
            'name' => 'required',
            'last_name' => 'required',
        ];

        if(request()->password!=null){
            $rule['password'] = 'required|min:6';
        }
        return $rule;

    }
    public function messages()
    {
        return [
            'required' => 'ข้อมูล :attribute จำเป็นต้องกรอก',
            'min' => 'ข้อมูล :attribute อย่างน้อย 6 ตัวอักษร'
            ];
    }
}
