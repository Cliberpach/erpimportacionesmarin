<table class="table table-sm table-striped table-bordered table-hover" id="table-detalle">
    <thead>
        <tr>
            <th scope="col">PRODUCTO</th>
            @foreach ($tallas as $talla)
                <th scope="col" data-talla={{ $talla->id }}>{{ $talla->descripcion }}</th>
            @endforeach

            <th>PRECIO VENTA</th>
            <th>SUBTOTAL</th>
            <th>DESCUENTO %</th>

        </tr>
    </thead>
    <tbody>

    </tbody>

</table>
