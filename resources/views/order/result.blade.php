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

<div class="modal fade" id="searchModal" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Búsqueda por Rango</h4>
                  </div>
                </div>
                {!! Form::open(array('route' => 'search.order','method'=>'POST')) !!}
                <div class="modal-body">
                    
                        <div class="card-body">

                          <div class="form-group bmd-form-group col-md-12">
                                <div class="input-group">
                                  <div class="input-group-prepend">
                                    <div class="input-group-text"><i class="material-icons">zoom_in</i></div>
                                  </div>
                                  <input id="datefilter" class="form-control" type="text" name="datefilter" value="" placeholder="Ingrese rango de fechas para la búsqueda" />
                                </div>
                            </div>

                            {!! Form::hidden('start', null, array('id'=>'start')) !!}
                            {!! Form::hidden('end', null, array('id'=>'end')) !!}
                            
                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Buscar</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="otherSearch" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Búsqueda Alternativa</h4>
                  </div>
                </div>
                {!! Form::open(array('route' => 'search.alter.order','method'=>'POST')) !!}
                <div class="modal-body">
                    
                        <div class="card-body">

                          <div class="form-group bmd-form-group col-md-12">
                                <div class="input-group">
                                  <div id="reportrange" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc; width: 100%">
                                      <i class="fa fa-calendar"></i>&nbsp;
                                      <span></span> <i class="fa fa-caret-down"></i>
                                  </div>
                                </div>
                            </div>

                            {!! Form::hidden('start_alternative', null, array('id'=>'start_alternative')) !!}
                            {!! Form::hidden('end_alternative', null, array('id'=>'end_alternative')) !!}
                            
                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Buscar</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<div class="row">
  <div class="col-md-12">
      <div class="card card-nav-tabs">
          <h4 class="card-header card-header-primary text-center">Ventas Anuales <b>{{ $year }}</b></h4>
          <div class="card-body">

      @if ($errors->all())

        <div class="alert alert-danger">
          <div class="container">
            <div class="alert-icon">
              <i class="material-icons">error_outline</i>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true"><i class="material-icons">clear</i></span>
            </button>
            @if ($errors->has('start'))
              <li>{{ $errors->first('start') }}</li>
            @endif
            @if ($errors->has('end'))
              <li>{{ $errors->first('end') }}</li>
            @endif
            @if ($errors->has('start_alternative'))
              <li>{{ $errors->first('start_alternative') }}</li>
            @endif
            @if ($errors->has('end_alternative'))
              <li>{{ $errors->first('end_alternative') }}</li>
            @endif
          </div>
        </div>

        @endif
              <div class="toolbar text-center">
                  <!--        Here you can write extra buttons/actions for the toolbar              -->
                  <a href="#" class="btn btn-rose btn-sm" data-toggle="modal" data-target="#searchModal">Búsqueda por Rango</a>
                  <a href="#" class="btn btn-rose btn-sm" data-toggle="modal" data-target="#otherSearch">Búsquedas Alternativa</a>
              </div>
                          
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
          </div>
        </div>
  </div>
</div>

@endsection