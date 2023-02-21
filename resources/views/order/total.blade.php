@extends('layouts.app')

@section('content')

@php
  use Carbon\Carbon;
  use App\Product;
  use App\State;
  Carbon::setLocale('es');
  $visible = State::find($id)->status;
@endphp

<div class="row">
  <div class="col-md-12">
      <div class="card card-nav-tabs">
          <h4 class="card-header card-header-primary">Total de la órden N°: <b>{{ $id }}</b> Estado: <b>{{ $visible }}</b></h4>
                
          <div class="card-body">
                          
              <div class="material-datatables">

                  <table id="datainfo" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                      <thead>
                          <tr>
                              <th>Nombre</th>
                              <th>Valor Unidad</th>
                              <th>Cantidad</th>
                              <th>Total</th>
                              <th>Imágen</th>
                          </tr>
                      </thead>
                      <tfoot>
                          <tr>
                              <th>Nombre</th>
                              <th>Valor Unidad</th>
                              <th>Cantidad</th>
                              <th>Total</th>
                              <th>Imágen</th>
                          </tr>
                      </tfoot>
                      <tbody>
                      @foreach ($order as $o)

                      @php
                        $value = Product::find($o->product_id);
                      @endphp

              <!--    end small modal -->

                      <tr>
                        <td>{{ $value->name }}</td>
                        <td>${{ $value->price }}</td>
                        <td>x{{ $o->num }}</td>
                        <td>${{ $value->price * $o->num }}</td>
                        <td><img width="100px" class="img-thumbnail" src="{{ asset('photos'. $value->path)  }}"></td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
              </div>
              <div class="row">
                <div class="col-md-6 text-left">
                  @if($visible == 'ESPERA')
                        {!! Form::open(array('route' => ['edit.order', $id],'method'=>'POST')) !!}
                        <button type="submit" class="btn btn-rose btn-sm">Cerrar Pedido</button>
                        {!! Form::close() !!}
                    @endif
                    <a target="_blank" href="{{ route('print.total.order', $id) }}" class="btn btn-link btn-default btn-just-icon">
                       <i class="material-icons">print</i>
                    </a>
                </div>
                <div class="col-md-6 text-right">
                  <h4><strong>SubTotal:</strong>${{ $subtotal }}</h4>
                  <h4><strong>Propina Sugerida (10%):</strong>${{ $propina }}</h4>
                  <h2><strong>Total:</strong>${{ $total }}</h2>
                </div>
              </div>
          
          </div>
        </div>
  </div>
</div>

@endsection