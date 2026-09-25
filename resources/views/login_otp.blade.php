@extends('_app')
@section('title', "Login")
@section('content')
@php
$currentPageName = Route::currentRouteName();
/*
$routeName = @checkValidUser();
if($routeName){
    return redirect()->route($routeName);
}*/

$emailid = $otp_find = '';
$otp_new = @session('generate_otp');
if(@$otp_new > 0){
    $otp_find = $otp_new;
    $emailid = @session('email_id');
}
@endphp
<section class="login-page login-csr">
    <div class="new_logo">
      <img src="{{ asset('pages/images/new_logo.png') }}">
   </div>
    <div class="csr-light"></div>
<div class="container mobile-img">
<div class="img-opp"></div>
    <div class="row above-section ">
        <!-- <div class="col-xs-12 col-md-6 col-lg-4 above_col p-0">
        <a href="List of documetns for CSR.pdf" target="_blank">Read more</a>
        <div class="head-pdf">
           <h1>List of Required documents before submitting the proposal</h1>
           <a href="pages/pdf/ongc_csr.pdf" target="_blank" class="download_pdf">Show List </a>
        </div>
    </div> -->

    <div class="col-xs-12 col-md-6 col-lg-8 d-flex align-items-center p-0">
         <div class="circle-img">
              <img src="{{ asset('pages/images/ongc-hand.png') }}">
          </div>
     </div>
     <div class="col-xs-12 col-md-6 col-lg-4 d-flex align-items-center p-0">
    <div class="login-box justify-content-center">
    <!-- /.login-logo -->
        <div class="card card-outline card-primary login-otp">
            <div class="form_image"><img src="{{ asset('pages/images/logo.png') }}"></div>
            <div class="card-header text-center">
                <a href="#" class="h1"><b>{{ config('app.name') }}</b></a><p class="login-sze mb-0"> Login</p>
                </div>
                <div class="card-body">
                <form method="POST" action="{{ route('login.otp') }}">
                    @csrf
                    <div class="input-group mb-3">
                        <div class="input-fields">
                        <input type="email" class="form-control @error('emailid') is-invalid @enderror" name="emailid" value="{{ old('emailid')?old('emailid'):@$emailid }}" required placeholder=" Email" autofocus  autocomplete="off" >
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
                    <div class="row">
                        <!-- /.col -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-block next-btn">{{ __($otp_find > 0?'Resend OTP':'Get OTP') }}</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
                @if($otp_find > 0)
                <form method="POST" action="{{ route('check.otp') }}" class="mt-5">
                    @csrf
                    <div class="input-group mb-3">  
                        
                        <div class="input-fields">                              <!--  value="{{ $otp_find }}" -->
                        <input type="text" class="form-control int @error('otp') is-invalid @enderror" name="otp" required placeholder=" OTP" autofocus  autocomplete="off">
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
                    <div class="row">
                        <!-- /.col -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-block next-btn">{{ __('Verify OTP & Login') }}</button>
                            <div class="list-doc mt-2">
                                <h1>List of Required documents before submitting the proposal</h1>
                                <a href="pages/pdf/ongc_csr.pdf" target="_blank" class="download_pdf">Show List </a>
                             </div>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
                @endif
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
</div>
</div>
</div>
</section>
@endsection

@section('javascript')
<script>
    localStorage.setItem("ispct", 0);
</script>
@endsection