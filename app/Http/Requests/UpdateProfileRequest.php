<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
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
        $prestataireId = Auth::id();
        return [
            'nom' => 'string|max:255',
            'prenom' => 'string|max:255',
            'email ' => [
                'string',
                'email',
                'max:255',
                Rule::unique('prestataires')->ignore($prestataireId),

            ],
            'telephone' => [
                'string',
                'max:20',
                Rule::unique('prestataires')->ignore($prestataireId),
            ],
            'image' => 'nullable|image|mimes:jpeg,png.jpg',
            'bio' =>'nullable|string',
        ];

    }
}
