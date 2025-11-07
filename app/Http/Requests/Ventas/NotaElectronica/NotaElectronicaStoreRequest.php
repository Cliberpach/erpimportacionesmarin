<?php

namespace App\Http\Requests\Ventas\NotaElectronica;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;

class NotaElectronicaStoreRequest extends FormRequest
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
        return [
            'documento_id'   => 'required',
            'fecha_emision'  => 'required|date',
            'tipo_nota'      => 'required',
            'cliente'        => 'required|string|max:191',
            'des_motivo'     => 'required|string|max:191',
            'cod_motivo'     => 'required|string|max:191',
        ];
    }

    /**
     * Get the validation messages for the rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'documento_id.required'  => 'El campo Documento es obligatorio.',
            'fecha_emision.required' => 'El campo Fecha de Emisión es obligatorio.',
            'fecha_emision.date'     => 'El campo Fecha de Emisión debe ser una fecha válida.',
            'tipo_nota.required'     => 'El campo Tipo es obligatorio.',
            'cliente.required'       => 'El campo Cliente es obligatorio.',
            'des_motivo.required'    => 'El campo Motivo es obligatorio.',
            'cod_motivo.required'    => 'El campo Tipo Nota de Crédito es obligatorio.',
        ];
    }

      protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
