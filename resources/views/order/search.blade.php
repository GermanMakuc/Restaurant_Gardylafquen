@extends('layouts.app')
@php
  use Carbon\Carbon;
  use App\Product;
  use App\Ticket;
  use App\User;
  use App\State;
  Carbon::setLocale('es');
@endphp
@section('content')

<div class="row">
  <div class="col-md-12">
      <div class="card card-nav-tabs">
          <h4 class="card-header card-header-primary">Ventas Dentro del rango: <b>{{ $start }}</b> Hasta <b>{{ $end }}</b></h4>
          <div class="card-body">
   
              <div class="material-datatables">

                  <table id="datatables1" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                      <thead>
                          <tr>
                              <th>N° Órden</th>
                              <th>Mesero</th>
                              <th>SubTotal</th>
                              <th>Total</th>
                              <th>Emitida</th>
                              <th>Acciones</th>
                          </tr>
                      </thead>
                      <tfoot>
                          <tr>
                              <th>N° Órden</th>
                              <th>Mesero</th>
                              <th>SubTotal</th>
                              <th>Total</th>
                              <th>Emitida</th>
                              <th class="disabled-sorting">Acciones</th>
                          </tr>
                      </tfoot>
                      <tbody>
                      @foreach ($results as $r)
                      @php
                        $state = State::find($r->state_id);
                        $owner = User::find($state->user_id)->name;
                      @endphp
                      <tr>
                        <td>{{ $r->state_id }}</td>
                        <td>{{ $owner }}</td>
                        <td>${{ $r->subtotal }}</td>
                        <td>${{ $r->total }}</td>
                       
                        <td>{{ Carbon::parse($r->created_at)->format('Y-m-d') }}</td>
                        <td>
                          <a href="{{ route('total.order', $r->state_id) }}" class="btn btn-link btn-warning btn-just-icon edit">
                            <i class="material-icons">zoom_in</i>
                          </a>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
              </div>
              <div class="row">
                <div class="col-md-6 text-left">
                      <a href="{{ route('result.order') }}" class="btn btn-rose btn-sm">Volver a Ventas</a>
                </div>
              </div>
          </div>
        </div>
  </div>
</div>

@endsection