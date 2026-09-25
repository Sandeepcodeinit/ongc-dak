<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

@php
    // Get logged-in user from Laravel session
    $user = session('user');

    $userIdd = data_get($user, 'id', 0);
    $usertype = data_get($user, 'user_type', 0);
    $user_email_id = data_get($user, 'email', '');

    // Sidebar state
    $sideBarCollapse = session('sideBarCollapse');

    $sideBarCollapseTxt = '';

    if ($sideBarCollapse == 0) {
        $sideBarCollapseTxt = 'sidebar-collapse';
    }
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | {{ config('app.name') }}</title>

    <link rel="shortcut icon" href="{{ asset('pages/images/logo.png') }}" type="image/x-icon" />
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">  <!-- BS Stepper -->
    <link rel="stylesheet" href="{{ asset('pages/plugins/bs-stepper/css/bs-stepper.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('pages/dist/css/adminlte.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('pages/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('pages/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('pages/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('pages/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('pages/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    @yield('css')
    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('pages/plugins/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('pages/others_ongc.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('pages/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('pages/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('pages/plugins/select2/css/select2.min.css') }}">
    <script src="{{ asset('pages/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('pages/sweetalert2.min.js') }}"></script>
    <style>    
        
      @keyframes rotate-loading {
        0%  { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      :root {
        --wd_ht: 100px;
        --logo_mr: 4%;
        --logo_wd: 92%;  
      }
      .loading-container {
        background-color: #797777b0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        position: fixed;
        top:0;
        left:0;
        width:100%;
        z-index: 1111;
      }

      .logo {
        position: absolute; 
        width: var(--wd_ht);
        height: var(--wd_ht);
      }

      .logo img{    width: var(--logo_wd); margin: var(--logo_mr); }
      /*   Lodear */
      .custom-file-sm, .custom-file-label-sm, .custom-file-input-sm{
        height: calc(1.8125rem + 2px) !important;
        padding: 0.25rem 0.5rem !important;
        font-size: .875rem !important;
        line-height: 1.5 !important;
        border-radius: 0.2rem !important;
      }
      .iFile{ margin: 0 2px;  }
      .select2-selection__choice{ color:#000 !important; }
      /*
      .login-csr {
        background: url(/pages/images/ongc-csr.jpeg);
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
      }
      .csr-light {
        position: absolute;
        width: 100%;
        height: 100%;
        background: #ffffff73;
        top: 0;
      }*/
        .sidebar-mini.sidebar-collapse .content-wrapper,
        .sidebar-mini.sidebar-collapse .main-footer,
        .sidebar-mini.sidebar-collapse .main-header,
        .sidebar-mini .content-wrapper,
        .sidebar-mini .main-footer,
        .sidebar-mini .main-header {
          margin-left: 0 !important;
        }
      .verifyDoc{ display:none;  }
      .navbar-nav .nav-link{  font-weight:bold; }
      .btn-xs {
        padding: 0.125rem 0.25rem !important;
      }
      input:focus, textarea:focus, select:focus, .select2:focus, .select2-container--default.select2-container--focus .select2-selection--single 
      {
        border-color: #ffc107 !important;
      }

      .maximizeButton {
        position: absolute;
        top: 25px;
        right: 10px;
        z-index: 9999;
        background: #fff;
        color: black;
      }
      .minimizeButton {
        position: fixed;
        top: 35px;
        right: 20px;
        z-index: 9999;
        background: #fff;
        color: black;
      }
      .input-error {
        border-color: #dc3545 !important;
      }
      .fullscreen {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050; /* Ensure it is above other content */
        background: white;
      }
      .mdlPdfFile.fullscreen {
        height: 100%; z-index: 99991;
      }

      .fileUpload{ cursor:pointer; }
        .dz-remove{ z-index: 9999; position: relative; }
        .dropzone .dz-remove {
            display: inline-block;
            padding: 6px 12px;
            margin: 5px 0;
            background-color: #e74c3c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none !important; /* Remove underline */
        }

        .dropzone .dz-remove:hover {
            background-color: #c0392b;
            text-decoration: none !important;
        }
        .dropzone .dz-preview.dz-processing .dz-progress {
          margin-top: 4px;
        }
        .dropzone .dz-preview.dz-error .dz-error-message {
          opacity: 1 !important;
          pointer-events: auto;
          top: 165px !important; width: 300px !important;
        }

        section.content::before {
          content: "";
          position: absolute;
          inset: 0;
          background-image: url(/pages/images/ongc-hand.png);
          background-size: contain;
          background-repeat: no-repeat;
          opacity: 0.2;/* Adjust this for the fade effect or opacity */
          z-index: -1;
          background-position: 50% 32%;
      }
      body {
          background: #eff7da;  /* change the color according you */
      }

      section.content {
          position: relative;
          z-index: 1;
      }


      section, .content-wrapper{
          background-color: transparent !important;
          /* Remove the white backgroud color */
      }

      body .card {
          background-color: transparent !important;
      }
      .headerNotification{
        position: sticky;
        top: 0;
        z-index: 1040;
        height: 36px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 600;
        font-size: 16px;
        white-space: nowrap;
        padding: 4px 0; box-shadow: rgb(149 157 165 / 32%) 0px 8px 24px;
        background: linear-gradient(135deg, #84363a, #b95f63);
        color: #fff;
            box-shadow: inset 0 -7px 6px rgb(0 0 0 / 11%);
      }
      .headerNotification .marquee {
        display: inline-block;
        white-space: nowrap;
        animation: marquee 50s linear infinite;
      }
      .headerNotification:hover .marquee {
        animation-play-state: paused;
      }
      @keyframes marquee {
          0% {
              transform: translateX(90%);
          }
        100% {
              transform: translateX(-95%);
          }
      }
      .main-sidebar{
        top: 36px !important;
      }
      [class*=icheck-]>input:first-child:disabled+label {
        opacity: 0.9 !important;
      }

      .loading-container {
        background-color: rgba(0, 0, 0, 0.65);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1111;
      }

      .loading-container .logo {
        position: absolute;
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .loading-container .logo img {
        width: 92%;
        height: 92%;
        object-fit: contain;
      }

      .loading {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 2px solid transparent;
        border-color: transparent #fff transparent #fff;
        animation: rotate-loading 1.5s linear infinite;
        transform-origin: 50% 50%;
      }
    </style>
</head>
<!--<body class="control-sidebar-slide-open sidebar-collapse layout-navbar-fixed layout-fixed sidebar-mini ">-->
<body class="sidebar-mini layout-fixed {{ $sideBarCollapseTxt }}">
  <div class="loading-container">
    <div class="logo">
      <img src="{{ asset('pages/images/logo2.png') }}" alt="Logo">
    </div>
    <div class="loading"></div>
  </div>

  <!-- Site wrapper -->
  <div class="wrapper">
      @if(!empty(@$user_email_id))
        @include('_siderBar')    
      @endif
      @yield('content')

  </div>
</body>
<!-- jQuery -->
<script src="{{ asset('pages/plugins/jquery/jquery.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('pages/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('pages/plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('pages/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<!-- BS-Stepper -->
<script src="{{ asset('pages/plugins/bs-stepper/js/bs-stepper.min.js') }}"></script>
<!-- DataTables  & Plugins -->
<script src="{{ asset('pages/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>

<script src="{{ asset('pages/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('pages/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('pages/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('pages/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('pages/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('pages/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('pages/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('pages/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('pages/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('pages/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('pages/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('pages/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('pages/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
<!-- Toastr -->
<script src="{{ asset('pages/plugins/toastr/toastr.min.js') }}"></script>
<script src="{{ asset('pages/common.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('pages/dist/js/adminlte.min.js') }}"></script>
<script>
$(function () {
  bsCustomFileInput.init();
  $('[data-toggle="tooltip"]').tooltip({
    html: true
  }); 
});
$('.select2').select2();
$(document).ready(function(){
        $('.bootstrapSwitch').bootstrapSwitch();
    });
@if(isset($errors) && $errors->any())
    @foreach($errors->all() as $error)
  show_msgT(2, "{{ $error }}");
  @endforeach
@endif
  @if(session('success') && session('success') != 'S')
    show_msgT(1, "{{ session('success') }}");
  @endif
  @if(session('error') && session('error') != 'S')
    show_msgT(2, "{{ session('error') }}");
  @endif

  setTimeout(function(){
    $('.loading-container').css('display', 'none');
  }, 1000);

</script>
@yield('javascript')
</html>