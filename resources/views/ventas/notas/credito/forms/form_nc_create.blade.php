 <form id="enviar_documento">
     @csrf
     <input type="hidden" name="documento_id" value="{{ old('documento_id', $documento->id) }}">
     <input type="hidden" name="tipo_nota" value="{{ $tipo_nota }}">
     <input type="hidden" name="productos_tabla" id="productos_tabla">
     @if (isset($nota_venta))
         <input type="hidden" name="nota_venta" id="nota_venta" value="1">
     @endif
     <div class="row">
         <div class="col-12 col-md-5 b-r">
             <div class="row">
                 <div class="col-12">
                     <p style="text-transform:uppercase"><strong><i class="fa fa-caret-right"></i> Información de nota
                             de @if (isset($nota_venta))
                                 devolución
                             @else
                                 crédito
                             @endif
                         </strong>
                     </p>
                 </div>
             </div>
             <div class="form-group row">
                 <div class="col-12 col-md-5">
                     <label class="required">Tipo Nota de @if (isset($nota_venta))
                             Devolución
                         @else
                             Crédito
                         @endif
                     </label>
                 </div>
                 <div class="col-12 col-md-7">
                     @if (isset($nota_venta))
                         <select name="cod_motivo" id="cod_motivo" class="select2_form form-control"
                             onchange="changeTipoNota(this)">
                             <option value=""></option>
                             @foreach (cod_motivos() as $item)
                                 <option value="{{ $item->simbolo }}" {{ $item->simbolo === '07' ? 'selected' : '' }}>
                                     {{ $item->descripcion }}</option>
                             @endforeach
                         </select>
                         <input type="hidden" name="cod_motivo" id="cod_motivo" value="07">
                     @else
                         <select name="cod_motivo" id="cod_motivo" class="select2_form form-control"
                             onchange="changeTipoNota(this)" required>
                             <option value=""></option>
                             @foreach (cod_motivos() as $item)
                                 <option value="{{ $item->simbolo }}" {{ $item->simbolo === '07' ? 'selected' : '' }}>
                                     {{ $item->descripcion }}</option>
                             @endforeach
                         </select>
                     @endif
                 </div>
             </div>
             <div class="form-group row">
                 <div class="col-12 col-md-5">
                     <label class="required">Motivo</label>
                 </div>
                 <div class="col-12 col-md-7">
                     <textarea name="des_motivo" id="des_motivo" rows="2" class="form-control" required></textarea>
                 </div>
             </div>
         </div>
         <div class="col-12 col-md-7">
             <div class="row">
                 <div class="col-12">
                     <p style="text-transform:uppercase"><strong><i class="fa fa-caret-right"></i> Información de
                             cliente</strong></p>
                 </div>
             </div>
             <div class="row">
                 <div class="col-12 col-md-3">
                     <div class="form-group row">
                         <div class="col-12 col-md-5">
                             <label class="required">Cliente ID</label>
                         </div>
                         <div class="col-12 col-md-7">
                             <input type="text" class="form-control" value="{{ $documento->clienteEntidad->id }}"
                                 readonly>
                         </div>
                     </div>
                 </div>
                 <div class="col-12 col-md-9">
                     <div class=" form-group row">
                         <div class="col-12 col-md-5">
                             <label class="required">Tipo Doc. / Nro. Doc</label>
                         </div>
                         <div class="col-12 col-md-3">
                             <input type="text" class="form-control"
                                 value="{{ $documento->clienteEntidad->tipo_documento }}" readonly>
                         </div>
                         <div class="col-12 col-md-4">
                             <input type="text" class="form-control"
                                 value="{{ $documento->clienteEntidad->documento }}" readonly>
                         </div>
                     </div>
                 </div>
                 <div class="col-12">
                     <div class="form-group row">
                         <div class="col-12 col-md-4">
                             <label class="required">Nombre / Razón Social</label>
                         </div>
                         <div class="col-12 col-md-8">
                             <input type="text" class="form-control" name="cliente" id="cliente"
                                 value="{{ $documento->clienteEntidad->nombre }}" readonly>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     <hr>
     <div class="row">
         <div class="col-12 col-md-4">
             <div class="form-group row">
                 <div class="col-12 col-md-6">
                     <label class="required">{{ $documento->tipo_documento_cliente }}</label>
                 </div>
                 <div class="col-12 col-md-6">
                     <input type="text" class="form-control" name="documento_cliente"
                         value="{{ $documento->documento_cliente }}" readonly>
                 </div>
             </div>
             <div class="form-group row d-none">
                 <div class="col-12 col-md-6">
                     <label class="required">Serie Nota</label>
                 </div>
                 <div class="col-12 col-md-7">
                     <input type="text" class="form-control" name="serie_nota" value="" readonly>
                 </div>
             </div>
             <div class="form-group row d-none">
                 <div class="col-12 col-md-6">
                     <label class="required">Nro. Nota</label>
                 </div>
                 <div class="col-12 col-md-7">
                     <input type="text" class="form-control" name="numero_nota" value="" readonly>
                 </div>
             </div>
             <div class="form-group row">
                 <div class="col-12 col-md-6">
                     <label class="required">Emisión de Nota</label>
                 </div>
                 <div class="col-12 col-md-6">
                     <input type="date" class="form-control" name="fecha_emision" value="{{ $fecha_hoy }}"
                         required>
                 </div>
             </div>
             <div class="form-group row">
                 <div class="col-12 col-md-6">
                     <label class="required">Fecha Documento</label>
                 </div>
                 <div class="col-12 col-md-6">
                     <input type="date" class="form-control" name="fecha_documento"
                         value="{{ $documento->fecha_documento }}" readonly>
                 </div>
             </div>
         </div>
         <div class="col-12 col-md-4">
             <div class="form-group row">
                 <div class="col-12 col-md-6">
                     <label class="required">Serie doc. afectado</label>
                 </div>
                 <div class="col-12 col-md-6">
                     <input type="text" class="form-control" name="serie_doc" value="{{ $documento->serie }}"
                         readonly>
                 </div>
             </div>
             <div class="form-group row">
                 <div class="col-12 col-md-6">
                     <label class="required">Nro. doc. afectado</label>
                 </div>
                 <div class="col-12 col-md-6">
                     <input type="text" class="form-control" name="numero_doc"
                         value="{{ $documento->correlativo }}" readonly>
                 </div>
             </div>
             <div class="form-group row">
                 <div class="col-12 col-md-6">
                     <label class="required">Tipo Pago</label>
                 </div>
                 <div class="col-12 col-md-6">
                     <input type="text" class="form-control text-uppercase" name="tipo_pago"
                         value="{{ $documento->formaPago() }}" readonly>
                 </div>
             </div>
         </div>
         <div class="col-12 col-md-4">
             <div class="form-group row @if ($documento->tipo_venta == '129') d-none @endif">
                 <div class="col-12 col-md-6">
                     <label class="required">Sub Total</label>
                 </div>
               
                 <div class="col-12 col-md-6">
                     <input type="text" class="form-control" name="sub_total" id="sub_total"
                         value="{{ $documento->total }}" readonly>
                 </div>
             </div>
             <div class="form-group row @if ($documento->tipo_venta == '129') d-none @endif">
                 <div class="col-12 col-md-6">
                     <label class="required">IGV {{ $documento->igv }}%</label>
                 </div>
                 <div class="col-12 col-md-6">
                     <input type="text" class="form-control" name="total_igv" id="total_igv"
                         value="{{ $documento->total_igv }}" readonly>
                 </div>
             </div>

             <div class="form-group row">
                 <div class="col-12 col-md-6">
                     <label class="required">Total</label>
                 </div>
                 <div class="col-12 col-md-6">
                     <input type="text" class="form-control" name="total" id="total"
                         value="{{ $documento->total_pagar }}" readonly>
                 </div>
             </div>
         </div>
     </div>
     <div class="row">
         <div class="col-12">
             <div class="panel panel-primary" id="panel_detalle">
                 <div class="panel-heading">
                     <div class="row">
                         <div class="col-10">
                             <h4>Seleccionar productos</h4>
                         </div>
                     </div>
                 </div>
                 <div class="panel-body ibox-content">
                     <div class="sk-spinner sk-spinner-wave">
                         <div class="sk-rect1"></div>
                         <div class="sk-rect2"></div>
                         <div class="sk-rect3"></div>
                         <div class="sk-rect4"></div>
                         <div class="sk-rect5"></div>
                     </div>
                     <div class="row">
                         <div class="col-12">
                             <div class="table-responsive">
                                 <table id="tbl-detalles" class="table table-hover tbl-detalles"
                                     style="width: 100%; text-transform:uppercase;">
                                     <thead>
                                         <th></th>
                                         <th>Cant.</th>
                                         <th>Descripcion</th>
                                         <th>P. Unit</th>
                                         <th>Total</th>
                                         <th class="tbl-detalles-opciones">Opciones</th>
                                         <th></th>
                                     </thead>
                                     <tbody>
                                     </tbody>
                                 </table>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </div>
     <hr>
     @include('ventas.notas.credito.table-devoluciones')
     <div class="hr-line-dashed"></div>
     <div class="form-group row">
         <div class="col-md-6 text-left" style="color:#fcbc6c">
             <i class="fa fa-exclamation-circle"></i> <small>Los campos marcados con asterisco
                 (<label class="required"></label>) son obligatorios.</small>
         </div>

         <div class="col-md-6 text-right">
             <div class="row">
                 <div class="col-12 col-md-6">
                     @if (isset($nota_venta))
                         <a href="{{ route('ventas.notas_dev', $documento->id) }}" id="btn_cancelar"
                             class="btn btn-w-m btn-block btn-default">
                             <i class="fa fa-arrow-left"></i> Regresar
                         </a>
                     @else
                         <a href="{{ route('ventas.notas', $documento->id) }}" id="btn_cancelar"
                             class="btn btn-w-m btn-block btn-default">
                             <i class="fa fa-arrow-left"></i> Regresar
                         </a>
                     @endif
                 </div>
                 <div class="col-12 col-md-6">
                     <button type="submit" class="btn btn-w-m btn-block btn-primary">
                         <i class="fa fa-save"></i> Grabar
                     </button>
                 </div>
             </div>
         </div>
     </div>
</form>
