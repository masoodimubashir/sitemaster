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
            'provider' => ['required', 'in:is_raw_material_provider,is_workforce_provider'],
        ];
    }


    public function messages(): array
    {
        return [
            'provider.required' => 'Please select a provider type.',
            'provider.in' => 'Invalid provider selected.',
        ];
    }


}


