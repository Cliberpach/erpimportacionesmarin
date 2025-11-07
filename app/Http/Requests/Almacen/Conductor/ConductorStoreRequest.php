<?php

namespace App\Http\Requests\Almacen\Conductor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class ConductorStoreRequest extends FormRequest
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
    public function rules()
    {
        $rules = [
            'tipo_documento' => [
                'required',
                'in:6',
                Rule::exists('tabladetalles', 'id')->where(function ($query) {
                    $query->where('estado', 'ACTIVO');
                }),
            ],
            'nro_documento' => [
                'required',
                'digits:8',
                'regex:/^[0-9]{8}$/',
                Rule::unique('conductores', 'nro_documento')->where(function ($query) {
                    $query->where('estado', 'ACTIVO');
                })
            ],
            'nombres' => [
                'required',
                'string',
                'max:160',
            ],
            'apellidos' => [
                'required',
                'string',
                'max:160',
            ],
            'licencia' => [
                'required',
                'alpha_num',
                'min:9',
                'max:10',
                'not_regex:/^0+$/',
            ]
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'tipo_documento.required'   => 'El tipo de documento es obligatorio.',
            'tipo_documento.in'         => 'El tipo de documento debe ser DNI.',
            'tipo_documento.exists'     => 'El tipo de documento no es válido o está inactivo.',

            'nro_documento.required'    => 'El número de documento es obligatorio.',
            'nro_documento.digits'      => 'El número de documento debe tener exactamente 8 dígitos.',
            'nro_documento.regex'       => 'El número de documento debe contener solo números.',
            'nro_documento.unique'      => 'Ya existe un conductor activo con este número de documento.',

            'nombres.required'           => 'El campo nombres es obligatorio.',
            'nombres.max'                => 'El campo nombres no debe exceder los 160 caracteres.',

            'apellidos.required'           => 'El campo apellidos es obligatorio.',
            'apellidos.max'                => 'El campo apellidos no debe exceder los 160 caracteres.',

            'licencia.required'         => 'La licencia es obligatoria.',
            'licencia.alpha_num'        => 'La licencia debe contener solo letras y números.',
            'licencia.min'              => 'La licencia debe tener al menos 9 caracteres.',
            'licencia.max'              => 'La licencia no debe exceder los 10 caracteres.',
            'licencia.not_regex'        => 'La licencia no puede estar compuesta solo por ceros.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
