@extends('layouts.app')
@php
  use Carbon\Carbon;
  use App\Category;
  use App\Product;
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

                    <h4 class="card-title">Crea Nuevo Producto</h4>
                  </div>
                </div>
                <form id ="form-new-product" action="{{ route('store.product') }}" method="post" enctype="multipart/form-data">
              {{ csrf_field() }}
                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-row">

                                <div class="form-group col-md-12">
                                  {!! Form::text('name', null, array('id'=>'name','class' => 'form-control',
                                   'placeholder' =>'Nombre', 'required' => 'true')) !!}
                                </div>

                                <div class="form-group col-md-12">
                                  {!! Form::select('category_id', $categories, null, [
                                  'id'=>'categories' , 
                                  'class' => 'selectpicker',
                                  'data-toggle' => 'dropdown',
                                  'data-style' => 'select-with-transition',
                                  'required' => 'true', 
                                  'placeholder' => 'Seleccione Categoria'
                                ]) !!}
                                </div>

                                <div class="form-group col-md-12">
                                  {!! Form::textarea('description', null, ['id'=>'description', 'name'=>'description',
                                    'class' => 'form-control','length' => 500,'rows' => '5', 'maxLength' => '500', 
                                    'placeholder' => 'Descripción del producto (Opcional)']) !!}
                                </div>

                                <div class="form-group col-md-12">
                                  {!! Form::text('price', null, array('id'=>'price', 'name'=>'price', 'class' => 'form-control',
                                    'required' => 'true', 'number' => 'true', 'placeholder' => '$ Valor del Producto')) !!}
                                </div>

                                <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                   <div class="fileinput-new thumbnail img-raised">
                                  <img src="{{ asset('/assets/img/image_placeholder.jpg') }}">
                                   </div>
                                   <div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
                                   <div>
                                  <span class="btn btn-raised btn-round btn-default btn-file">
                                     <span class="fileinput-new">Seleccionar Imágen</span>
                                     <span class="fileinput-exists">Cambiar</span>
                                     <input id="photo" type="file" name="photo" />
                                  </span>
                                        <a href="#" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
                                        <i class="fa fa-times"></i> Remover</a>
                                   </div>
                                </div>
                                  
                            </div>

                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Agregar</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

      <div class="col-md-12">
          <div class="card">
            <div class="card-header card-header-primary card-header-icon">
              <h4 class="card-title">Listado de Productos</h4>
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
            @if ($errors->has('photo'))
            <li>{{ $errors->first('photo') }}</li>
            @endif
            @if ($errors->has('image'))
            <li>{{ $errors->first('image') }}</li>
            @endif
            @if ($errors->has('category_id'))
            <li>{{ $errors->first('category_id') }}</li>
            @endif
            @if ($errors->has('price'))
            <li>{{ $errors->first('price') }}</li>
            @endif
            @if ($errors->has('description'))
            <li>{{ $errors->first('description') }}</li>
            @endif
          </div>
        </div>

        @endif

                  <div class="toolbar text-left">
                      <!--        Here you can write extra buttons/actions for the toolbar              -->
                      <a href="#" class="btn btn-rose btn-sm" data-toggle="modal" data-target="#createModal">Agregar Nuevo Producto</a>
                  </div>
                  <div class="material-datatables">

                  <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                      <thead>
                          <tr>
                              <th>Nombre</th>
                              <th>Valor Unidad</th>
                              <th>Categoria</th>
                              <th>Imágen</th>
                              <th class="disabled-sorting">Acciones</th>
                          </tr>
                      </thead>
                      <tfoot>
                          <tr>
                              <th>Nombre</th>
                              <th>Valor Unidad</th>
                              <th>Categoria</th>
                              <th>Imágen</th>
                              <th>Acciones</th>
                          </tr>
                      </tfoot>
                      <tbody>
                      @foreach ($products as $p)

                      @php
                        $category = Category::find($p->category_id);
                        $value = Product::find($p->id);
                      @endphp

              <!-- small modal -->
              <div class="modal fade modal-mini modal-primary" id="deleteModal-{{ $p->id }}" tabindex="-1" role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-small">
                  <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="material-icons">clear</i></button>
                      </div>
                      <div class="modal-body">
                        <p>¿Estás seguro que quieres borrar el producto {{ $p->name }}?</p>
                      </div>
                    <div class="modal-footer justify-content-center">
                      <button type="button" class="btn btn-link" data-dismiss="modal">No</button>
                      {!! Form::open(['method' => 'POST','route' => ['destroy.product', $p->id]]) !!}
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


  <div class="modal fade" id="editModal-{{ $p->id }}" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Editar Producto</h4>
                  </div>
                </div>
                
                {!! Form::model($value, ['method' => 'POST','route' => ['update.product', $p->id]]) !!}

                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-row">

                                <div class="form-group col-md-12">
                                  {!! Form::text('name', null, array('id'=>'name','class' => 'form-control',
                                   'placeholder' =>'Nombre', 'required' => 'true')) !!}
                                </div>

                                <div class="form-group col-md-12">
                                  <label for="category_id">Categoria</label>
                                    {!! Form::select('category_id', $categories, $value->category_id, [
                                  'id'=>'category_id',
                                  'name'=>'category_id',
                                  'class' =>'form-control'
                                  ]) !!}
                                </div>

                                <div class="form-group col-md-12">
                                  {!! Form::textarea('description', null, ['id'=>'description', 'name'=>'description',
                                    'class' => 'form-control','length' => 500,'rows' => '5', 'maxLength' => '500', 
                                    'placeholder' => 'Descripción del producto (Opcional)']) !!}
                                </div>

                                <div class="form-group col-md-12">
                                  {!! Form::text('price', null, array('id'=>'price', 'name'=>'price', 'class' => 'form-control',
                                    'required' => 'true', 'number' => 'true', 'placeholder' => '$ Valor del Producto')) !!}
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

  <div class="modal fade" id="image-{{ $p->id }}" tabindex="-1" role="dialog" data-backdrop="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="card card-signup card-plain">
                <div class="modal-header">
                  <div class="card-header card-header-primary text-center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                      <i class="material-icons">clear</i>
                    </button>

                    <h4 class="card-title">Editar Imágen</h4>
                  </div>
                </div>
                
                <form id ="form-new-product" action="{{ route('update.product.image', $p->id) }}" method="post" enctype="multipart/form-data">
              {{ csrf_field() }}

                <div class="modal-body">
                    
                        <div class="card-body">

                            <div class="form-row">

                                <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                   <div class="fileinput-new thumbnail img-raised">
                                  <img src="{{ asset('photos'. $p->path)  }}">
                                   </div>
                                   <div class="fileinput-preview fileinput-exists thumbnail img-raised"></div>
                                   <div>
                                  <span class="btn btn-raised btn-round btn-default btn-file">
                                     <span class="fileinput-new">Seleccionar Imágen</span>
                                     <span class="fileinput-exists">Cambiar</span>
                                     <input id="image" type="file" name="image" />
                                  </span>
                                        <a href="#" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput">
                                        <i class="fa fa-times"></i> Remover</a>
                                   </div>
                                </div>

                                  
                            </div>

                        </div>

                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-primary btn-link btn-wd btn-lg">Cambiar Imágen</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>

                      <tr>
                        <td>{{ $p->name}}</td>
                        <td>${{ $p->price}}</td>
                        <td>{{ $category->name }}</td>
                        <td><img width="100px" class="img-thumbnail" src="{{ asset('photos'. $p->path)  }}"></td>
                        <td>
                            <a data-toggle="modal" data-target="#editModal-{{ $p->id }}" class="btn btn-link btn-warning btn-just-icon edit">
                              <i class="material-icons">edit</i>
                            </a>
                            <a href="#" data-toggle="modal" data-target="#image-{{ $p->id }}" class="btn btn-link btn-info btn-just-icon remove">
                               <i class="material-icons">image</i>
                            </a>
                            <a href="#" data-toggle="modal" data-target="#deleteModal-{{ $p->id }}" class="btn btn-link btn-danger btn-just-icon remove">
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
