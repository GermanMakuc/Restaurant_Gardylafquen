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
  <meta content="<?php echo e(csrf_token()); ?>" name="csrf-token">
</head>

<body class="index-page sidebar-collapse">
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
            <li class="nav-item">
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
              <?php echo $__env->yieldContent('content'); ?>
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

</body>
<?php echo $__env->yieldContent('cart'); ?>
</html>