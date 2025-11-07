<?php

namespace App\Http\Requests\Almacen\Transportista;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class TransportistaUpdateRequest extends FormRequest
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
            'tipo_documento' => [
                'required',
                'in:8',
                Rule::exists('tabladetalles', 'id')->where(function ($query) {
                    $query->where('estado', 'ACTIVO');
                }),
            ],
            'nro_documento' => [
                'required',
                'digits:11',
                Rule::unique('transportistas', 'nro_documento')
                    ->ignore($this->route('id'))
                    ->where(function ($query) {
                        $query->where('estado', 'ACTIVO');
                    }),
            ],
            'nombre' => [
                'required',
                'string',
                'max:160',
            ],
            'direccion' => ['nullable', 'string', 'max:200'],
            'mtc' => [
                'nullable',
                'regex:/^(?!0+$)[A-Za-z0-9]+$/',
                'max:20'
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo_documento.required'   => 'El tipo de documento es obligatorio.',
            'tipo_documento.in'         => 'El tipo de documento debe ser RUC.',
            'tipo_documento.exists'     => 'El tipo de documento no es válido o está inactivo.',

            'nro_documento.required'    => 'El número de documento es obligatorio.',
            'nro_documento.digits'      => 'El número de documento debe tener exactamente 11 dígitos numéricos.',
            'nro_documento.unique'      => 'Ya existe un conductor activo con este número de documento.',

            'nombre.required'           => 'El nombre es obligatorio.',
            'nombre.max'                => 'El nombre no debe exceder los 160 caracteres.',

            'direccion.max' => 'La dirección no debe exceder los 200 caracteres.',

            'mtc.regex'     => 'El código MTC debe ser alfanumérico, sin símbolos ni guiones, y no puede ser solo ceros.',
            'mtc.max'       => 'El código MTC no debe exceder los 20 caracteres.',

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
