@extends('_app')
@section('title', 'Login')
@section('content')
<section class="new-signing-modal login-csr">
    <div class="new_logo">
        <img src="{{ asset('pages/images/new_logo.png') }}" alt="ONGC Logo">
    </div>

    <div class="container mobile-img">
        <div class="img-opp"></div>
        <div class="row above-section">
            <div class="col-xs-12 col-md-6 col-lg-8 d-flex align-items-center p-0">
                <div class="circle-img">
                    <img src="{{ asset('pages/images/ongc-hand.png') }}" alt="ONGC Hand">
                </div>
            </div>

            <div class="col-xs-12 col-md-6 col-lg-4 d-flex align-items-center p-0">
                <div class="form_Sec">
                    <div class="row mb-2 login_registerDiv">
                        <div class="col-xs-12 col-sm-12 p-0">
                            <p class="mb-2 text-center" style="font-size: 14px;">Proposals are accepted only through agency that are registered with ONGC</p>
                        </div>
                    </div>

                    <div class="form_image">
                        <img src="{{ asset('pages/images/logo.png') }}" alt="ONGC CSR Logo">
                    </div>

                    <div class="text-center">
                        <a href="#"><b>{{ config('app.name') }}</b></a>
                        <p class="login-sze mb-0 showText">Login To Submit Proposal</p>
                    </div>

                    <form method="POST" action="{{ route('login.submit') }}" id="myForm">
                        @csrf

                        <div class="input-group mb-3">
                            <div class="input-fields">
                                <input type="text" inputmode="text" id="emailid" class="form-control @error('emailid') is-invalid @enderror" name="emailid" value="{{ old('emailid')?old('emailid'):@$emailid }}" required placeholder=" Email/Cpf" autofocus  autocomplete="off" >
                                <div class="input-group-append">
                                    <div class="input-group-text envelope-bg">
                                        <span class="fas fa-envelope"></span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-dark mb-0 email-otp">Please use the email of your agency, OTP for login will be shared on same</p>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="input-group mb-3">
                            <div class="input-fields">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" required placeholder="Password" autofocus autocomplete="off">

                                <div class="input-group-append">
                                    <div class="input-group-text envelope-bg">
                                        <span class="fas fa-lock"></span>
                                    </div>
                                </div>
                            </div>
                            @error('password')
                                <span class="error-message" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        @if(session('message') && session('status') == 2)
                            <span class="text-danger text-bold" style="font-size: 13px;">{{ session('message') }}</span>
                        @endif

                        <button type="submit" class="btn btn-block mb-3 next-btn" id="next_btn">{{ __('Next') }}</button>
                    </form>
                </div>
            </div>
        </div>
        <br clear="all">
    </div>
</section>
@endsection

@section('javascript')
<script>
    (function () {
        var input = document.getElementById('emailid');
        var form = document.getElementById('myForm');
        if (!input || !form) return;

        function validateEmailIdField() {
            var value = input.value.trim();

            if (value === '') {
                input.setCustomValidity('');
                return;
            }
            if (/^[0-9]+$/.test(value)) {
                input.setCustomValidity('');
                return;
            }

            if (value.indexOf('@') === -1) {
                input.setCustomValidity("Please include an '@' in the email address. '" + value + "' is missing an '@'.");
                return;
            }
            input.setCustomValidity('');
        }

        input.addEventListener('input', validateEmailIdField);
        input.addEventListener('blur', validateEmailIdField);

        form.addEventListener('submit', function (e) {
            validateEmailIdField();
            if (!input.checkValidity()) {
                input.reportValidity();
                e.preventDefault();
            }
        });
    })();
</script>
@endsection