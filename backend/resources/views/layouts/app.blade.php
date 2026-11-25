<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('/assets/img/apple-icon.png') }}">
  <link rel="icon" type="image/png" href="{{ asset('/assets/img/favicon.png') }}">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <title>
    Restaurant Gardylafquen
  </title>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
  <!--     Fonts and icons     -->
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
  <!-- CSS Files -->
  <link href="{{ asset('/assets/css/material-kit.min.css?v=2.1.1') }}" rel="stylesheet">
  <link href="{{ asset('/assets/css/smart_cart.css') }}" rel="stylesheet" />
  <link href="{{ asset('/assets/css/datetables.css') }}" rel="stylesheet" />
  <link href="{{ asset('/assets/css/daterangepicker.css') }}" rel="stylesheet" />
  <meta content="{{ csrf_token() }}" name="csrf-token">
</head>

<body class="index-page sidebar-collapse">
  <nav class="navbar bg-dark sticky-top navbar-expand-lg" id="sectionsNav">
    <div class="container">
      <div class="navbar-translate">
        <a class="navbar-brand" href="{{ route('index') }}">
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
              <a class="nav-link" href="{{ route('index') }}">
                <i class="material-icons">local_library</i>Orden 
                    <span class="sr-only">(current)</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('result.order') }}">
                <i class="material-icons">attach_money</i>Ventas 
                    <span class="sr-only">(current)</span>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('index.user') }}">
                <i class="material-icons">person_pin</i>Meseros 
                    <span class="sr-only">(current)</span>
                </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('index.product') }}">
                <i class="material-icons">restaurant</i>Productos 
                    <span class="sr-only">(current)</span>
                </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ route('index.category') }}">
                <i class="material-icons">layers</i>Categorias 
                    <span class="sr-only">(current)</span>
                </a>
            </li>
{{--           <li class="dropdown nav-item">
            <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
              <i class="material-icons">apps</i> Components
            </a>
            <div class="dropdown-menu dropdown-with-icons">
              <a href="./index.html" class="dropdown-item">
                <i class="material-icons">layers</i> All Components
              </a>
              <a href="https://demos.creative-tim.com/material-kit/docs/2.1/getting-started/introduction.html" class="dropdown-item">
                <i class="material-icons">content_paste</i> Documentation
              </a>
            </div>
          </li> --}}
        </ul>
      </div>
    </div>
  </nav>

  <div class="main main-raised">

    <div class="section section-tabs">
      <div class="container">
        
        @if($message = Session::get('success'))
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
                <b>Éxitoso:</b> {{ $message }}
              </div>
            </div>
          </div>
        </div>

        @endif

      @if($message = Session::get('error'))
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
                <b>Error:</b> {{ $message }}
              </div>
            </div>
          </div>
        </div>
      @endif
              @yield('content')
      </div>
    </div>
  </div>

  <!--   Core JS Files   -->
  <script src="{{ asset('/assets/js/core/jquery.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('/assets/js/jquery.smartCart.js') }}" type="text/javascript"></script>
  <script src="{{ asset('/assets/js/core/popper.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('/assets/js/isotope.pkgd.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('/assets/js/core/bootstrap-material-design.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('/assets/js/moment.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('/assets/js/moment-with-locales.min.js') }}" type="text/javascript"></script>
  <!--  Plugin for the Datepicker, full documentation here: https://github.com/Eonasdan/bootstrap-datetimepicker -->
  <script src="{{ asset('/assets/js/plugins/bootstrap-datetimepicker.js') }}" type="text/javascript"></script>
  <!--  Plugin for the Sliders, full documentation here: http://refreshless.com/nouislider/ -->
  <script src="{{ asset('/assets/js/plugins/nouislider.min.js') }}" type="text/javascript"></script>
  <!--  Google Maps Plugin    -->
  <script src="{{ asset('/assets/js/material-kit.js?v=2.0.5') }}" type="text/javascript"></script>
  <!-- Forms Validations Plugin -->
  <script src="{{ asset('/assets/js/plugins/jquery.validate.min.js') }}"></script>
  <!--  DataTables.net Plugin, full documentation here: https://datatables.net/    -->
  <script src="{{ asset('/assets/js/plugins/jquery.datatables.js') }}"></script>
  <!--  Plugin for Select, full documentation here: http://silviomoreto.github.io/bootstrap-select -->
  <script src="{{ asset('/assets/js/plugins/jquery.select-bootstrap.js') }}"></script>
    <!-- Plugin for Fileupload, full documentation here: http://www.jasny.net/bootstrap/javascript/#fileinput -->
  <script src="{{ asset('/assets/js/plugins/jasny-bootstrap.min.js') }}"></script>
  <script src="{{ asset('/assets/js/daterangepicker.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('/assets/js/print.min.js') }}" type="text/javascript"></script>
  <script src="{{ asset('/assets/js/script.js') }}" type="text/javascript"></script>

</body>
@yield('cart')
</html>