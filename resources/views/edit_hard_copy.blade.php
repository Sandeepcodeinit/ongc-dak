@extends('_app')
@php
    $pageNm = 'Update non-portal';
@endphp
@section('title', $pageNm)
@section('content')
<style>
    .custom-file,
    #view_pdf_button {
        display: inline-flex;
        align-items: center;
    }

    .custom-file-label {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #view_pdf_button {
        margin-top: 90px;
    }

    label.col-form-label {
        border-bottom: 1px solid #939090;
        padding-bottom: 10px !important;
        margin-top: 15px !important;
        width: 100% !important;
        padding-left: 0px;
        color: #6896e2;
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
                        <form action="{{ route('hard_copy_proposal.update', $getHardCopyDetails->id) }}" method="POST" class="frm_hard_copy_update" enctype="multipart/form-data">
                            @method('PUT')
                            @csrf
                            <div class="form-group row mr-0">
                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Proposal Title<i class="text-danger">*</i> </label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Title</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm proposal_title" name="proposal_title" value="{{ old('proposal_title') ? old('proposal_title') : $getHardCopyDetails->proposal_title}}" placeholder=" Proposal Title">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Email</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Email</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm agency_email" name="agency_email" value="{{ old('agency_email') ? old('agency_email') : $getHardCopyDetails->agency_email}}" placeholder=" Email">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Proposal Receipt date<i class="text-danger">*</i> </label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Date</span>
                                        </div>
                                        <input type="date" class="form-control form-control-sm project_receipt_date" name="project_receipt_date" value="{{ old('project_receipt_date') ? old('project_receipt_date') : $getHardCopyDetails->project_receipt_date}}" placeholder=" Proposal Recipt Date">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Proposal Cost<i class="text-danger">*</i> </label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₹</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm proposal_cost" name="proposal_cost" value="{{ old('proposal_cost') ? old('proposal_cost') : $getHardCopyDetails->proposal_cost}}" placeholder=" Proposal Cost">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Proposal Schedule</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Schedule</span>
                                        </div>
                                        
                                        @php 
                                            $scheduleviiList = scheduleViiList();
                                        @endphp
                                        <select name="proposal_schedule[]" class="form-control select2" multiple>
                                            <option value="">Select Schedule</option>
                                            @foreach($scheduleviiList as $schedulevii)
                                                <option value="{{ $schedulevii->sc_viis_id }}" 
                                                {{ isset($getHardCopyDetails->proposal_schedule) && in_array($schedulevii->sc_viis_id, explode(',', $getHardCopyDetails->proposal_schedule)) ? 'selected' : '' }}>
                                                    {{ $schedulevii->sc_viis }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Is Aspirational or not</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                        
                                        <select class="form-control is_aspirrational_district" id="is_aspirrational_district" name="is_aspirrational_district">
                                            <option value="no" {{ old('is_aspirrational_district', $getHardCopyDetails->is_aspirrational_district ?? '') == 'no' ? 'selected' : '' }}>No</option>
                                            <option value="yes" {{ old('is_aspirrational_district', $getHardCopyDetails->is_aspirrational_district ?? '') == 'yes' ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>State Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">State</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm project_location" name="project_location" value="{{ old('project_location') ? old('project_location') : $getHardCopyDetails->project_location}}" placeholder=" Enter State Name">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>District Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">District</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm district" name="district" value="{{ old('district') ? old('district') : $getHardCopyDetails->district}}" placeholder=" Enter District Name">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Is MoPNG or not</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select class="form-control mopng_reference" id="mopng_reference" name="mopng_reference">
                                            <option value="no" {{ old('mopng_reference', $getHardCopyDetails->mopng_reference ?? '') == 'no' ? 'selected' : '' }}>No</option>
                                            <option value="yes" {{ old('mopng_reference', $getHardCopyDetails->mopng_reference ?? '') == 'yes' ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>VIP Type</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Name</span>
                                        </div>
                                      
                                        <select class="form-control vip_type" id="vip_type" name="vip_type">
                                            <option value="">Select</option>    
                                            <option value="MP" {{ (old('vip_type') == 'MP' || $getHardCopyDetails->vip_type == 'MP') ? 'selected' : '' }}>MP</option>
                                            <option value="MLA" {{ (old('vip_type') == 'MLA' || $getHardCopyDetails->vip_type == 'MLA') ? 'selected' : '' }}>MLA</option>
                                            <option value="Other" {{ (old('vip_type') == 'Other' || $getHardCopyDetails->vip_type == 'Other') ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                </div>

                                @php 
                                    $getMpList = getMpList();    
                                @endphp
                                
                                <div class="col-sm-6 pr-3 vip-name" style="display:none;">
                                    <label class="col-sm-12 col-form-label"><br>VIP Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select name="mp_id" class="form-control select2" data-placeholder="Select">
                                            <option value="">Select VIP</option>
                                            @foreach($getMpList as $mpList)
                                                <option value="{{ $mpList->id }}" 
                                                    {{ in_array($mpList->id, explode(',', $getHardCopyDetails->mp_id)) ? 'selected' : '' }}>
                                                    {{ $mpList->vip_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>


                                <div class="col-sm-6 pr-3 person_name" style="display:none";>
                                    <label class="col-sm-12 col-form-label"><br>Referring Person Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Name</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm referring_person_name" name="referring_person_name" value="{{ old('referring_person_name') ? old('referring_person_name') : $getHardCopyDetails->referring_person_name}}" placeholder=" Person Name">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Select Committee</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select name="commitee_id[]" class="form-control select2" multiple>
                                            <option value="">Select Committee</option>
                                            @foreach($getCommiteeDetails as $committee)
                                                <option value="{{ $committee->id }}" 
                                                        {{ in_array($committee->id, explode(',', $getHardCopyDetails->commitee_id)) ? 'selected' : '' }}>
                                                    {{ $committee->committee_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Agency Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Name</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm implementing_agency" name="implementing_agency"value="{{ old('implementing_agency') ? old('implementing_agency') : $getHardCopyDetails->implementing_agency}}"placeholder=" Agency Name">
                                    </div>
                                </div>

                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>FPR Name</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Name</span>
                                        </div>
                                        <input type="text" class="form-control form-control-sm fpr_name" name="fpr_name" value="{{ old('fpr_name') ? old('fpr_name') : $getHardCopyDetails->fpr_name}}" placeholder=" FPR Name">
                                    </div>
                                </div>

                                <!-- <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Is Considered or Not</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select class="form-control is_approved" id="is_approved" name="is_approved">
                                            <option value="no" {{ old('is_approved', $getHardCopyDetails->is_approved ?? '') == 'no' ? 'selected' : '' }}>No</option>
                                            <option value="yes" {{ old('is_approved', $getHardCopyDetails->is_approved ?? '') == 'yes' ? 'selected' : '' }}>Yes</option>
                                        </select>
                                    </div>
                                </div> -->

                                @php 
                                    $proposalStatus = proposalStatus();   
                                @endphp
                                <div class="col-sm-6 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Proposal Status</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Select</span>
                                        </div>
                                        <select class="form-control proposal_status" id="proposal_status" name="proposal_status">
                                            <option value="">Select Status</option>
                                            @foreach($proposalStatus as $status)
                                                <option value="{{ $status }}" {{ $getHardCopyDetails->proposal_status == $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-5">
                                    <label class="col-sm-12 col-form-label d-flex align-items-center"><br>Proposal File</label>
                                    <div class="custom-file custom-file-sm mr-2">
                                        <input type="file" class="custom-file-input custom-file-input-sm" name="proposal_pdf" accept=".pdf" id="proposal_pdf">
                                        <label class="custom-file-label custom-file-label-sm" for="proposal_pdf" id="proposal_pdf_label">
                                        {{ $getHardCopyDetails->proposal_pdf ? basename($getHardCopyDetails->proposal_pdf) : 'Choose file' }}
                                        </label>
                                    </div>
                                </div>
                                
                                @if(isset($getHardCopyDetails->proposal_pdf) && !empty($getHardCopyDetails->proposal_pdf))
                                    <div id="view_pdf_button">
                                        <a href="{{ Storage::url('app/public/' . $getHardCopyDetails->proposal_pdf) }}" target="_blank" class="btn btn-primary btn-sm">View PDF</a>
                                    </div>
                                @endif

                                


                                <div class="col-sm-12 pr-3">
                                    <label class="col-sm-12 col-form-label"><br>Remarks</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Remarks</span>
                                        </div>
                                        <textarea class="form-control form-control-sm remarks" name="remarks" value="" placeholder="Enter Remarks">{{ old('remarks') ? old('remarks') : @$getHardCopyDetails->remarks }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-12 input-group input-group-sm mt-2">
							        <button type="submit" class="btn btn-sm" style="background-color: #84363a; color:#fff;">Update</button>
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
        $('.frm_hard_copy_update').submit(function (e) {
            e.preventDefault();
            submitProposalForm();
        });

        function submitProposalForm() {
            var form = $('.frm_hard_copy_update')[0]; 
            var formData = new FormData(form); 

            $.ajax({
                type: 'POST',
                url: form.action,
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response);
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
                    console.log(error.responseJSON.message);
                }
            });
        }

        // Custom input for proposal cost to allow only numbers and one decimal
        $('.proposal_cost').on('input', function() {
            var value = $(this).val();
            $(this).val(value.replace(/[^0-9.]/g, ''));
            if ((value.match(/\./g) || []).length > 1) {
                $(this).val(value.substring(0, value.length - 1));
            }
        });

        var getVal = $('.vip_type').val();
        if(getVal === 'MP')
        {
            $('.vip-name').show();
            $('.person_name').hide();
        }
        else{
            $('.vip-name').hide();
            $('.person_name').show();
        }
        
        // change value on click
        $('.vip_type').on('change', function() {
            var selectedValue = $(this).val();
            if(selectedValue === 'MP')
            {
                $('.vip-name').show();
                $('.person_name').hide();
                $('.referring_person_name').val('');
            }
            else{
                $('.vip-name').hide();
                $('.person_name').show();
                $('select[name="mp_id"]').val('');
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const proposalPdfInput = document.getElementById('proposal_pdf');
        const proposalPdfLabel = document.getElementById('proposal_pdf_label');

        // Update label with selected file name when a file is chosen
        proposalPdfInput.addEventListener('change', function () {
            if (proposalPdfInput.files.length > 0) {
                proposalPdfLabel.textContent = proposalPdfInput.files[0].name;
            }
        });
    });
</script>
@endsection
