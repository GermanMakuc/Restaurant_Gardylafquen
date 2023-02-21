@extends('layouts.app')

@section('content')

@php
  use Carbon\Carbon;
  use App\Product;
  Carbon::setLocale('es');
@endphp

        @if ($errors->all())

        <div class="alert alert-danger">
          <div class="container">
            <div class="alert-icon">
              <i class="material-icons">error_outline</i>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true"><i class="material-icons">clear</i></span>
            </button>
            @if ($errors->has('table'))
              <li>El campo mesa es obligatorio.</li>
            @endif
            @if ($errors->has('user_id'))
              <li>El campo mesero es obligatorio.</li>
            @endif
            @if ($errors->has('notes'))
              <li>{{ $errors->first('notes') }}</li>
            @endif
          </div>
        </div>

        @endif

<div class="row">
  <div class="col-md-4">
      <div class="card card-nav-tabs">
          <h4 class="card-header card-header-primary">Balance Actual Orden N°: <b>{{ $id }}</b></h4>
          <div class="card-body">

                  <!-- Cart submit form -->
          {!! Form::model($state, ['id' => 'form-new-order', 'method' => 'POST','route' => ['update.order', $id]]) !!}

          <div class="col-md-12">
              <div class="form-group">
                 {!! Form::selectRange('table', 1, 20, null,  [
                    'id'=>'table' , 
                    'class' => 'selectpicker',
                    'data-toggle' => 'dropdown',
                    'data-style' => 'select-with-transition',
                    'required' => 'true', 
                    'placeholder' => 'Seleccione Mesa',
                  ]) !!}
              </div>
          </div>

          <div class="col-md-12">
              <div class="form-group">
                 {!! Form::select('user_id', $names, $state->user_id, [
                    'id'=>'user_id' , 
                    'class' => 'selectpicker',
                    'data-toggle' => 'dropdown',
                    'data-style' => 'select-with-transition',
                    'required' => 'true', 
                    'placeholder' => 'Seleccione Mesero'
                  ]) !!}
              </div>
          </div>

          <div class="col-md-12">
              <div class="form-group">
              {!! Form::textarea('notes', null, [
                'class' => 'form-control',
                'placeholder' => 'Añada una nota para esta órden u observación (Opcional)',
                'length' => 500,
                'rows' => '5', 
                'maxLength' => '500'
                ]) !!}
              </div>
          </div>

          <!-- SmartCart element -->
          <div id="smartcart"></div>
          {!! Form::close() !!}
          
          <div class="row">
          	<div class="col-md-12">
	         <a target="_blank" href="{{ route('print.order', $id) }}" class="btn btn-link btn-default btn-just-icon pull-right">
		         <i class="material-icons">print</i>
		      </a>
	      	</div>
	      </div>
          </div>
      </div>
  </div>
              <div class="col-md-8">

        <div class="button-group filters-button-group">
          <button class="btn btn-info btn-sm btn-default" data-filter="*">Mostrar Todos</button>
          @foreach($categories as $cate)
            <button class="btn btn-info btn-sm" data-filter=".cate-{{ $cate->id }}">{{ $cate->name }}</button>
          @endforeach
        </div>
              <div class="grid">
                  <div class="col-md-12">
                  @foreach($products as $p)
                    <div class="card col-md-3 pull-left element-item cate-{{ $p->category_id }}" data-category="cate-{{ $p->category_id }}">
                        <div class="sc-product-item thumbnail">
                        <a href="#">
                          <img data-name="product_image" class="card-img-top sc-add-to-cart" src="{{ asset('photos'. $p->path)  }}" alt="{{ $p->name }}" data-toggle="tooltip" data-placement="top" title="{{ $p->description }}">
                        </a>
                          <div class="card-body text-center">
                              <small data-name="product_name" class="card-title">{{ $p->name }}</small>
                              <p class="card-text"><small class="text-muted">${{ $p->price }}</small></p>
                              <input name="product_price" value="{{ $p->price }}" type="hidden" />
                              <input name="product_id" value="{{ $p->id }}" type="hidden" />
                          </div>
                        </div>
                    </div>
                    @endforeach
                  </div>
                </div>
              </div>
</div>

@endsection

@section('cart')

    <script type="text/javascript">
        $(document).ready(function(){

        	// Initialize the leaveStep event
		      /*$("#smartcart").on("cartEmpty", function(e) {
		         alert("Cart is empty");
		      });*/

                $('#smartcart').smartCart({
                    cart: [
                    @foreach($order as $d)
                		@php
        							$value = Product::find($d->product_id);
        						@endphp
	                    {
						    product_price: {{ $value->price}},
						    product_image: '{{ asset('photos'. $value->path) }}',
						    product_quantity: {{ $d->num}},
						    product_name: '{{ $value->name}}',
						    product_id: {{ $value->id}}
						},
					@endforeach
                    ],
                    lang: {  // Language variables
		                cartTitle: "",
		                checkout: 'Modificar Orden',
		                clear: 'Limpiar',
		                subtotal: 'Total Parcial:',
		                cartRemove:'×',
		                cartEmpty: 'No hay productos seleccionados'
            	},
            });
		});
    </script>

@stop