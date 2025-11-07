<?php

namespace App\Http\Services\Pedidos\Pedidos;

use App\Models\Reservas\Reservas\Pedido;

class PedidoManager
{
    private PedidoService $s_pedido;

    public function __construct() {
        $this->s_pedido = new PedidoService();
    }

    public function store(array $datos):array{
        return $this->s_pedido->store($datos);
    }

    public function facturar(array $datos):object {
        return $this->s_pedido->facturar($datos);
    }

    public function generarDocumentoVenta(array $datos):int {
        return $this->s_pedido->generarDocumentoVenta($datos);
    }

    public function update(array $datos,int $id):Pedido{
        return $this->s_pedido->update($datos,$id);
    }

    public function destroy(int $id):Pedido{
        return $this->s_pedido->destroy($id);
    }

    public function cambiarCliente(array $datos):Pedido{
        return $this->s_pedido->cambiarCliente($datos);
    }
}
