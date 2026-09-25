  <!-- Navbar -->
  @php

    $curRouteNm = Route::currentRouteName();
	$legacy_user_id = session('legacy_user_id');
    $userInfo = AuthUser();
    if (!$userInfo) {
        $userInfo = (object) [
            'id' => 0,
            'email' => '',
            'user_name' => 'Guest',
            'username' => 'Guest',
            'designation' => '',
            'user_pan_gst' => '',
            'user_type' => 0,
            'role_type' => '',
            'vendor_verified' => 0,
            'vendor_code' => '',
            'work_station' => 0,
            'user_pan_card_no' => '',
            'work_center_name' => '',
        ];
    }
    $user_id = $userInfo->id ?? 0;
    $user_email = $userInfo->email ?? '';
    $user_name = $userInfo->user_name ?? $userInfo->username ?? 'Guest';
	$designation = $userInfo->designation ?? '';
    $user_pann = $userInfo->user_pan_gst ?? '';
    $usertype = $user_type = $userInfo->user_type ?? 0;
    $user_role = $userInfo->role_type ?? '';
    $vendor_verified = $userInfo->vendor_verified ?? 0;
	$vendorCode = $userInfo->vendor_code ?? '';
    $prposal_count = @$prposalCount; //// comes from controller
	$workCenter = $userInfo->work_station ?? 0;
	$user_pancard = $userInfo->user_pan_card_no ?? '';

	$chief_verified_status = 0;
	$show3 = '';
    if($user_type < 99){
      $show1 = $user_name;
	  if(isset($designation) && (!empty($designation)))
	  {
		$show2 = $designation;
	  }
	  else{
		$show2 = $user_role;
	  }
     
	  $work_center_name = data_get($userInfo, 'work_center_name', '') ?: (data_get($userInfo, 'work_station_name', '') ?: '');
	  $show3 = $work_center_name !== '' ? '<br>'.$work_center_name : '';
    }else{
      $show1 = $user_email;
      $show2 = $user_pancard;

      $agencyDetails = agencyDetails($user_pann, 'PAN');
      $chief_verified_status = @$agencyDetails->chief_verified_status;
    }

	$agencyDetail = getAgencyAllowed($user_pann);
	$agencyLoggedIn = checkAgency($user_email);

	$notificationDetail = notificationCountDetails();
	$notifyCount = $notificationDetail['notificationCount'];
	$notifyData = $notificationDetail['notifications'];
	
  @endphp
  	<nav class="main-header navbar navbar-expand navbar-white navbar-light">
		<ul class="navbar-nav">
			
				<li class="nav-item brand-link" style="margin:auto; padding: 0; width: auto;">
					<img src="{{ asset('pages/images/logo.png') }}" alt="{{ config('app.name') }}" class="brand-image elevation-3" style="opacity: .8">
				</li>

				<li class="nav-item d-none d-sm-inline-block">
					<a href="{{ route('hard_copy_proposal') }}" class="nav-link @php echo in_array($curRouteNm, ['hard_copy_proposal', 'hard_copy_proposal.edit']) ? 'active' : ''; @endphp">
						<p><i class="fa fa-tasks" data-toggle="tooltip" data-placement="bottom" title="Hard Copy Proposal"></i> Hard Copy Proposal</p>
					</a>
				</li> 

		</ul>
		
		<!-- Right navbar links -->
		<ul class="navbar-nav ml-auto">
			
			<li class="nav-item"><p style="margin: 6px 0;font-size: 14px;text-align: center;line-height: 14px;">
				<b>{{ @$show1 }}</b><br>{{ @$show2 }}{!! $show3 !!}</p>
			</li>
			<li class="nav-item dropdown">
				<a class="nav-link" data-toggle="dropdown" href="#">
				<img src="{{ asset('pages/images/user_icon.png') }}" title="{{ $user_name }}" alt="Photo" class="img-size-32 mr-3 img-circle" style="border: 1px solid;">
				</a>
				<div class="dropdown-menu dropdown-menu-sm dropdown-menu-right m-0 p-0">
					<!--
					<div class="dropdown-divider m-0"></div>
					<a href="" class="dropdown-item">
						<i class="fas fa-user mr-2"></i> {{ __('Profile') }}
					</a>
					-->
					<div class="dropdown-divider m-0"></div>
					<a href="{{ route('logout') }}" class="dropdown-item">
						<i class="fas fa-sign-out-alt mr-2"></i>  {{ __('Logout') }}
					</a>
					<div class="dropdown-divider m-0"></div>
					@if($user_type < 99)
						<a href="{{ route('password.index') }}" class="dropdown-item">
							<i class="fas fa-key mr-2"></i>  {{ __('Change Password') }}
						</a>
					@endif
				</div>
			</li>
		</ul>
  	</nav>
