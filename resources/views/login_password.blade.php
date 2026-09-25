@extends('_app')
@section('title', "Login")
@section('content')
@php
$currentPageName = Route::currentRouteName();
$emailid = @session('email_id');
if ($emailid == '') {
    $loginUrl = route('login');
    echo "<script> window.location = '$loginUrl'; </script>";
    die;
}
@endphp
<style>
    .btnConfirmation {
        background: #a62d34;
        color: #fff;
    }

    .btnConfirmation:hover {
        background-color: #a62d34; 
        color:  #fff; 
    }
</style>

<section class="login-page login-csr">
    <div class="new_logo">
      <img src="{{ asset('pages/images/new_logo.png') }}">
   </div>
    <div class="csr-light"></div>
    <div class="container mobile-img">
    <div class="img-opp"></div>
        <div class="row above-section ">
    <div class="col-xs-12 col-md-6 col-lg-8 d-flex align-items-center p-0">
    <div class="circle-img">
                   <img src="{{ asset('pages/images/ongc-hand.png') }}">
                   </div>
</div>
<div class="col-xs-12 col-md-6 col-lg-4 d-flex align-items-center p-0">
    <div class="login-box justify-content-center">
    <!-- /.login-logo -->
        <div class="card card-outline card-primary login-pswd">
            <div class="form_image"><img src="{{ asset('pages/images/logo.png') }}"></div>
                <div class="card-header text-center">
                    <a href="#" class="h1"><b>{{ config('app.name') }}</b> Login </a>
                </div>
                <div class="card-body">
                <form method="POST" action="{{ route('check.password') }}">
                    @csrf
                    <div class="input-group mb-3">
                        <div class="input-fields">
                        <input type="email" class="form-control @error('emailid') is-invalid @enderror" name="emailid" value="{{ @$emailid }}" readonly placeholder=" Email" >
                        <div class="input-group-append">
                            <div class="input-group-text envelope-bg">
                            <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                        </div>

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="input-group mb-3">
                        <div class="input-fields">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder=" Password" autofocus  autocomplete="off" required >
                        <div class="input-group-append">
                            <div class="input-group-text envelope-bg">
                            <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        </div>

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="row">
                        <!-- /.col -->
                        <div class="col-12 input-group">
                            <button type="submit" class="btn btn-sm btn-block next-btn">{{ __('Login') }}</button>
                            <button type="button" class="btn btn-sm btn-block next-btn" onclick="window.location='{{ route("login") }}';">{{ __('Back') }}</button>
                            <p class="w-100 mt-2 pr-1 text-right forgotPassword" style="font-size: 14px;">
                                <a href="{{ route('forgot_password') }}" class="" style="color: #a62d34cf;">Forgot password</a>
                            </p>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    </div>
 </div>
    </div>
</section>

<!-- Popup Information -->
@php 
	$type = 'Login';
	$getPageContent = popupInfo($type);
	if($getPageContent != null){
		$content = $getPageContent->content;
	}
@endphp
@if(isset($content) && (!empty($content)))
    <div class="modal fade" id="myModalLogin" tabindex="-1" role="dialog" aria-labelledby="exampleModal" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="margin-top: 150px;">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="custom-control custom-checkbox">
                            <table>
                                <tr>
                                    <td style="margin-top: 150px">{!! $content !!}</td>
                                </tr>
                            </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 page-content">
                        <button type="button" class="btn btn-sm btnConfirmation" style="font-size: 20px;" data-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
   </div>
@endif


@if($getNotificationDetails->count() > 0)
    <div class="modal fade" id="myModalBroadcast" tabindex="-1" role="dialog" aria-labelledby="exampleModal" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="margin-top: 150px;">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="custom-control">
                                <ul>
                                    @foreach($getNotificationDetails as $getNotificationDetail)
                                        <li> {!! $getNotificationDetail->content !!}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 page-content">
                        <button type="button" class="btn btn-sm btnConfirmation" data-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection


@section('javascript')
<script>
    window.onload = function() {
        $('#myModalLogin').modal('show');

        $('#myModalBroadcast').modal('show');
    };

    $('.notify').on('click', function() {
        $('#myModalBroadcast').modal('show'); 
    });
    localStorage.setItem("ispct", 0);
</script>
@endsection
