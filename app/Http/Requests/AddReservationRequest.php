<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email' => 'required|email|max:255',

            'date' => 'required|date|after_or_equal:today',
            'heure' => 'required|date_format:H:i',
            'adresse' => 'required|string|max:500',
            'description_besoin' => 'required|string',

            'service_id' => 'required|integer|exists:services,id'
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est obligatoire',
            'prenom.required' => 'Le prénom est obligatoire',
            'telephone.required' => 'Le numéro de téléphone est obligatoire',
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être valide',
            'date.required' => 'La date est obligatoire',
            'date.after_or_equal' => 'La date ne peut pas être dans le passé',
            'heure.required' => 'L\'heure est obligatoire',
            'heure.date_format' => 'Le format de l\'heure doit être HH:MM',
            'adresse.required' => 'L\'adresse est obligatoire',
            'description_besoin.required' => 'La description du besoin est obligatoire',
            'service_id.required' => 'Le service est obligatoire',
            'service_id.exists' => 'Le service sélectionné n\'existe pas'
        ];
    }
}
