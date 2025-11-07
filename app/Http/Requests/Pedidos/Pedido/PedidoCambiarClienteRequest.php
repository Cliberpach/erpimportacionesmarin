<?php

namespace App\Http\Requests\Pedidos\Pedido;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PedidoCambiarClienteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cliente_cambio_id' => [
                'required',
                Rule::exists('clientes', 'id')->where(function ($query) {
                    $query->where('estado', 'ACTIVO');
                }),
            ],
        ];
    }

    public function messages()
    {
        return [
            'cliente_cambio_id.required' => 'El cliente es obligatorio.',
            'cliente_cambio_id.exists'   => 'El cliente seleccionado no existe o no se encuentra activo.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'success' => false,
            'errors'  => $validator->errors(),
        ], 422));
    }
}
