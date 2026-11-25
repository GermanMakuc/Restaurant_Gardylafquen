<?php
  use App\Order;
  use App\State;
  use App\User;
  use Carbon\Carbon;
  Carbon::setLocale('es');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="<?php echo e(asset('/assets/img/apple-icon.png')); ?>">
  <link rel="icon" type="image/png" href="<?php echo e(asset('/assets/img/favicon.png')); ?>">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    Restaurant Gardylafquen
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
   <!-- CSS Files -->
  <link href="<?php echo e(asset('/assets/css/material-kit.min.css?v=2.1.1')); ?>" rel="stylesheet">
  <link href="<?php echo e(asset('/assets/css/smart_cart.css')); ?>" rel="stylesheet" />
  <link href="<?php echo e(asset('/assets/css/datetables.css')); ?>" rel="stylesheet" />
  <link href="<?php echo e(asset('/assets/css/daterangepicker.css')); ?>" rel="stylesheet" />

</head>

<body>

  <nav class="navbar bg-dark sticky-top navbar-expand-lg" id="sectionsNav">
    <div class="container">
      <div class="navbar-translate">
        <a class="navbar-brand" href="<?php echo e(route('index')); ?>">
            RESTAURANT <small>Gardylafquen</small>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" aria-expanded="false" aria-label="Toggle navigation">
          <span class="sr-only">Toggle navigation</span>
          <span class="navbar-toggler-icon"></span>
          <span class="navbar-toggler-icon"></span>
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
      <div id="menu" class="collapse navbar-collapse">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
              <a class="nav-link" href="<?php echo e(route('index')); ?>">
                <i class="material-icons">local_library</i>Orden 
                    <span class="sr-only">(current)</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo e(route('result.order')); ?>">
                <i class="material-icons">attach_money</i>Ventas 
                    <span class="sr-only">(current)</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo e(route('index.user')); ?>">
                <i class="material-icons">person_pin</i>Meseros 
                    <span class="sr-only">(current)</span>
                </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo e(route('index.product')); ?>">
                <i class="material-icons">restaurant</i>Productos 
                    <span class="sr-only">(current)</span>
                </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo e(route('index.category')); ?>">
                <i class="material-icons">layers</i>Categorias 
                    <span class="sr-only">(current)</span>
                </a>
            </li>

        </ul>
      </div>
    </div>
  </nav>

  <div class="main main-raised">

    <div class="section section-tabs">
      <div class="container">

        <?php if($message = Session::get('success')): ?>
        <div class="row">
          <div class="col-md-12">
            <div class="alert alert-success">
              <div class="container">
                <div class="alert-icon">
                  <i class="material-icons">check</i>
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true"><i class="material-icons">clear</i></span>
                </button>
                <b>Éxitoso:</b> <?php echo e($message); ?>

              </div>
            </div>
          </div>
        </div>

        <?php endif; ?>

      <?php if($message = Session::get('error')): ?>
      <div class="row">
          <div class="col-md-12">
            <div class="alert alert-danger">
              <div class="container">
                <div class="alert-icon">
                  <i class="material-icons">error_outline</i>
                </div>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true"><i class="material-icons">clear</i></span>
                </button>
                <b>Error:</b> <?php echo e($message); ?>

              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

        <?php if($errors->all()): ?>

        <div class="alert alert-danger">
          <div class="container">
            <div class="alert-icon">
              <i class="material-icons">error_outline</i>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
              <span aria-hidden="true"><i class="material-icons">clear</i></span>
            </button>
            <?php if($errors->has('table')): ?>
              <li>El campo mesa es obligatorio.</li>
            <?php endif; ?>
            <?php if($errors->has('user_id')): ?>
              <li>El campo mesero es obligatorio.</li>
            <?php endif; ?>
            <?php if($errors->has('notes')): ?>
              <li><?php echo e($errors->first('notes')); ?></li>
            <?php endif; ?>
          </div>
        </div>

        <?php endif; ?>

        <?php if($CountStates > 0): ?>

            <div class="row">
              <div class="col-md-12">
                  <div class="card card-nav-tabs">
                      <h4 class="card-header card-header-primary text-center">Órdenes en Espera</h4>
                      <div class="card-body">
                        <div class="material-datatables">

                            <table id="datatables" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>N° Órden</th>
                                        <th>N° Mesa</th>
                                        <th>Mesero</th>
                                        <th>Notas</th>
                                        <th>Emitida</th>
                                        <th class="disabled-sorting">Acciones</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>N° Órden</th>
                                        <th>N° Mesa</th>
                                        <th>Mesero</th>
                                        <th>Notas</th>
                                        <th>Emitida</th>
                                        <th>Acciones</th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                <?php $__currentLoopData = $states; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                  $value = User::find($s->user_id)->name;
                                ?>
                                <tr>
                                  <td><?php echo e($s->id); ?></td>
                                  <td><?php echo e($s->table); ?></td>
                                  <td><?php echo e($value); ?></td>
                                  <?php if($s->notes != null): ?>
                                  <td><b>Si</b></td>
                                  <?php else: ?>
                                  <td><b>No</b></td>
                                  <?php endif; ?>
                                  <td><?php echo e(Carbon::createFromFormat('Y-m-d H:i:s',$s->created_at)->diffForHumans()); ?></td>
                                  <td>
                                    <a href="<?php echo e(route('show.order', $s->id)); ?>" class="btn btn-link btn-warning btn-just-icon edit">
                                      <i class="material-icons">edit</i>
                                    </a>
                                    <a href="#" data-toggle="modal" data-target="#deleteModal-<?php echo e($s->id); ?>" class="btn btn-link btn-danger btn-just-icon remove">
                                  <i class="material-icons">close</i>
                                  </a>
                                    <a data-toggle="modal" data-target="#terminateModal-<?php echo e($s->id); ?>" class="btn btn-link btn-success btn-just-icon">
                                      <i class="material-icons">attach_money</i>
                                    </a>
                                  </td>
                                </tr>
              <!-- small modal -->
              <div class="modal fade modal-mini modal-primary" id="terminateModal-<?php echo e($s->id); ?>" tabindex="-1" role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-small">
                  <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="material-icons">clear</i></button>
                      </div>
                      <div class="modal-body">
                        <p>¿Estás seguro que quieres finalizar el pedido <?php echo e($s->id); ?>?, Si lo finalizas, ya no podrás editar el pedido y será dirigido al total de la cuenta. ¿Deseas continuar?</p>
                      </div>
                    <div class="modal-footer justify-content-center">
                      <button type="button" class="btn btn-link" data-dismiss="modal">No</button>
                      <a href="<?php echo e(route('total.order', $s->id)); ?>" class="btn btn-success btn-link">
                        Si
                        <div class="ripple-container"></div>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              <!--    end small modal -->

                            <!-- small modal -->
              <div class="modal fade modal-mini modal-primary" id="deleteModal-<?php echo e($s->id); ?>" tabindex="-1" role="dialog" data-backdrop="false">
                <div class="modal-dialog modal-small">
                  <div class="modal-content">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="material-icons">clear</i></button>
                      </div>
                      <div class="modal-body">
                        <p>¿Estás seguro que quieres borrar la orden <?php echo e($s->id); ?>?</p>
                      </div>
                    <div class="modal-footer justify-content-center">
                      <button type="button" class="btn btn-link" data-dismiss="modal">No</button>
                      <?php echo Form::open(['method' => 'POST','route' => ['destroy.order', $s->id]]); ?>

                      <button type="submit" class="btn btn-success btn-link">
                        Si
                        <div class="ripple-container"></div>
                      </button>
                      <?php echo Form::close(); ?>

                    </div>
                  </div>
                </div>
              </div>
              <!--    end small modal -->
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                              </tbody>
                            </table>
                          </div>
                      </div>
                    </div>
              </div>
          </div>

        <?php endif; ?>

          <div class="row">
              <div class="col-md-4">
                  <div class="card card-nav-tabs">
                      <h4 class="card-header card-header-primary">Balance Actual</h4>
                      <div class="card-body">
                              <!-- Cart submit form -->
                            <?php echo Form::open(array('route' => 'store.order','id' => 'form-new-order', 'method'=>'POST')); ?>

                            
                          <div class="col-md-12">
                            <div class="form-group">
                               <?php echo Form::selectRange('table', 1, 20, null,  [
                                  'id'=>'table' , 
                                  'class' => 'selectpicker',
                                  'data-toggle' => 'dropdown',
                                  'data-style' => 'select-with-transition',
                                  'required' => 'true', 
                                  'placeholder' => 'Seleccione Mesa',
                                ]); ?>

                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                               <?php echo Form::select('user_id', $names, $names, [
                                  'id'=>'user_id' , 
                                  'class' => 'selectpicker',
                                  'data-toggle' => 'dropdown',
                                  'data-style' => 'select-with-transition',
                                  'required' => 'true', 
                                  'placeholder' => 'Seleccione Mesero'
                                ]); ?>

                            </div>
                        </div>

                    <div class="col-md-12">
                        <div class="form-group">
                        <?php echo Form::textarea('notes', null, [
                          'class' => 'form-control',
                          'placeholder' => 'Añada una nota para esta órden u observación (Opcional)',
                          'length' => 500,
                          'rows' => '5', 
                          'maxLength' => '500'
                          ]); ?>

                        </div>
                    </div>
                    <!-- SmartCart element -->
                    <div id="smartcart"></div>

                      <?php echo Form::close(); ?>

                      </div>
                    </div>
              </div>
              <div class="col-md-8">
        <div class="button-group filters-button-group">
          <button class="btn btn-info btn-sm btn-default" data-filter="*">Mostrar Todos</button>
          <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button class="btn btn-info btn-sm" data-filter=".cate-<?php echo e($cate->id); ?>"><?php echo e($cate->name); ?></button>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
              <div class="grid">
                  <div class="col-md-12">
                  <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="card col-md-3 pull-left element-item cate-<?php echo e($p->category_id); ?>" data-category="cate-<?php echo e($p->category_id); ?>">
                        <div class="sc-product-item thumbnail">
                        <a href="#">
                          <img data-name="product_image" class="card-img-top sc-add-to-cart" src="<?php echo e(asset('photos'. $p->path)); ?>" alt="<?php echo e($p->name); ?>" data-toggle="tooltip" data-placement="top" title="<?php echo e($p->description); ?>">
                        </a>
                          <div class="card-body text-center">
                              <small data-name="product_name" class="card-title"><?php echo e($p->name); ?></small>
                              <p class="card-text"><small class="text-muted">$<?php echo e($p->price); ?></small></p>
                              <input name="product_price" value="<?php echo e($p->price); ?>" type="hidden" />
                              <input name="product_id" value="<?php echo e($p->id); ?>" type="hidden" />
                          </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </div>
                </div>
              </div>
          </div>
      </div>
    </div>
  </div>

  <!--   Core JS Files   -->
  <script src="<?php echo e(asset('/assets/js/core/jquery.min.js')); ?>" type="text/javascript"></script>
  <script src="<?php echo e(asset('/assets/js/jquery.smartCart.js')); ?>" type="text/javascript"></script>
  <script src="<?php echo e(asset('/assets/js/core/popper.min.js')); ?>" type="text/javascript"></script>
  <script src="<?php echo e(asset('/assets/js/isotope.pkgd.min.js')); ?>" type="text/javascript"></script>
  <script src="<?php echo e(asset('/assets/js/core/bootstrap-material-design.min.js')); ?>" type="text/javascript"></script>
  <script src="<?php echo e(asset('/assets/js/moment.min.js')); ?>" type="text/javascript"></script>
  <script src="<?php echo e(asset('/assets/js/moment-with-locales.min.js')); ?>" type="text/javascript"></script>
  <!--  Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
  <script src="<?php echo e(asset('/assets/js/plugins/bootstrap-datetimepicker.js')); ?>" type="text/javascript"></script>
  <!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
  <script src="<?php echo e(asset('/assets/js/plugins/nouislider.min.js')); ?>" type="text/javascript"></script>
  <!--  Google Maps Plugin    -->
  <script src="<?php echo e(asset('/assets/js/material-kit.js?v=2.0.5')); ?>" type="text/javascript"></script>
  <!-- Forms Validations Plugin -->
  <script src="<?php echo e(asset('/assets/js/plugins/jquery.validate.min.js')); ?>"></script>
  <!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
  <script src="<?php echo e(asset('/assets/js/plugins/jquery.datatables.js')); ?>"></script>
  <!--  Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
  <script src="<?php echo e(asset('/assets/js/plugins/jquery.select-bootstrap.js')); ?>"></script>
    <!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
  <script src="<?php echo e(asset('/assets/js/plugins/jasny-bootstrap.min.js')); ?>"></script>
  <script src="<?php echo e(asset('/assets/js/daterangepicker.min.js')); ?>" type="text/javascript"></script>
  <script src="<?php echo e(asset('/assets/js/print.min.js')); ?>" type="text/javascript"></script>
  <script src="<?php echo e(asset('/assets/js/script.js')); ?>" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            // Initialize Smart Cart        
            $('#smartcart').smartCart();
        });
    </script>

</body>

</html>