<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPrestataireRequest extends FormRequest
{

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
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:prestataires',
            'telephone' => 'required|string|min:10|max:20|unique:prestataires',
            'password' => 'required|string|min:8',
            'categorie' => 'required|string',
            'ville' => 'required|string',
            'zone' => 'required|string',
        ];
    }
}
