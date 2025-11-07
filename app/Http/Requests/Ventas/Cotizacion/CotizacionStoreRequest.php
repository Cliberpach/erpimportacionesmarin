<?php

namespace App\Http\Requests\Ventas\Cotizacion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

class CotizacionStoreRequest extends FormRequest
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
            'almacen' => [
                'required',
                'exists:almacenes,id',
                Rule::exists('almacenes', 'id')->where(function ($query) {
                    $query->where('estado', 'ACTIVO');
                }),
            ],
            'cliente' => [
                'required',
                Rule::exists('clientes', 'id')->where(function ($query) {
                    $query->where('estado', 'ACTIVO');
                }),
            ],
            'condicion_id' => [
                'required',
                Rule::exists('condicions', 'id')->where(function ($query) {
                    $query->where('estado', 'ACTIVO');
                }),
            ],
            'origen_venta'      => 'required|exists:tabladetalles,id,estado,ACTIVO',

            'telefono' => [
                'nullable',
                'regex:/^[0-9]+$/',
                'min:9',
                'max:20',
            ],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'almacen.required'  => 'El campo almacén es obligatorio.',
            'almacen.exists'    => 'El almacén seleccionado no existe o está ANULADO.',

            'cliente.required'  => 'El campo cliente es obligatorio.',
            'cliente.exists'    => 'El cliente seleccionado no existe o no está activo.',

            'condicion_id.required' => 'El campo condición es obligatorio.',
            'condicion_id.exists'   => 'La condición seleccionada no existe o no está activa.',

            'origen_venta.required'      => 'El campo origen venta es obligatorio.',
            'origen_venta.exists'        => 'El origen venta seleccionado no existe o no está activo.',

            'telefono.regex'                   => 'El teléfono solo puede contener números.',
            'telefono.min'                     => 'El teléfono debe tener al menos :min dígitos.',
            'telefono.max'                     => 'El teléfono no puede tener más de :max dígitos.',

        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}
