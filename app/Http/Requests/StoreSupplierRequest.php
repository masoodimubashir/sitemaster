<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Can;

class StoreSupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'contact_no' => 'required|unique:suppliers,contact_no|string|digits:10',
            'address' => 'required|string',
            'is_raw_material_provider' => 'required_without:is_workforce_provider|boolean|nullable',
            'is_workforce_provider' => 'required_without:is_raw_material_provider|boolean|nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'is_raw_material_provider.required_without' => 'Please select at least one type of provider.',
            'is_workforce_provider.required_without' => 'Please select at least one type of provider.',
        ];
    }

}
