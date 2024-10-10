<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
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
            'site_name' => 'required|string|min:5',
            'service_charge' => 'required',
            'location' => 'required|string',
            'site_owner_name' => 'required|string',
            'contact_no' => 'required|digits:10',
            'user_id' => 'required|exists:users,id'
        ];
    }
}
