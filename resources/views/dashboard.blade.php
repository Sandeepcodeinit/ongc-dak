@extends('_app')
@php
$pageNm = 'Dashboard';

$user = session('user') ?? [];
$workCenterNames = $workCenterNames ?? [];
$workCenter = $workCenter ?? null;
$getBudgetTotal = $getBudgetTotal ?? 0;
$releasedAmount = $releasedAmount ?? 0;
$agencyCount = $agencyCount ?? 0;
$proposalCompletedCount = $proposalCompletedCount ?? 0;
$verifiedAgencyCount = $verifiedAgencyCount ?? 0;
$proposalPVCount = $proposalPVCount ?? 0;
$newAgencyCount = $newAgencyCount ?? 0;
$inProgressAgencyCount = $inProgressAgencyCount ?? 0;
$parkedAgencyCount = $parkedAgencyCount ?? 0;
$inProgressCount = $inProgressCount ?? 0;
$newProposalCount = $newProposalCount ?? 0;
$approvedProposalCount = $approvedProposalCount ?? 0;
$parkedProposalCount = $parkedProposalCount ?? 0;
$getTotalScData = $getTotalScData ?? 0;
$getTotalStData = $getTotalStData ?? 0;
$getTotalObcData = $getTotalObcData ?? 0;
$getTotalMiniorityData = $getTotalMiniorityData ?? 0;
$getTotalGeneralData = $getTotalGeneralData ?? 0;
$openCount = $openCount ?? 0;
$closedCount = $closedCount ?? 0;
$awaitedCount = $awaitedCount ?? 0;
$totalAmounts = $totalAmounts ?? [];
$startDate = $startDate ?? null;
$endDate = $endDate ?? null;
$month = date('m');
if($month < 4){ $start_date=(date('Y')-1).'-04-01'; }else{ $start_date=date('Y').'-04-01'; } $end_date=date('Y-m-d');
    if(@$startDate !='' ){ $start_date=$startDate; } if(@$endDate !='' ){ $end_date=$endDate; } 
    $userType = data_get($user, 'user_type', 0);
    $workLocation = data_get($user, 'work_station', 0);
    $scheduleviiList = scheduleViiList();
    @endphp
    @section('title', $pageNm)
    @section('content')
    
    <style>
        .icon>i.fa {
            font-size: 110px !important;
        }

        .amount-data {
            text-align: center;
            height: 45px;
            padding-top: 9px;
            background: #b8575d;
            color: #fff;
            font-size: 20px;
        }
    </style>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <!--
      	<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1>{{ $pageNm }}</h1>
				</div>
			</div>
      </div>-->
        </section>

        <!-- Main content -->

        <section class="content mt-0">
            <form action="{{ route('dashboard.admin') }}" method="post">
                @csrf
                <div class="card card-primary">
                    <div class="row mb-2 mt-2">
                        <div class="col-sm-1">
                            <p class="mb-0 text-bold" style="font-size: 18px;">&nbsp;&nbsp;&nbsp; Filters: </p>
                        </div>

                        <div class="col-lg-4">
                            <div class="input-group input-group-sm input-daterange">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-group-text-sm">Start Date</span>
                                </div>
                                <input type="date" class="start-date form-control form-control-sm from_dt"
                                    name="start_date" value="{{ @$start_date }}" id="startDate">
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="input-group input-group-sm input-daterange">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-group-text-sm">End Date</span>
                                </div>
                                <input type="date" class="end-date form-control form-control-sm to_dt" name="end_date"
                                    id="endDate" value="{{ $end_date }}">
                            </div>
                        </div>

                        <div class="col-md-2 col-lg-2">
                            <button type="submit" class="btn btn-sm"  style="background-color: #84363a; color:#fff;">Submit</button>
                        </div>
                    </div>

                    <div class="card-header" style="background-color: #84363a; color:#fff;">
                        <h3 class="card-title">{{ $pageNm }}</h3>
                    </div>

                    @if($workLocation == 1 || $userType != 3)
                        <div class="col-lg-6 col-sm-6 mt-3">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text input-group-text-sm">Work Center</span>
                                </div>
                                <select class="form-control work_center select2" name="work_center" id="work_center"
                                    onchange="this.form.submit()">
                                    <option value="">All Work Center</option>
                                    @php
                                    foreach($workCenterNames as $key => $address){
                                        $selected = (!empty($workCenter) && $address->adto_id == $workCenter) ? 'selected' : '';
                                        echo '<option value="'.$address->adto_id.'" '.$selected.'>'.$address->addressto.'</option>';
                                    }
                                    @endphp
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- @if($getBudgetTotal > 0)
                        @php 
                            $remainingAmount = $getBudgetTotal - $releasedAmount;
                        @endphp
            
                        <div class="input-group input-group-sm mt-3">
                            <span class="input-group-text amount-data text-white col">
                                <b>Amount Allocated to Workcenter : </b>&nbsp;{{ number_format($getBudgetTotal) }}
                            </span>
                            <span class="input-group-text amount-data text-white col">
                                <b>Amount Disbursed : </b>&nbsp;{{ number_format($releasedAmount) }}
                            </span>
                            <span class="input-group-text amount-data text-white col">
                                <b> Remaining Allocated Amount : </b>&nbsp;{{ number_format($remainingAmount) }}
                            </span>
                        </div>
                    @endif -->

                    <div class="card-body pb-0">
						<div class="row">
                            <div class="col-lg-6 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <b><h5>Total Agencies on Portal</b> : <font class="float-right">{{ $agencyCount }}</font></h5>
                                        <!-- <h5>Agencies Verified : <font class="float-right">{{ $verifiedAgencyCount }}</font></h5>
                                        <h5>Agencies under verification at FPR level : <font class="float-right">{{ $inProgressAgencyCount }}</font></h5>
                                        <h5>Agencies Yet to be Assigned : <font class="float-right">{{ $newAgencyCount }}</font></h5>
                                        <h5>Agencies not Verified and Parked : <font class="float-right">{{ $parkedAgencyCount }}</font></h5>
                                        <h5>Invalid Agencies : <font class="float-right">0</font></h5> -->
                                    </div>

                                    <!-- <div class="icon">
                                        <i class="fa fa-list-ul"></i>
                                    </div> -->
                                    <!-- <a href="{{ route('proposal.all')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
                                </div>
                            </div>

                            <div class="col-lg-6 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                       <b><h5>Total Proposals Submitted on Portal</b> : <font class="float-right">{{ $proposalCompletedCount }}</font></h5>
                                        <!-- <h5>Proposals under Examination at FPR level : <font class="float-right">{{ $inProgressCount }}</font></h5>
                                        <h5>Proposals Initiated in DISHA : <font class="float-right">{{ $proposalPVCount }}</font></h5>
										<h5>Proposals Approved in DISHA : <font class="float-right">{{ $approvedProposalCount }}</font></h5>
                                        <h5>Proposals Yet to be Assigned : <font class="float-right">{{ $newProposalCount }}</font></h5>
                                        <h5>Proposals not under Consideration</Under-Consideration> : <font class="float-right">{{ $parkedProposalCount }}</font></h5> -->
                                    </div>
                                    <!-- <div class="icon">
                                        <i class="fa fa-newspaper"></i>
                                    </div> -->
                                    <!-- <a href="{{ route('proposal.all')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
                                </div>
                            </div>

                            <div class="col-lg-6 ">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <!-- <u><h5>Total Agencies on Portal : <font class="float-right">{{ $agencyCount }}</font></h5></u> -->
                                        <h5>Agencies Verified : <font class="float-right">{{ $verifiedAgencyCount }}</font></h5>
                                        <h5>Agencies under verification at FPR level : <font class="float-right">{{ $inProgressAgencyCount }}</font></h5>
                                        <h5>Agencies Yet to be Assigned : <font class="float-right">{{ $newAgencyCount }}</font></h5>
                                        <h5>Agencies not Verified and Parked : <font class="float-right">{{ $parkedAgencyCount }}</font></h5>
                                        <!-- <h5>Agencies Invalid : <font class="float-right">0</font></h5> -->
                                    </div>

                                    <div class="icon">
                                        <i class="fa fa-list-ul"></i>
                                    </div>
                                    <!-- <a href="{{ route('proposal.all')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
                                </div>
                            </div>

                            <div class="col-lg-6 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <!-- <u><h5>Total Proposals Submitted on Portal : <font class="float-right">{{ $proposalCompletedCount }}</font></h5></u> -->
                                        <h5>Proposals under Examination at FPR level : <font class="float-right">{{ $inProgressCount }}</font></h5>
                                        <h5>Proposals Initiated in DISHA, awaiting approval : <font class="float-right">{{ $proposalPVCount }}</font></h5>
										<h5>Proposals Approved in DISHA : <font class="float-right">{{ $approvedProposalCount }}</font></h5>
                                        <h5>Proposals yet to be Assigned : <font class="float-right">{{ $newProposalCount }}</font></h5>
                                        <h5>Proposals not under Consideration</Under-Consideration> : <font class="float-right">{{ $parkedProposalCount }}</font></h5>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-newspaper"></i>
                                    </div>
                                    <!-- <a href="{{ route('proposal.all')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
                                </div>
                            </div>

                            <!-- <div class="col-lg-4 col-4">
                                <div class="small-box bg-success">
                                    <div class="inner">
										<h4>Beneficiaries Details : </h4>
                                        <h5>SC : <font class="float-right">{{ number_format($getTotalScData )}}</font></h5>
                                        <h5>ST : <font class="float-right">{{ number_format($getTotalStData) }}</font></h5>
                                        <h5>OBC : <font class="float-right">{{ number_format($getTotalObcData) }}</font></h5>
                                        <h5>Minorities : <font class="float-right">{{ number_format($getTotalMiniorityData) }}</font></h5>
                                        <h5>General : <font class="float-right">{{ number_format($getTotalGeneralData) }}</font></h5>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-info-circle"></i>
                                    </div>
                                     <a href="{{ route('proposal.all')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
                                <!-- </div>
                            </div>  -->

                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-6 col-sm-6 mb-2">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text input-group-text-sm">Please select Graph Type</span>
                            </div>
                            <select class="form-control graph_type" name="graph_type" id="graph_type"
                                onchange="selectGraph();">
                                <option value="pie" {{ request('graph_type') == 'pie' ? 'selected' : '' }}>Pie
                                    Chart</option>
                                <option value="bar" {{ request('graph_type', 'bar') == 'bar' ? 'selected' : '' }}>Bar Chart
                                </option>
                                
                                <!-- <option value="line" {{ request('graph_type') == 'line' ? 'selected' : '' }}>Line Chart
                                </option> -->
                            </select>
                        </div>
                    </div>
                    <section class="col-lg-12 connectedSortable" id="pieGraph">
                        <div class="card card-primary">
                            <div class="card-header" style="background-color: #84363a; color:#fff;">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-pie mr-1"></i>
                                    Pie Chart for number of proposals and value of proposals
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-5">
                                        <select class="form-control statePin" name="state_pin" id="statePin">
                                            <option value="state">State/UT of project implemention</option>
                                            <!-- <option value="city">District</option> -->
                                            <!-- <option value="status">Status</option> -->
                                            <option value="schedule">Schedule VII/Theme</option>
                                            <option value="address">Work Center</option>

                                            {{--<option value="gst_type">GST Type</option>
											<option value="target_audience">Focused/Target</option>
											<option value="community_beneficery">Community of beneficery</option>--}}
                                        </select>
                                    </div>

                                    <div class="col-lg-5">
                                        <select class="form-control" name="proposal_data" id="proposalData">
                                            <option value="proposal_count">Proposal count</option>
                                            <option value="proposal_amount" class="proposalAmt">Approved amount</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-2">
                                        <button type="button" class="btn" 
                                            onclick="submitForm()" style="background-color: #84363a; color:#fff;">Submit</button>
                                    </div>
                                </div>
                                <div id="pieContainer" style="min-width: 500px; height: 500px; max-width: 800px; margin: 0 auto;"></div>
                                <div class="col-lg-12 schedule_list" style="display:none;">
                                    <!-- <div class="input-group input-group-sm">
                                        <h4 class="input-group-prepend">Schedule VII</h4>
                                        @foreach($scheduleviiList as $schedulevii)
                                            <p> {{ $schedulevii->sc_viis }}</p>
                                        @endforeach
                                    </div> -->
                                </div>
                            </div>
                    </section>

                    <section class="col-lg-12 connectedSortable">
                        <div class="card card-primary" id="lineGraph" style="display:none">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-line mr-1"></i>
                                    Line Chart for number of proposals and value of proposals
                                </h3>
                            </div>
                            <input type="hidden" id="totalCost" value="{{ json_encode($totalAmounts) }}">

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-5">
                                        <select class="form-control statePin" name="state_pin" id="stateLinePin">
                                            <option value="state">State/UT of project implemention</option>
                                            <!-- <option value="city">District</option> -->
                                            <!-- <option value="status">Status</option> -->
                                            <option value="schedule">Schedule VII/Theme</option>
                                            <option value="address">Work Center</option>
                                            {{--<option value="gst_type">GST Type</option>
											<option value="target_audience">Focused/Target</option>
											<option value="community_beneficery">Community of beneficery</option>--}}
                                        </select>
                                    </div>

                                    <div class="col-lg-5">
                                        <select class="form-control" name="proposal_data" id="proposalLineData">
                                            <option value="proposal_count">Proposal count</option>
                                            <option value="proposal_amount" class="proposalAmt">Approved amount</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-2">
                                        <button type="button" class="btn"
                                            onclick="submitLineForm();" style="background-color: #84363a; color:#fff;">Submit</button>
                                    </div>
                                </div>
                                <div id="lineContainer" style="width: 100%; height: 400px;"></div>
                                <!-- <div class="col-lg-12 schedule_list" style="display:none;">
                                    <div class="input-group input-group-sm">
                                        <h4 class="input-group-prepend">Schedule VII</h4>
                                        @foreach($scheduleviiList as $schedulevii)
                                            <p> {{ $schedulevii->sc_viis }}</p>
                                        @endforeach
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </section>

                    <!-- <section class="col-lg-6 connectedSortable">
						<div class="card card-primary">
							<div class="card-header">
								<h3 class="card-title">
									<i class="fas fa-globe mr-1"></i>
									Maps
								</h3>
							</div>
							<div class="card-body">
								<div id="mapdiv"></div>
							</div>
						</div>
					</section>  -->

                    <section class="col-lg-12 connectedSortable" id="barGraph" style="display:none">
                        <div class="card card-primary">
                            <div class="card-header" style="background-color: #84363a; color:#fff;">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-bar mr-1"></i>
                                    Bar Chart for number of proposals and value of proposals
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-5">
                                        <select class="form-control statePin" name="state_pin" id="stateBarPin">
                                            <option value="state">State/UT of project implemention</option>
                                            <!-- <option value="city">District</option> -->
                                            <!-- <option value="status">Status</option> -->
                                            <option value="schedule">Schedule VII/Theme</option>
                                            <option value="address">Work Center</option>
                                            {{--<option value="gst_type">GST Type</option>
											<option value="target_audience">Focused/Target</option>
											<option value="community_beneficery">Community of beneficery</option>--}}
                                        </select>
                                    </div>

                                    <div class="col-lg-5">
                                        <select class="form-control" name="proposal_data" id="proposalBarData">
                                            <option value="proposal_count">Proposal count</option>
                                            <option value="proposal_amount" class="proposalAmt">Approved amount</option>
                                        </select>
                                    </div>

                                    <div class="col-lg-2">
                                        <button type="button" class="btn"
                                            onclick="submitBarForm()"style="background-color: #84363a; color:#fff;">Submit</button>
                                    </div>
                                </div>
                                <div id="barContainer" style="width: 100%; height: 400px;"></div>
                                <!-- <div class="col-lg-12 schedule_list" style="display:none;">
                                    <div class="input-group input-group-sm">
                                        <h4 class="input-group-prepend">Schedule VII</h4>
                                        @foreach($scheduleviiList as $schedulevii)
                                            <p> {{ $schedulevii->sc_viis }}</p>
                                        @endforeach
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </section>

                    <section class="col-lg-12">
                        <div class="card  card-primary" id="tableData" style="display: none">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-table mr-1"></i>
                                    Proposal Details
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div id="tableResultContainer"></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
    </div>
    </form>
    </section>
    </div>
    @endsection

    @section('css')
    <style>
    #mapdiv {
        width: 100%;
        height: 400px;
    }

    .connectedSortable {
        min-height: 0 !important;
    }

    .connectedSortable.active {
        min-height: 100px;
    }
    </style>
    @endsection

    @section('javascript')
    <script src="https://code.highcharts.com/5.0.0/highcharts.js"></script>
    <script src="{{ asset('pages/map/map.js') }}"></script>
    <script src="{{ asset('pages/map/indiaLow.js') }}"></script>
    <script src="{{ asset('pages/map/index.js') }}"></script>
    <script src="{{ asset('pages/map/Animated.js') }}"></script>
    <script>
    // For date filter
    $(".start-date").on("change", function() {
        // Set minimum date for endDate based on the selected startDate
        $(".end-date").attr("min", $(this).val());

        // Update the startDate variable
        var startDate = new Date($('.start-date').val());

        // Update the endDate variable
        var endDate = new Date($('.end-date').val());

        // Check if endDate is less than startDate
        if (startDate > endDate) {
            alert('End date should be greater than start date');
        }
    });

    // For Proposal-amount
    //$(".proposalAmt").css("display", "none");
    $(".statePin, .graph_type").on("change", function() {
        var selectedValue = $(this).val();
        // if(selectedValue === 'city')
        // {
        // 	$(".proposalAmt").css("display", "none");
        // }
        // else{
        // 	$(".proposalAmt").css("display", "block");
        // }
        // if (selectedValue === 'state' || selectedValue === 'schedule' || selectedValue === 'address' ||
        //     selectedValue === 'gst_type' || selectedValue === 'target_audience' || selectedValue ===
        //     'community_beneficery') {
        //     $(".proposalAmt").css("display", "block");
        // } else {
        //     $(".proposalAmt").css("display", "none");
        // }

        // Js to display schedule only when filter 'schedule' is selected
        if(selectedValue === 'schedule')
        {
            $(".schedule_list").css("display", "block");
        }
        else{
            $(".schedule_list").css("display", "none");
        }
    });


    function selectGraph() {
        var graphValue = $('.graph_type').val();
        $("#pieGraph, #lineGraph, #barGraph").css("display", "none").removeClass('active');

        if (graphValue === 'pie') {
            submitForm(); // This should render the pie chart
            $("#pieGraph").css("display", "block").addClass('active');
        } else if (graphValue === 'line') {
            submitLineForm();
            $("#lineGraph").css("display", "block").addClass('active');
        } else if (graphValue === 'bar') {
            submitBarForm();
            $("#barGraph").css("display", "block").addClass('active');
        }
    }

    // ✅ Load pie chart by default on page load
    $(document).ready(function () {
        $('.graph_type').val('pie'); 
        selectGraph(); 
    });

    // Pie chart js start
    function submitForm() {
        // Get CSRF token value
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Get form data
        var statePinValue = document.getElementById('statePin').value;
        var proposalDataValue = document.getElementById('proposalData').value;
        var startDate = $(".start-date").val();
        var endDate = $(".end-date").val();
        var filterParam = statePinValue;

        
        // Utility function to convert amount to Cr / Lakh format
        function formatAmountIndian(value) {
            if (value >= 10000000) {
                return (value / 10000000).toFixed(2) + ' Cr';
            } else if (value >= 100000) {
                return (value / 100000).toFixed(2) + ' L';
            } else {
                return value.toLocaleString('en-IN');
            }
        }

        var proposalDataValue = document.getElementById('proposalData').value;

        $.ajax({
            type: 'POST',
            url: "{{ route('dashboard.pieGraph') }}",
            data: {
                graph_type: 'pie',
                state_pin: statePinValue,
                proposal_data: proposalDataValue,
                start_date: startDate,
                end_date: endDate,
                _token: csrfToken
            },
            beforeSend: function () {
                show_msg(3, '', 'Please wait...', 4);
            },
            success: function (response) {
                var result = response.result;

                var formattedResult = result.map(function (item) {
                    return {
                        name: item.name,
                        y: parseFloat(item.y),
                        id: item.id
                    };
                });

                Highcharts.chart('pieContainer', {
                    chart: {
                        type: 'pie',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: ''
                    },
                    credits: {
                        enabled: false
                    },
                    tooltip: {
                        formatter: function () {
                            if (proposalDataValue === 'proposal_amount') {
                                const total = this.series.data.reduce((sum, point) => sum + point.y, 0);
                                const percentage = ((this.y / total) * 100).toFixed(2);
                                const formattedAmt = formatAmountIndian(this.y);
                                return `<b>${this.point.name}</b><br>Amount: ${formattedAmt}<br>(${percentage}%)`;
                            } else {
                                return `<b>${this.point.name}</b>: ${this.y.toLocaleString('en-IN')}`;
                            }
                        }
                    },
                    plotOptions: {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: {
                                enabled: true,
                                formatter: function () {
                                    // ✅ Only show name on labels
                                    return this.point.name;
                                }
                            }
                        },
                        series: {
                            point: {
                                events: {
                                    click: function () {
                                        var clickedPointData = {
                                            id: this.id,
                                            filterParam: filterParam
                                        };
                                        createTable(clickedPointData);
                                    }
                                }
                            }
                        }
                    },
                    series: [{
                        name: response.labelName,
                        data: formattedResult
                    }]
                });

                Swal.close();
            },
            error: function (error) {
                console.error(error);
            }
        });
    }
    // Pie chart js end

    // Line chart js start
    function submitLineForm() {
        // Get CSRF token value
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Get form data
        var statePinValue = document.getElementById('stateLinePin').value;
        var proposalDataValue = document.getElementById('proposalLineData').value;
        var startDate = $(".start-date").val();
        var endDate = $(".end-date").val();
        var filterParam = statePinValue;

        $.ajax({
            type: 'POST',
            //url: '/dashboard-line-graph',
            url: "{{ route('dashboard.lineGraph') }}",
            data: {
                graph_type: 'line',
                state_pin: statePinValue,
                proposal_data: proposalDataValue,
                start_date: startDate,
                end_date: endDate,
                _token: csrfToken
            },
            beforeSend: function() {
                show_msg(3, '', 'Please wait...', 4);
            },
            success: function(response) {
                console.log();
                var status = response.status;
                var result = response.result;

                new Highcharts.Chart({
                    chart: {
                        renderTo: 'lineContainer',
                        type: 'line',
                        marginBottom: 120,
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        categories: result.map(item => item.data),
                        labels: {
                            rotation: -45,
                            style: {
                                fontSize: '12px'
                            }
                        },
                    },
                    yAxis: {
                        title: {
                            text: response.yAxis
                        },
                        labels: {
                            formatter: function() {
                                return Highcharts.numberFormat(this.value, 0, '.', ','); // Format Y-axis numbers
                            }
                        }
                    },
                    credits: {
                        enabled: false
                    },
                    tooltip: {
                        pointFormatter: function() {
                            //return '<b>' + Highcharts.numberFormat(this.y, 0, '.', ',') + '</b>'; // Format tooltip numbers
                            if(proposalDataValue == 'proposal_amount'){
                                return '<b>' + this.y.toLocaleString() + '</b><br>('+price_in_words(this.y.toFixed(2))+')';
                            }else{
                                return '<b>' + Highcharts.numberFormat(this.y, 0, '.', ',') + '</b>';
                            }
                        }
                    },
                    plotOptions: {
                        series: {
                            point: {
                                events: {
                                    click: function() {
                                        // Access data for the clicked point from the original result array
                                        var clickedPointIndex = this.index;
                                        var clickedPointData = {
                                            id: result[clickedPointIndex].id,
                                            filterParam: filterParam
                                        };
                                        createTable(clickedPointData);
                                    }
                                }
                            }
                        }
                    },
                    legend: false,
                    series: [{
                        name: "Total Count",
                        data: result.map(item => ({
                            y: item.y,
                            name: item.name
                        }))
                    }]
                });
                Swal.close();
            },
            error: function(error) {
                console.error(error);
                var status = error.status;
            }
        });
    }

    //submitLineForm();
    // Line chart js end

    // Bar chart js start
    function submitBarForm() {
        // Get CSRF token value
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Get form data
        var statePinValue = document.getElementById('stateBarPin').value;
        var proposalDataValue = document.getElementById('proposalBarData').value;
        var startDate = $(".start-date").val();
        var endDate = $(".end-date").val();
        var filterParam = statePinValue;

        $.ajax({
            type: 'POST',
            //url: '/dashboard-bar-graph',
            url: "{{ route('dashboard.barGraph') }}",
            data: {
                state_pin: statePinValue,
                proposal_data: proposalDataValue,
                start_date: startDate,
                end_date: endDate,
                _token: csrfToken
            },
            beforeSend: function() {
                show_msg(3, '', 'Please wait...', 4);
            },
            success: function(response) {
                console.log(response);
                var status = response.status;
                var result = response.result;

                Highcharts.chart('barContainer', {
                    chart: {
                        type: 'column',
                        backgroundColor: 'transparent'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        categories: result.map(item => item.data),
                    },
                    yAxis: {
                        title: {
                            text: response.yAxis
                        },
                        labels: {
                            formatter: function() {
                                return Highcharts.numberFormat(this.value, 0, '.',
                                ','); // Formatting Y-axis numbers
                            }
                        }
                    },
                    credits: {
                        enabled: false
                    },
                    tooltip: {
                        pointFormatter: function() {
                            //return '<b>' + Highcharts.numberFormat(this.y, 0, '.', ',') +'</b>'; // Formatting tooltip numbers
                            if(proposalDataValue == 'proposal_amount'){
                                //return '<b>' + this.y.toLocaleString() + '</b><br>('+price_in_words(this.y.toFixed(2))+')';
                                return '<b>' + this.y.toLocaleString() + ' Cr</b> : ('+this.per+')';
                            }else{
                                return '<b>' + Highcharts.numberFormat(this.y, 0, '.', ',') + '</b>';
                            }
                        }
                    },
                    plotOptions: {
                        series: {
                            point: {
                                events: {
                                    click: function() {
                                        // Access data for the clicked point from the original result array
                                        var clickedPointIndex = this.index;
                                        var clickedPointData = {
                                            id: result[clickedPointIndex].id,
                                            filterParam: filterParam
                                        };
                                        createTable(clickedPointData);
                                    }
                                }
                            }
                        }
                    },
                    series: [{
                        name: response.labelName,
                        data: result.map(item => ({
                            y: item.y,
                            name: item.name,
                            per: item.per || ''
                        }))
                    }]
                });
                Swal.close();
            },
            error: function(error) {
                console.error(error);
                var status = error.status;
            }
        });
    }


    //submitBarForm();
    // Bar chart js end

    // country map js start
    // var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // // Get form data
    // var startDate = $(".start-date").val();
    // var endDate = $(".end-date").val();

    // $.ajax({
    // 	type: 'POST',
    // 	url: '/dashboard-map-chart', 
    // 	data: {start_date:startDate,end_date:endDate , _token:csrfToken},

    // 	success: function(response) {
    // 		var status = response.status;
    // 		var result = response.result;

    // 		var root = am5.Root.new("mapdiv");

    // 		// Set themes
    // 		root.setThemes([
    // 			am5themes_Animated.new(root)
    // 		]);

    // 		// Create the map chart
    // 		var chart = root.container.children.push(am5map.MapChart.new(root, {
    // 			panX: "translateX",
    // 			panY: "translateY",
    // 			projection: am5map.geoMercator(),
    // 			layout: root.horizontalLayout
    // 		}));

    // 		// Create main polygon series for India
    // 		var polygonSeries = chart.series.push(am5map.MapPolygonSeries.new(root, {
    // 			geoJSON: am5geodata_indiaLow,
    // 				calculateAggregates: true,
    // 				valueField: "value"
    // 		}));

    // 		// Add tooltips to state polygons
    // 		polygonSeries.mapPolygons.template.setAll({
    // 			tooltipHTML: "<b>State: {name}</b><br>Proposal Count: {total_proposal}",
    // 			interactive: true,
    // 			toggleKey: "active",
    // 		});

    // 		polygonSeries.mapPolygons.template.states.create("hover", {
    // 			fill: root.interfaceColors.get("primaryButtonHover")
    // 		});

    // 		polygonSeries.mapPolygons.template.states.create("active", {
    // 			fill: root.interfaceColors.get("primaryButtonHover")
    // 		});
    // 		polygonSeries.set("heatRules", [{
    // 			target: polygonSeries.mapPolygons.template,
    // 			dataField: "value",
    // 			min: am5.color(0x8ab7ff),
    // 			max: am5.color(0x25529a),
    // 			key: "fill"
    // 		}]);

    // 		var states = result;

    // 		var previousPolygon;

    // 		polygonSeries.mapPolygons.template.on("active", function (active, target) {
    // 			if (previousPolygon && previousPolygon != target) {
    // 				previousPolygon.set("active", false);
    // 			}
    // 			previousPolygon = target;

    // 			if (target.get("active")) {
    // 				var clickedPointData = {
    // 					id: target.dataItem.dataContext.state_code,
    // 					filterParam: 'state'
    // 				};
    // 				createTable(clickedPointData);
    // 			}
    // 		});

    // 		// Set states data
    // 		polygonSeries.data.setAll(states);

    // 		chart.appear(1000, 100);						
    // 	},
    // 	error: function(error) {
    // 		console.error(error);
    // 		var status = error.status;
    // 	}
    // });
    // country map js end 
    /*
    function submitDateForm() {
        submitForm();
        submitLineForm();
        submitBarForm();
    }*/

    $(document).ready(function() {
        $('#statePin').change(function() {
            // Hide the table and clear previous listing
            $('#tableData').css("display", "none");
            $('#tableResultContainer').html('');
        });
    });

    // Function to create the table after graph click
    function createTable(clickedPointData) {
        $('#tableData').css("display", "block");
		var proposalDataValue = document.getElementById('proposalData').value;
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var id = clickedPointData.id;
        var columnName = clickedPointData.filterParam;

        $.ajax({
            type: 'POST',
            url: "{{ route('dashboard.tableDetail') }}",
            data: {
                id: id,
                param: columnName,
                _token: csrfToken,
				proposalDataValue : proposalDataValue
            },
            success: function(response) {
                if (response.status === 1 && response.result) {
                    $('#tableResultContainer').html(response.result);
                } else {
                    console.error('Unexpected response:', response);
                }
            },
            error: function(error) {
                console.error(error);
            }
        });
    }
    </script>
    @endsection