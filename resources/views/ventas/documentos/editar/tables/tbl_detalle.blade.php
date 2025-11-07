<table class="table table-sm table-striped table-bordered table-hover" id="table-detalle">
    <thead>
        <tr>
            <th></th>
            <th scope="col">PRODUCTO</th>
            <th scope="col">COLOR</th>
            @foreach ($tallas as $talla)
                <th scope="col" data-talla={{ $talla->id }}>{{ $talla->descripcion }}</th>
            @endforeach

            <th>PRECIO VENTA</th>
            <th>SUBTOTAL</th>
            <th>DSCTO %</th>
        </tr>
    </thead>
    <tbody>

    </tbody>


</table>
