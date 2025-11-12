<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class setparameterRequest extends FormRequest
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
            'bio' => 'nullable|string|max:1000',
            'prix_heure' => 'required|numeric|min:0',
             'carte_identite' => 'image|mimes:jpeg,png,jpg',
             'facebook_url' => 'nullable|url|max:255',
             'linkedin_url' => 'nullable|url|max:255',
        ];
    }
}
