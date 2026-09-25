@extends('_app')
@php
    $pageNm = 'Add Hard Copy';
@endphp
@section('title', $pageNm)
@section('content')
    <style>
        label.col-form-label {
            border-bottom: 1px solid #939090;
            padding-bottom: 10px !important;
            margin-top: 15px !important;
            width: 100% !important;
            padding-left: 0px;
            color: #6896e2;
        }

        span.select2-dropdown.select2-dropdown--above {
            width: 500px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            width: 474px;
        
        }
    </style>
    <div class="content-wrapper">
        <section class="content">	
            <div class="card">
                <div class="card-header" style="background-color: #84363a; color:#fff;">
                    <h3 class="card-title">{{ $pageNm }}</h3>
                    <button type="button" class="btn btn-xs btn-warning float-right ml-2 mr-2" onclick="linkClick(this);" data-link="{{ route('hard_copy_proposal') }}">Back</button>
                </div>
                    <div class="card-body">
                        <form action="{{ route('hard_copy_proposal.store') }}" method="POST" class="frm_hard_copy" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group row mr-0">
                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Proposal Title<i class="text-danger">*</i> </label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Title</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm proposal_title" name="proposal_title" value="" placeholder=" Proposal Title">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Email</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Email</span>
                                        </div>
                                        <input type="email" class="form-control form-control-sm agency_email" name="agency_email" placeholder=" Email">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Proposal Receipt date<i class="text-danger">*</i> </label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Date</span>
                                        </div>
                                        <input type="date" class="form-control form-control-sm project_receipt_date" name="project_receipt_date" value="" placeholder=" Proposal Recipt Date">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Proposal Cost<i class="text-danger">*</i> </label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₹</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm proposal_cost" name="proposal_cost" placeholder="Proposal Cost" >
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Proposal Schedule</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Schedule</span>
                                        </div>
                                        @php 
                                            $scheduleviiList = scheduleViiList();
                                        @endphp
                                        <!-- <input type="text" class="form-control form-control-sm proposal_schedule" name="proposal_schedule" value="" placeholder=" Proposal Schedule"> -->
                                        <select name="proposal_schedule[]" class="form-control select2" data-placeholder="Select" multiple>
                                            <option value="">Select Committee</option>
                                            @foreach($scheduleviiList as $schedulevii)
                                                <option value="{{ $schedulevii->sc_viis_id }}">{{ $schedulevii->sc_viis }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Is Aspirational or not</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select class="form-control is_aspirrational_district" id="is_aspirrational_district" name="is_aspirrational_district">
                                            <option value="no">No</option>
                                            <option value="yes">Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">State Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">State</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm project_location" name="project_location" value="" placeholder="Enter State Name">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">District Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">District</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm district" name="district" value="" placeholder="Enter District Name">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Is MoPNG  or not</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select class="form-control mopng_reference" id="mopng_reference" name="mopng_reference">
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">VIP Type</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Name</span>
                                        </div>
                                        <!-- <input type="text" class="form-control form-control-sm vip_type" name="vip_type" value="" placeholder=" VIP Type"> -->
                                        <select class="form-control vip_type" id="vip_type" name="vip_type">
                                            <option value="">Select</option>    
                                            <option value="MP">MP</option>
                                            <option value="MLA">MLA</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                @php 
                                    $getMpList = getMpList();    
                                @endphp
                                <div class="col-sm-6 pr-3 vip-name" style="display:none;">
                                    <label class="col-sm-12 col-form-label">VIP Type</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select name="mp_id" class="form-control select2" data-placeholder="Select">
                                            <option value="">Select VIP</option>
                                            @foreach($getMpList as $mpList)
                                                <option value="{{ $mpList->id }}">{{ $mpList->vip_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3 person_name" style="display:none";>
                                    <label class="col-sm-12 col-form-label">Referring Person Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Name</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm referring_person_name" name="referring_person_name" value="" placeholder=" Person Name">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Select Committee</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select name="commitee_id[]" class="form-control select2" data-placeholder="Select" multiple>
                                            <option value="">Select Committee</option>
                                            @foreach($getCommiteeDetails as $committee)
                                                <option value="{{ $committee->id }}">{{ $committee->committee_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Agency Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Name</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm implementing_agency" name="implementing_agency" value="" placeholder=" Agency Name">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">FPR Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Name</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm fpr_name" name="fpr_name" value="" placeholder=" FPR Name">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <label class="col-sm-12 col-form-label">Proposal File</label>
                                    <div class="custom-file custom-file-sm">
                                        <input type="file" class="custom-file-input custom-file-input-sm proposal_pdf" name="proposal_pdf" accept=".pdf">
                                        <label class="custom-file-label custom-file-label-sm" for="proposal_pdf">Choose file</label>
                                    </div>
                                </div>

                                <!-- <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Is Approved or Not</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select class="form-control is_approved" id="is_approved" name="is_approved">
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                </div> -->

                                @php 
                                    $proposalStatus = proposalStatus();   
                                @endphp
                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label">Proposal Status</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select class="form-control proposal_status" id="proposal_status" name="proposal_status">
                                            <option value="">Select Status</option>    
                                            @foreach($proposalStatus as $status)
                                                <option value="{{ $status }}">{{ $status }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12 pr-3">
                                    <label class="col-sm-12 col-form-label">Remarks</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Remarks</span>
                                        </div>
                                        <textarea class="form-control form-control-sm remarks" name="remarks" value="" placeholder="Enter Remarks"></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12 input-group input-group-sm mt-2 mt-3">
							        <button type="submit" class="btn btn-sm" style="background-color: #84363a; color:#fff;">Save</button>
						        </div>
                            </div>
                        </form>     
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
    $(document).ready(function () {
        $('.frm_hard_copy').submit(function (e) {
            e.preventDefault();
            getHardCopyList();
        });

        function getHardCopyList() {
            var form = $('.frm_hard_copy')[0];
            var formData = new FormData(form);  

            $.ajax({
                type: 'POST',
                url: $(form).attr('action'),
                data: formData,
                contentType: false,        
                processData: false,         
                success: function(response) {
                    var status = response.status;
                    var message = response.message;

                    // Display the response message on the page
                    show_msgT(status, message);

                    if(status === 1){
                        setTimeout(function(){ 
                            window.location.href = '/hard_copy_proposal/'; 
                        }, 1000);
                    } else {
                        console.log("Error: " + response.message);
                    }
                },
                error: function(error) {
                    console.error(error);
                    var status = error.status;
                    var message = error.responseJSON.message;

                    // Display the error message on the page
                    show_msgT(status, message);
                }
            });
        }

        // Restrict input to only numbers and decimal point
        $('.proposal_cost').on('input', function() {
            var value = $(this).val();

            // Allow only numbers and up to one decimal point
            $(this).val(value.replace(/[^0-9.]/g, ''));

            // If the user enters more than one decimal point, trim the input
            if ((value.match(/\./g) || []).length > 1) {
                $(this).val(value.substring(0, value.length - 1));
            }
        });

        // Prevent non-numeric and non-decimal keys from being pressed
        $('.proposal_cost').on('keypress', function(event) {
            var charCode = (event.which) ? event.which : event.keyCode;
            // Allow only numbers (48-57), period (46), backspace (8), delete (46)
            if ((charCode != 46 || $(this).val().indexOf('.') != -1) && (charCode < 48 || charCode > 57)) {
                event.preventDefault();
            }
        });

        $('.vip_type').on('change', function() {
            var selectedValue = $(this).val();
            if(selectedValue == 'MP')
            {
                $('.vip-name').show();
                $('.person_name').hide();
            }
            else{
                $('.vip-name').hide();
                $('.person_name').show();
            }
        }
    )
    });
</script>
@endsection
