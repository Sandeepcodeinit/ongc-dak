@extends('_app')
@php
    $pageNm = 'Hard Copy Proposal';
    $user     = AuthUser() ?? [];
@endphp
@section('title', $pageNm)
@section('content')
<div class="content-wrapper">
    <section class="content">
        <!-- Import file for proposal which comes in hard copy format-->
        <div class="card">
            <div class="card-header" style="background-color: #84363a; color:#fff;">
                <h3 class="card-title">{{ $pageNm }} List</h3>
                <button type="button" class="btn btn-success btn-sm float-right" onclick="linkClick(this);" data-link="{{ route('hard_copy_proposal.add') }}">Add New</button>
            </div>
            <div class="card-body">
                @php 
                    if(1){  
                        $proposalTitle  = $inputData['proposal_title'] ?? '';
                        $agencyTitle    = $inputData['agency_name'] ?? '';
                        $is_mopng       = $inputData['is_mopng'] ?? '';
                        $vip_type       = $inputData['vip_person_name'] ?? '';
                        $mla_name       = $inputData['other_vip_name'] ?? '';
                        $length         = $inputData['length'] ?? 100;
                        $getMpList      = getMpList();
                        $getCommiteeList = getCommiteeList();
                        $user_type 	    = AuthUser()->user_type ?? 0;
                    }
                @endphp
                <section class="filter-function">
                    <div class="heading-format">
                        <h2>Filter Hard Copy Proposal By:
                            <button type="button" class="btn btn-xs btn-success float-right" data-link="{{ route('hard_copy_proposal', ['action' => 'reset']) }}" onclick="linkClick(this);"><i class="fa fa-refresh"></i> Clear all filter</button>
                        </h2>
                    </div>

                    <form action="{{ route('hard_copy_proposal') }}" method="post" class="frmSearch">
					    @csrf
                        <div class="row">
                            <div class="col-md-5 col-lg-5">
                                <input type="text" class="form-control proposal_title" name="proposal_title" value="{{ $proposalTitle }}" placeholder="By Proposal title">
                            </div>

                            <div class="col-md-5 col-lg-5">
                                <input type="text" class="form-control agency_name" name="agency_name" value="{{ $agencyTitle }}" placeholder="By Agency Name">
                            </div>
                        
                            <div class="col-md-2 col-lg-2">
                                <select class="form-select is_mopng" name="is_mopng">
                                    <option value="">MoPNG or not</option>
                                    <option value="1" {{ $is_mopng == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ $is_mopng == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-2 col-lg-2">
                                <select class="form-select vip_person_name" name="vip_person_name">
                                    <option value="">MLA/MP</option>
                                    <option value="MP" {{ $vip_type == 'MP' ? 'selected' : '' }}>MP</option>
                                    <option value="MLA" {{ $vip_type == 'MLA' ? 'selected' : '' }}>MLA</option>
                                    <option value="Other" {{ $vip_type == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-3 mp_select" style="display:none;">
                                <select class="form-control mp_name select2 w-100" name="mp_name" id="mp_name"  style="width:100%;">
                                    <option value="">Select VIP Name</option>
                                    @foreach($getMpList as $mpList)
                                        <option value="{{ $mpList->id }}" {{ $mpList->id == $mp_name ? 'selected' : '' }}>{{ $mpList->vip_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 col-lg-3 vip_commitee">
                                <select class="form-control select2 mp_name w-100" name="vip_commitee" id="vip_commitee" style="width:100%;">
                                    <option value="">Select Standing Commitee</option>
                                    @foreach($getCommiteeList as $getCommitee)
                                        <option value="{{ $getCommitee->id }}" {{ $getCommitee->id == $vip_commitee ? 'selected' : '' }}>
                                            {{ $getCommitee->committee_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="vip_input" style="display:none;">
                                <input type="text" class="form-control" name="other_vip_name" placeholder="Enter MLA Name" value="{{ $mla_name }}">
                            </div>
                          
                            <div class="col-md-1 col-lg-1">
                                <select class="form-select length" name="length" aria-label="Show per page">
                                    <option value="">Show per page</option>
                                    <option value="100" {{ $length==100?'selected':'selected' }}>100</option>
                                    <option value="200" {{ $length==200?'selected':'' }}>200</option>
                                    <option value="500" {{ $length==500?'selected':'' }}>500</option>
                                    <option value="800" {{ $length==800?'selected':'' }}>800</option>
                                    <option value="1000" {{ $length==1000?'selected':'' }}>1000</option>
                                </select>
                            </div>

                            <div class="col-md-1 col-lg-1">
                                <button type="submit" class="btn" style="background-color: #84363a; color:#fff;">Submit</button>
                            </div>
                        </div>
                    </form>
                </section>
                <div class="row">
                    <div class="col-lg-6">
                        <div id="export-button-container"></div>
                    </div>
                    <div class="col-lg-12">
                        <table class="table table-bordered {{ $user_type < 99?'tableExport tblResponsiv':'tblResp' }}" data-pgNam="{{ $pageNm }} Print {{ date('d-m-Y h:i:s A') }}" width="100%" style="min-width: 100%;">
                            <thead>
                                <tr class="bg-dark">
                                    <th>S no.</th>
                                    <th>Submission Date</th>
                                    <th>Proposal Title</th>
                                    <th>Proposal Cost</th>
                                    <!-- <th>Is Considered or Not</th> -->
                                    <th>Proposal Status</th>
                                    <th>Remarks</th>
                                    <th>Aspirational District</th>
                                    <th>State</th>
                                    <!-- <th>District</th> -->
                                    <th>MoPNG Reference</th>
                                    <!-- <th>VIP Type</th> -->
                                    <th>VIP Name</th>
                                    <th>Implementing Agency</th>
                                    <th>Commitee Name</th>
                                    <th>FPR Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $i = ($getHardCopyProposalDetails->currentPage() - 1) * $getHardCopyProposalDetails->perPage() + 1; // Calculate the starting number for the serial
                                @endphp
                                @foreach ($getHardCopyProposalDetails as $getHardCopyProposalDetail)
        
                                    @php 
                                        $date = $getHardCopyProposalDetail->project_receipt_date ?? '';
                                        $formatDate = date("d-m-Y", strtotime($date)) ?? '';  
                                    @endphp 
                                    <tr>
                                        @php 
                                            $committeeId = $getHardCopyProposalDetail->commitee_id ?? 0; 
                                            $getCommiteeName = getCommiteeName($committeeId);
                                        @endphp
                                            
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ $i++ }}</td> 
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ $formatDate }}</td>
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ strtoupper($getHardCopyProposalDetail->proposal_title ?? '') }}</td>
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ formatIndianRupees($getHardCopyProposalDetail->proposal_cost ?? 0) }}</td>
                                        @php 
                                            if($getHardCopyProposalDetail->is_approved == 0)
                                            {
                                                $isApproved = 'No';
                                            }else{
                                                $isApproved = 'Yes';
                                            }
                                        @endphp
                                        <!-- <td style="padding-top: 2px; padding-bottom: 2px;">{{ $isApproved }}</td> -->
                                        @php
                                            $proposalStatus = (empty($getHardCopyProposalDetail->proposal_status) || 
                                            $getHardCopyProposalDetail->is_aspirrational_district == 0 || 
                                            strtolower($getHardCopyProposalDetail->is_aspirrational_district) === 'no') ? 'No' : 'Yes';
                                        @endphp
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ $getHardCopyProposalDetail->proposal_status ?? '' }}</td>

                                        <td 
                                            style="padding-top: 2px; padding-bottom: 2px; word-wrap: break-word; white-space: normal;" 
                                            data-toggle="tooltip" 
                                            title="{{ $getHardCopyProposalDetail->remarks }}">
                                            {{ Str::limit($getHardCopyProposalDetail->remarks, 20) }}
                                        </td>
                                        @php
                                            $isAspirational = (empty($getHardCopyProposalDetail->is_aspirrational_district) || 
                                            $getHardCopyProposalDetail->is_aspirrational_district == 0 || 
                                            strtolower($getHardCopyProposalDetail->is_aspirrational_district) === 'no') ? 'No' : 'Yes';
                                        @endphp

                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ $isAspirational }}</td> 
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ $getHardCopyProposalDetail->project_location }}</td>
                                        <!--<td style="padding-top: 2px; padding-bottom: 2px;">{{ $getHardCopyProposalDetail->district }}</td> -->
                                        @php
                                            $moPng = (empty($getHardCopyProposalDetail->mopng_reference) || 
                                            $getHardCopyProposalDetail->mopng_reference == 0 || 
                                            strtolower($getHardCopyProposalDetail->mopng_reference) === 'no') ? 'No' : 'Yes';
                                        @endphp
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ $moPng }}</td>
                                        <!-- <td style="padding-top: 2px; padding-bottom: 2px;">{{ $getHardCopyProposalDetail->vip_type }}</td> -->

                                        @if($getHardCopyProposalDetail->vip_type == 'MLA' || $getHardCopyProposalDetail->vip_type == 'Other')
                                            <td style="padding-top: 2px; padding-bottom: 2px;">{{ $getHardCopyProposalDetail->referring_person_name }}</td>
                                        @else
                                        @php
                                            $getMpList = $getHardCopyProposalDetail->mp_id ? getMpList($getHardCopyProposalDetail->mp_id) : null;
                                        @endphp
                                        <td style="padding-top: 2px; padding-bottom: 2px;">
                                            {{ $getMpList ? $getMpList->vip_name : '' }}
                                        </td>
                                        @endif
                                       
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ $getHardCopyProposalDetail->implementing_agency }}</td>
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ $getCommiteeName ?? '' }}</td>
                                        <td style="padding-top: 2px; padding-bottom: 2px;">{{ $getHardCopyProposalDetail->fpr_name }}</td>
                                       
                                        
                                        <td><a href="{{ route('hard_copy_proposal.edit', ['id' => $getHardCopyProposalDetail->id]) }}" class="btn btn-sm btn-success btnEdit">Edit</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-lg-12">
                        {{ $getHardCopyProposalDetails->appends($inputData)->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>

            @if($user->user_type != 8)
                <!-- Import excel -->
                <div class="card-body">
                    <div class="card-header" style="background-color: #84363a; color:#fff;">
                        <h3 class="card-title">{{ $pageNm }}</h3>
                    </div>
                    <p class="mb-0 text-danger">* Select/ Import only csv file and max size is 5 MB.  </p>
                    <hr class="w-100 mt-2 mb-2">
                    <form action="{{ route('hard_copy_proposal.import') }}" method="post" class="frmHardCopyProposal" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <label for="importHardCopyProposal" class="col-sm-1">CSV File</label>
                            <div class="col-sm-6">
                                <div class="custom-file custom-file-sm">
                                    <input type="file" class="custom-file-input custom-file-input-sm" name="importHardCopyProposal" id="importHardCopyProposal" accept=".csv" required>
                                    <label class="custom-file-label custom-file-label-sm" for="importHardCopyProposal">Choose file</label>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-sm btn-success" name="import"> Import</button>
                                <button type="button"  class="btn btn-sm btn-warning" onclick="linkClick(this);" data-link="{{ route('hard_copy_proposal') }}"> Refresh</button>
                            </div>
                            <div class="col-sm-3">
                                <a href="{{ asset('storage/more_info_docs/hard_copy_proposal.csv') }}" class="btn btn-sm float-right" style="background-color: #84363a; color:#fff;" download="">Download Import Excel Format</a>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </section>
</div>

@endsection

@section('javascript')
    <script>
        var getVal = $('.vip_person_name').val();
        if(getVal == 'MP')
        {
            $(".mp_select").css("display", "block");
        }
        else if(getVal == 'MLA' || getVal == 'Other')
        {
            $(".vip_input").css("display", "block");
        }
        $('.vip_person_name').on('change', function() {
            var selectedValue = $(this).val();  // Get selected value
            if(selectedValue == 'MP') 
            {
                // Show MP dropdown and hide the input field
                $(".mp_select").css("display", "block");
                $(".vip_input").css("display", "none");
            } 
            else if (selectedValue == 'MLA' || selectedValue == 'Other') {
                // Show input field for other VIP types and hide MP dropdown
                $(".mp_select").css("display", "none");
                $(".vip_input").css("display", "block");
            } 
            else {
                // Hide both if no value is selected
                $(".mp_select, .vip_input").css("display", "none");
            }
        });
   
        var table = $('.tblResponsiv').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": false,
        "ordering": false,
        "info": false,
        "autoWidth": false,
        "responsive": true,
        "dom": 'Bfrtip',
            "buttons": [
            {
            extend: 'pdfHtml5',
            title: $(".tableExport").attr('data-pgNam'),
            filename: ($('.tableExport').attr('data-pgNam').replaceAll("\n", ",")).replaceAll(":", "-"),
            text: '<i class="fa fa-file-pdf"></i> Export to PDF',
            className: 'btn-sm btn-info',
            orientation: 'landscape',
            pageSize: 'A4',
            exportOptions: {
                columns: ':visible' // Export only the visible columns
            },
            customize : function(doc) {
                doc.defaultStyle.fontSize = 12;
                

                if (doc.content && doc.content[1] && doc.content[1].table && doc.content[1].table.body) {
                // Remove last column from each row
                doc.content[1].table.body.forEach(function(row) {
                    if (row.length > 0) {
                    row.pop(); // Remove last cell from each row
                    }
                });
                // Check if widths are defined and non-empty
                if (doc.content[1].table.widths && doc.content[1].table.widths.length > 0) {
                    // Remove width of last column
                    doc.content[1].table.widths.pop();
                }
                }

                doc.pageMargins = [20, 20, 20,20 ]; 
                //doc.content[0].text = "SALES ORDER";
                doc.footer = function(page, pages) {
                return {
                    margin: [5, 0, 10, 0],
                    height: 30,
                    columns: [{
                    alignment: "center",
                    text: [
                        { text: page.toString(), italics: true },
                        " of ",
                        { text: pages.toString(), italics: true }
                    ]
                    }]
                }
                }   
            },
            },
            {
            extend: 'excel',
            title: $(".tableExport").attr('data-pgNam'),
            text: '<i class="fa fa-file-excel"></i> Export to csv', // Optional custom button text
            exportOptions: {
                modifier: {
                page: 'all'
                },
                columns: ':not(:last-child):visible'
            },
            filename: ($('.tableExport').attr('data-pgNam').replaceAll("\n", ",")).replaceAll(":", "-"),
            action: function (e, dt, button, config) {
                // Your custom function to be executed on Excel button click
                console.log(123);
                $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
            },
            className: 'btn-sm btn-warning'
            },
            {
            extend: 'colvis', // Use 'colvis' as the extend value
            text: '<i class="fa fa-columns"></i> Select Columns',
            columns: ':not(:last-child)'
            }
        ]
        }).buttons().container().appendTo($('#export-button-container'));

        // Add class to responsive data
        $('.tblResponsiv tbody').on('click', '.dtr-control', function() {
            $('.bootstrapSwitch').bootstrapSwitch();
            var trr = $(this).closest('tr');
            $('.dtr-details li').each(function(index){
                var index = $(this).attr('data-dtr-index');
                var row = $(this).attr('data-dt-row');
                var clsName = trr.find('td:eq('+index+')').attr('class');
                if(clsName != undefined){
                    $(this).find('.dtr-data').addClass(clsName);
                }
            });
        });
        function showWCList(){
            var agency_status = $('.agency_status').val();
            var user_type = $('.frmSearch').attr('data-user_type');
            if(parseInt(user_type) == 3){
                $('.divAddressTo').css('display', 'none');
                if(parseInt(agency_status) == 1){
                    $('.divAddressTo').css('display', 'block');
                }
            }
        }
        showWCList();

        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection