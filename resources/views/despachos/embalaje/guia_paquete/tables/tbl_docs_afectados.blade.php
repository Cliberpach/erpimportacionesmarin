<table class="table table-hover text-center" id="table-docs-afectados">
    <thead class="thead-light">
        <tr>
            <th scope="col">DOCUMENTO</th>
            <th scope="col">COMPROBANTE</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($ventas as $venta)
            <tr>
                <td>{{ $venta->documento_nro }}</td>
                <td>{{ $venta->convert_en_serie }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
