@extends('layouts.app')
@php
  use Carbon\Carbon;
  use App\Category;
  use App\Product;
  use App\User;
  Carbon::setLocale('es');
@endphp

@section('content')

<div class="modal fade" id="createModal" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Agregar Nuevo Mesero</h4>
                  </div>
                </div>
                {!! Form::open(array('route' => 'store.user','id' => 'form-new-product', 'method'=>'POST')) !!}
                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-row">

                                <div class="form-group col-md-12">
                                  {!! Form::text('name', null, array('id'=>'name','class' => 'form-control',
                                   'placeholder' =>'Nombre', 'required' => 'true')) !!}
                                </div>

                                <div class="form-group col-md-12">
                                  {!! Form::text('email', null, array('id'=>'email', 'name'=>'email', 'class' => 'form-control',
                                    'required' => 'true', 'email' => 'true', 'placeholder' => 'E-Mail')) !!}
                                </div>
                                  
                            </div>

                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Agregar</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

      <div class="col-md-12">
          <div class="card">
            <div class="card-header card-header-primary card-header-icon">
              <h4 class="card-title">Listado de Meseros</h4>
            </div>
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
            @if ($errors->has('name'))
              <li>{{ $errors->first('name') }}</li>
            @endif
            @if ($errors->has('email'))
              <li>{{ $errors->first('email') }}</li>
            @endif
          </div>
        </div>

        @endif

                  <div class="toolbar text-left">
                      <!--        Here you can write extra buttons/actions for the toolbar              -->
                      <a href="#" class="btn btn-rose btn-sm" data-toggle="modal" data-target="#createModal">Agregar Nuevo Mesero</a>
                  </div>
                  <div class="material-datatables">

                  <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                      <thead>
                          <tr>
                              <th>Nombre</th>
                              <th>Email</th>
                              <th class="disabled-sorting">Acciones</th>
                          </tr>
                      </thead>
                      <tfoot>
                          <tr>
                              <th>Nombre</th>
                              <th>Email</th>
                              <th>Acciones</th>
                          </tr>
                      </tfoot>
                      <tbody>
                      @foreach ($meseros as $m)

                      @php
                        $value = User::find($m->id);
                      @endphp

              <!-- small modal -->
              <div class="modal fade modal-mini modal-primary" id="deleteModal-{{ $m->id }}" tabindex="-1" role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-small">
                  <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="material-icons">clear</i></button>
                      </div>
                      <div class="modal-body">
                        <p>¿Estás seguro que quieres borrar al mesero {{ $m->name }}?</p>
                      </div>
                    <div class="modal-footer justify-content-center">
                      <button type="button" class="btn btn-link" data-dismiss="modal">No</button>
                      {!! Form::open(['method' => 'POST','route' => ['destroy.user', $m->id]]) !!}
                      <button type="submit" class="btn btn-success btn-link">
                        Si
                        <div class="ripple-container"></div>
                      </button>
                      {!! Form::close() !!}
                    </div>
                  </div>
                </div>
              </div>
              <!--    end small modal -->


  <div class="modal fade" id="editModal-{{ $m->id }}" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Editar Mesero</h4>
                  </div>
                </div>
                {!! Form::model($value, ['method' => 'POST','route' => ['update.user', $m->id]]) !!}
                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-row">

                                <div class="form-group col-md-12">
                                  {!! Form::text('name', null, array('id'=>'name','class' => 'form-control',
                                   'placeholder' =>'Nombre', 'required' => 'true')) !!}
                                </div>

                                <div class="form-group col-md-12">
                                  {!! Form::text('email', null, array('id'=>'email', 'name'=>'email', 'class' => 'form-control',
                                    'required' => 'true', 'email' => 'true', 'placeholder' => 'E-Mmail')) !!}
                                </div>

                                  
                            </div>

                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Editar</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

                      <tr>
                        <td>{{ $m->name}}</td>
                        <td>{{ $m->email}}</td>
                        <td>
                            <a data-toggle="modal" data-target="#editModal-{{ $m->id }}" class="btn btn-link btn-warning btn-just-icon edit">
                              <i class="material-icons">edit</i>
                            </a>
                            <a href="#" data-toggle="modal" data-target="#deleteModal-{{ $m->id }}" class="btn btn-link btn-danger btn-just-icon remove">
                            <i class="material-icons">close</i>
                            </a>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                  </div>
              </div><!-- end content-->
          </div><!--  end card  -->
      </div> <!-- end col-md-12 -->

@endsection
