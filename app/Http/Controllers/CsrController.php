<?php

namespace App\Http\Controllers;

use App\Models\Csr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\Proposal;
use App\Http\Controllers\CronController;

class CsrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = session('user') ?? [];
        $user_idd = (int) data_get($user, 'id', 0);
        $user_type = data_get($user, 'user_type', 0);
        $proposal_count = data_get($user, 'proposal_count', 0);
        $data = [];

        if (($user_type >= 0 && $user_type <= 10) && $user_idd > 0) {
            $pg = 'dashboard';
        } else {
            $pg = 'dashboard';
        }

        $data['workCenterNames'] = DB::table('addresstos')->where('status', 1)->get();
        $data['workCenter'] = data_get($user, 'work_station', null);

        return view($pg)->with($data);
    }

    public function dashboard(Request $request)
    {
        $user = session('user') ?? [];

        if (empty($user)) {
            return redirect()->route('login');
        }

        $userType = data_get($user, 'user_type', 0);
        $userWorkStation = data_get($user, 'work_station', 0);

        // Request parameter
        $startDate  = @$request->start_date;
        $endDate    = @$request->end_date;
        $callFrom   = @$request->callFrom;

        // If logged-in user is chief
        if($userType == 3 && $userWorkStation != 1)
        {
            $workCenter = $userWorkStation;
        }
        elseif($userWorkStation == 1 )
        {
            $workCenter = @$request->work_center;
        }
        else{
            $workCenter = @$request->work_center;
        }

        $getBudgetTotal = 0;
        $releasedAmount = 0;

        if($workCenter != null)
        {
            // Get allowed amount
            $getBudgetData = DB::table('work_center_budgetary')->where('budget_delete', 0)->where('budget_wc_id', $workCenter)->orderByDesc('budget_financial_year')->first();

            if($getBudgetData != null)
            {
                $getBudgetTotal = $getBudgetData->budget_total;
            }
            
            $releasedAmount = DB::table('proposal_amt_milestones')->where('grant_wc_id', $workCenter)->sum('grant_amount');
        }
            
        if($startDate == null || $startDate == ''){
            // $month = date('m');
            // if($month < 4){
            //     $startDate = (date('Y')-1).'-04-01';
            // }else{
            //     $startDate = date('Y').'-04-01';
            // }
            $startDate = '2024-04-01';
            $endDate = date('Y-m-d');
        }

        $pg = 'dashboard';
        $data = [];
        
        // Get total registered Agency details
        $agencyData = DB::table('agencies')->where('financial_save', 1)->distinct('pan_card_no')
        ->when(!empty($startDate), function ($query) use ($startDate) {
            return $query->whereDate('agency_save_date', '>=', $startDate);
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            return $query->whereDate('agency_save_date', '<=', $endDate);
        });
    
        // Agency count with work-center or with not work-center
        $agencyCount = ($workCenter !== null) ? $agencyData->where('work_center_id', $workCenter)->count() : $agencyData->count();

        // Get total verified agency
        $verifiedAgencyData = DB::table('agencies')->where('agency_verified', 1)->distinct('pan_card_no')
        ->when(!empty($startDate), function ($query) use ($startDate) {
            return $query->whereDate('agency_save_date', '>=', $startDate);
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            return $query->whereDate('agency_save_date', '<=', $endDate);
        });

        // Verified Agency count with work-center or with not work-center
        $verifiedAgencyCount = ($workCenter !== null) ? $verifiedAgencyData->where('work_center_id', $workCenter)->count() : $verifiedAgencyData->count();

        // Get new agencies details
        $newAgencies =  DB::table('agencies')->where('financial_save', 1)->where('agencies.chief_by_id', null)
        //->orWhere('agencies.fpr_verified_status', 2)
        //->orWhere('agencies.tl_verified_status', 2)
        ->when(!empty($startDate), function ($query) use ($startDate) {
            return $query->whereDate('agency_save_date', '>=', $startDate);
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            return $query->whereDate('agency_save_date', '<=', $endDate);
        });

        // New Agency count with work-center or with not work-center
        $newAgencyCount = ($workCenter !== null) ? $newAgencies->where('work_center_id', $workCenter)->count() : $newAgencies->count();

        // In-progress Agency count with work-center or with not work-center
        $inProgressAgencies =  DB::table('agencies')->where('financial_save', 1)->where('agency_status', 1)->where('agency_verified', 0)
        ->when(!empty($startDate), function ($query) use ($startDate) {
            return $query->whereDate('agency_save_date', '>=', $startDate);
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            return $query->whereDate('agency_save_date', '<=', $endDate);
        })
        ->get();

        // In-progress Agency count with work-center or with not work-center
        $inProgressAgencyCount = ($workCenter !== null) ? $inProgressAgencies->where('work_center_id', $workCenter)->count() : $inProgressAgencies->count();
        
        // Parked Agency count with work-center or with not work-center
        $parkedAgencies =  DB::table('agencies')->where('financial_save', 1)->where('agency_status', 3)
        ->when(!empty($startDate), function ($query) use ($startDate) {
            return $query->whereDate('agency_save_date', '>=', $startDate);
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            return $query->whereDate('agency_save_date', '<=', $endDate);
        })->get();

        // Parked Agency count with work-center or with not work-center
        $parkedAgencyCount = ($workCenter !== null) ? $parkedAgencies->where('work_center_id', $workCenter)->count() : $parkedAgencies->count();
        
        // Get details of Total proposal
        $proposalData = DB::table('proposals')->where('final_save', 1)
        ->whereNotNull('final_save_date')
        ->when($startDate !== null, function ($query) use ($startDate) {
            return $query->whereDate('final_save_date', '>=', $startDate);
        })
        ->when($endDate !== null, function ($query) use ($endDate) {
            return $query->whereDate('final_save_date', '<=', $endDate);
        });
        
      
        // Proposal count with work-center or with not work-center
        $proposalCompletedCount = ($workCenter !== null ) ? $proposalData->where('address_to', $workCenter)->count() : $proposalData->count();
        
        // Get details of Total proposal with PV NUmber
        $proposalPVData = DB::table('proposals')->where('final_save', 1)->where('pv_status', 2)
        ->whereNotNull('final_save_date')
        ->when($startDate !== null, function ($query) use ($startDate) {
            return $query->whereDate('final_save_date', '>=', $startDate);
        })
        ->when($endDate !== null, function ($query) use ($endDate) {
            return $query->whereDate('final_save_date', '<=', $endDate);
        })
        ->when($workCenter !== null , function ($query) use ($workCenter) {
            return $query->where('address_to', $workCenter);
        });

        // Get details of In-progress proposal
        $inProgressProposals = DB::table('proposals')->where('final_save', 1)->where('pv_status', '!=' , 2)->where('chief_assign_to_head', '>', 0)
        ->when($startDate !== null, function ($query) use ($startDate) {
            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
        })
        ->when($endDate !== null, function ($query) use ($endDate) {
            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
        })
        ->when($workCenter !== null , function ($query) use ($workCenter) {
            return $query->where('address_to', $workCenter);
        });

        // Get In-proposal
        $inProgressCount = $inProgressProposals->count();

        // Get details of new proposal
        $newProposals = DB::table('proposals')->where('final_save', 1)->where('chief_assign_by', 0)
        ->when($startDate !== null, function ($query) use ($startDate) {
            return $query->whereDate('final_save_date', '>=', $startDate);
        })
        ->when($endDate !== null, function ($query) use ($endDate) {
            return $query->whereDate('final_save_date', '<=', $endDate);
        })
        ->when($workCenter !== null , function ($query) use ($workCenter) {
            return $query->where('address_to', $workCenter);
        });

        // Get new-proposal count
        $newProposalCount = $newProposals->count();

        // Get details of approved proposal
        $proposalQuery = DB::table('proposals')->where('final_save', 1)
        ->when($startDate !== null, function ($query) use ($startDate) {
            return $query->whereDate('final_save_date', '>=', $startDate);
        })
        ->when($endDate !== null, function ($query) use ($endDate) {
            return $query->whereDate('final_save_date', '<=', $endDate);
        })
        ->when($workCenter !== null , function ($query) use ($workCenter) {
            return $query->where('address_to', $workCenter);
        });
        $approvedProposals = clone $proposalQuery;
        $benificeryProposals = clone $proposalQuery;

        $approvedProposals->where('disha_verify_yn', 1);
        // Get Total proposal with PV number
        $approvedProposalCount = $approvedProposals->count();

        // Get Total proposal with PV number
        $proposalPV = $proposalPVData->count();
        $proposalPVCount = $proposalPV - $approvedProposalCount;

        // Get beneficiery details
        $getTotalScData = $benificeryProposals->sum('schedule_caste_number');
        $getTotalStData = $benificeryProposals->sum('schedule_tribe_number');
        $getTotalObcData =  $benificeryProposals->sum('obc_number');
        $getTotalMiniorityData =  $benificeryProposals->sum('minority_number');
        $getTotalGeneralData =  $benificeryProposals->sum('general_number');

        // Get Total parked proposal
        $parkedProposals = DB::table('proposals')->where('final_save', 1)->where('chief_assign_to_head', '<', 0)
        ->when($startDate !== null, function ($query) use ($startDate) {
            return $query->whereDate('final_save_date', '>=', $startDate);
        })
        ->when($endDate !== null, function ($query) use ($endDate) {
            return $query->whereDate('final_save_date', '<=', $endDate);
        })
        ->when($workCenter !== null , function ($query) use ($workCenter) {
            return $query->where('address_to', $workCenter);
        });
        $parkedProposalCount = $parkedProposals->count();

        $data['agencyCount'] = $agencyCount;
        $data['proposalCompletedCount'] = $proposalCompletedCount;
        $data['verifiedAgencyCount'] = $verifiedAgencyCount;
        $data['proposalPVCount'] = $proposalPVCount;
        $data['newAgencyCount'] = $newAgencyCount;
        $data['inProgressAgencyCount'] = $inProgressAgencyCount;
        $data['parkedAgencyCount'] = $parkedAgencyCount;
        $data['inProgressCount'] = $inProgressCount;
        $data['newProposalCount'] = $newProposalCount;
        $data['approvedProposalCount'] = $approvedProposalCount;
        $data['parkedProposalCount'] = $parkedProposalCount;
        $data['getTotalScData'] = $getTotalScData;
        $data['getTotalStData'] = $getTotalStData;
        $data['getTotalObcData'] = $getTotalObcData;
        $data['getTotalMiniorityData'] = $getTotalMiniorityData;
        $data['getTotalGeneralData'] = $getTotalGeneralData;
     
        // Get count from proposals table as per their status
        $openCount = DB::table('proposals')->where('final_save', 1)->where('proposal_status', 1)
        ->when(!empty($startDate), function ($query) use ($startDate) {
            return $query->whereDate('final_save_date', '>=', $startDate);
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            return $query->whereDate('final_save_date', '<=', $endDate);
        })
        ->count();

        $closedCount = DB::table('proposals')->where('final_save', 1)->where('proposal_status', 2)
        ->when(!empty($startDate), function ($query) use ($startDate) {
            return $query->whereDate('final_save_date', '>=', $startDate);
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            return $query->whereDate('final_save_date', '<=', $endDate);
        })
        ->count();

        $awaitedCount = DB::table('proposals')->where('final_save', 1)->where('proposal_status', 3)
        ->when(!empty($startDate), function ($query) use ($startDate) {
            return $query->whereDate('final_save_date', '>=', $startDate);
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            return $query->whereDate('final_save_date', '<=', $endDate);
        })
        ->count();

        $workCenterNames = DB::table('addresstos')->where('status',1)->get();

        $data['openCount'] = $openCount;
        $data['closedCount'] = $closedCount;
        $data['awaitedCount'] = $awaitedCount;
        $data['workCenterNames'] = $workCenterNames;
    
        // Get years
        $years = DB::table('proposals')
            ->select(DB::raw('YEAR(final_save_date) as year'))
            ->whereNotNull('final_save_date')
            ->distinct()
            ->pluck('year');
    
        // Array to store total amounts for each dynamic year
        $totalAmounts = [];
    
        // Loop through each dynamic year
        foreach ($years as $year) {
            // Loop through each month
            for ($month = 1; $month <= 12; $month++) {
                $totalAmount = DB::table('proposals')
                    ->where('final_save', 1)
                    ->whereNotNull('final_save_date')
                    ->whereYear('final_save_date', $year)
                    ->whereMonth('final_save_date', $month)
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->sum('project_cost');

                // Only store the total amount for the current year and month if it's greater than 0
                if ($totalAmount > 0) {
                    $totalAmounts[$year][$month] = $totalAmount;
                }
            }
        }
        $data['startDate'] = $startDate;
        $data['endDate'] = $endDate;
    
        $data['totalAmounts'] = $totalAmounts;
        $data['workCenter'] = $workCenter;

        $data['getBudgetTotal'] = $getBudgetTotal;
        $data['releasedAmount'] = $releasedAmount;
        
        if($callFrom == 'json'){
            return response()->json(['status' => 1, 'result' => $data]);
        }else{
            return view($pg)->with($data);
        }
    }

    /**
        * This function all display data of Pie chart
    */
    public function pieGraph(Request $request)
    {
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE'");

        $stateData    = @$request->state_pin;
        $proposalData = @$request->proposal_data;
        $startDate    = @$request->start_date;
        $endDate      = @$request->end_date;

        $user         = @AuthUser();
        $workCenterId = @$user->work_station;
        $userType     = @$user->user_type;
        // $workCenterId = @$request->workCenterId;
    
        $proposalsDate = DB::table('proposals')
            ->select('prop_id', 'final_save_date')

            ->when($startDate !== null, function ($query) use ($startDate) {
                return $query->whereDate('final_save_date', '>=', $startDate);
            })
            ->when($endDate !== null, function ($query) use ($endDate) {
                return $query->whereDate('final_save_date', '<=', $endDate);
            })
            ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                return $query->where('address_to', $workCenterId);
            })
            ->get();
            //echo "-->".$proposalsDate->pluck('prop_id') ; die;

        switch ($stateData) {
            case 'state':
                if ($proposalData == 'proposal_count') {
                    $results = DB::table('proposals_pincodes')
                    ->select(
                        DB::raw('COUNT(DISTINCT `proposals_pincodes`.`propp_id`) as y'),
                        'proposals_pincodes.state_cd as id',
                        DB::raw('(SELECT pincode.state_name FROM pincode WHERE pincode.state_code = `proposals_pincodes`.`state_cd` LIMIT 1) as name')
                    )
                    ->when(!empty($startDate) || !empty($endDate) || !empty($workCenterId)  , function ($query) use ($proposalsDate) {
                        return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                    })
                    ->groupBy('proposals_pincodes.state_cd')
                    ->get();
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'State', 'yAxis' => 'Total Proposal']);
                }
                elseif ($proposalData == 'proposal_amount'){

                    // $proposalData = DB::table('proposals')->where('final_save',1)->where('pv_status',2)->where('disha_proposal_amt', '>=',0);
                    // $projectIds = $proposalData->pluck('prop_id')->toArray();

                    // $pincodeRecords = DB::table('proposals_pincodes')
                    // ->select('propp_id', 'budget_percent', 'disha_approved_amt')
                    // ->whereIn('propp_id', $projectIds)
                    // ->whereNotNull('budget_percent')
                    // ->whereNotNull('disha_approved_amt')
                    // ->get();

                    // $stateWiseAmounts = [];
        
                    // foreach ($pincodeRecords as $pin) {
                    //     $projectId = $pin->propp_id;
                
                    //     $statewiseAmount = ($pin->disha_approved_amt * $pin->budget_percent) / 100;
                
                    //     // If multiple rows for same proposal, sum them
                    //     if (isset($stateWiseAmounts[$projectId])) {
                    //         $stateWiseAmounts[$projectId] += $statewiseAmount;
                    //     } else {
                    //         $stateWiseAmounts[$projectId] = $statewiseAmount;
                    //     }
                    // }   
                   

                    // // Now display each proposal's amount
                    // foreach ($stateWiseAmounts as $proposalId => $amount) {
                    //     dd($amount);
                    //     // echo "Proposal ID: <strong>{$proposalId}</strong> — State-wise Amount: ₹" . number_format($amount, 2) . "<br>";
                    //     // dd($amount);
                    // }

                    // dd($amount);


                    // $results = DB::table('proposals_pincodes')
                    //         ->select(
                    //             DB::raw('0 as y'),
                    //             'proposals_pincodes.state_cd as id',
                    //             DB::raw('(SELECT pincode.state_name FROM pincode WHERE pincode.state_code = `proposals_pincodes`.`state_cd` LIMIT 1) as name')
                    //         )
                    //         ->when(!empty($startDate) || !empty($endDate) ||  !empty($workCenterId)  , function ($query) use ($proposalsDate) {
                    //             return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                    //         })
                    //         ->groupBy('proposals_pincodes.state_cd')
                    //         ->get();
                    //         foreach ($results as $key => $roww) {
                    //             $stateId = $roww->id;
                    //             $sum = DB::table('proposals')
                    //                     ->whereIn('prop_id', function($query) use($stateId) {
                    //                         $query->select('propp_id')
                    //                             ->from('proposals_pincodes')
                    //                             ->where('state_cd', $stateId);
                    //                     })
                    //                     ->sum('disha_proposal_amt');
                    //             $roww->y = $sum;
                    //         }
                    /*$results = DB::table('proposals_pincodes')
                    ->select(
                        DB::raw('(
                            SELECT SUM(proposals.disha_proposal_amt) 
                            FROM proposals 
                            WHERE proposals.prop_id IN (
                                SELECT GROUP_CONCAT(proposals_pincodes.propp_id)
                            )
                        ) as y'),
                        'proposals_pincodes.state_cd as id',
                        DB::raw('(SELECT pincode.state_name FROM pincode WHERE pincode.state_code = `proposals_pincodes`.`state_cd` LIMIT 1) as name')
                    )
                    ->join('proposals', 'proposals.prop_id', '=', 'proposals_pincodes.propp_id')
                    ->when(!empty($startDate) || !empty($endDate) || !empty($workCenterId)  , function ($query) use ($proposalsDate) {
                        return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                    })
                    ->where('proposals.final_save', 1)
                    ->where('proposals.disha_verify_yn', 1)
                    ->groupBy('proposals_pincodes.state_cd')
                    ->get();*/
                    // return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'State', 'yAxis' => 'Total Proposal']);
                    


                    // Step 1: Get valid proposals
                    $proposalData = DB::table('proposals')
                        ->where('final_save', 1)
                        ->where('pv_status', 2)
                        ->where('disha_proposal_amt', '>=', 0);

                    $projectIds = $proposalData->pluck('prop_id')->toArray();

                    // Step 2: Get pincode records with state info
                    $pincodeRecords = DB::table('proposals_pincodes')
                        ->select('propp_id', 'budget_percent', 'disha_approved_amt', 'state_cd')
                        ->whereIn('propp_id', $projectIds)
                        ->whereNotNull('budget_percent')
                        ->whereNotNull('disha_approved_amt')
                        ->whereNotNull('state_cd')
                        ->get();

                        // Step 3: Aggregate amount by state
                        $stateWiseAmounts = [];
                        foreach ($pincodeRecords as $pin) {
                            $stateCd = $pin->state_cd;
                            $amount = ($pin->disha_approved_amt * $pin->budget_percent) / 100;

                            if (isset($stateWiseAmounts[$stateCd])) {
                                $stateWiseAmounts[$stateCd] += $amount;
                            } else {
                                $stateWiseAmounts[$stateCd] = $amount;
                            }
                        }

                        // Step 4: Get state names
                        $stateNames = DB::table('pincode')
                            ->select('state_code', 'state_name')
                            ->groupBy('state_code', 'state_name')
                            ->pluck('state_name', 'state_code');

                        // Step 5: Prepare chart data
                        $results = [];
                        foreach ($stateWiseAmounts as $stateCd => $amount) {
                            $results[] = [
                                'name' => $stateNames[$stateCd] ?? $stateCd,
                                'y' => round($amount, 2)
                            ];
                        }

                    // Step 6: Return data as JSON
                    return response()->json([
                        'status' => 1,
                        'result' => $results,
                        'labelName' => 'State',
                        'yAxis' => 'DISHA Approved Amount'
                    ]);
                }

                
            break;
            case 'city':
                if($proposalData == 'proposal_count')
                {
                    $results = DB::table('proposals_pincodes')
                    ->select(
                        DB::raw('COUNT(DISTINCT `proposals_pincodes`.`propp_id`) as y'),
                        'proposals_pincodes.district_cd as id',
                        DB::raw('(SELECT pincode.district_name FROM pincode WHERE pincode.district_code = `proposals_pincodes`.`district_cd` LIMIT 1) as name')
                    )
                    ->when(!empty($startDate) || !empty($endDate) ||  !empty($workCenterId) , function ($query) use ($proposalsDate) {
                        return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                    })
                    ->whereNotNull('proposals_pincodes.district_cd')
                    ->where('proposals_pincodes.district_cd', '<>', '')
                    ->groupBy('proposals_pincodes.district_cd') 
                    ->get();

                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Proposal']);
                }
            break;
            case 'status':
                if($proposalData == 'proposal_count')
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->select(
                            DB::raw('SUM(CASE WHEN proposal_status = 1 THEN 1 ELSE 0 END) as Open'),
                            DB::raw('SUM(CASE WHEN proposal_status = 2 THEN 1 ELSE 0 END) as Close'),
                            DB::raw('SUM(CASE WHEN proposal_status = 3 THEN 1 ELSE 0 END) as Await'),
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'name' => 'Open',
                                'y' => (int)$getDetail->Open,
                                'id'=> 1
                            ];
                            $results[] = [
                                'name' => 'Close',
                                'y' => (int)$getDetail->Close,
                                'id'=> 2
                            ];
                            $results[] = [
                                'name' => 'Await',
                                'y' => (int)$getDetail->Await,
                                'id'=> 3
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Proposal']);
                }
                // elseif ($proposalData == 'proposal_amount') 
                // {
                //     $getDetails = DB::table('proposals')
                //         ->where('final_save', 1)
                //         ->select(
                //             DB::raw('SUM(CASE WHEN proposal_status = 1 THEN fpr_project_cost ELSE 0 END) as Open'),
                //             DB::raw('SUM(CASE WHEN proposal_status = 2 THEN fpr_project_cost ELSE 0 END) as Close'),
                //             DB::raw('SUM(CASE WHEN proposal_status = 3 THEN fpr_project_cost ELSE 0 END) as Await')
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('final_save_date', '<=', $endDate);
                //         })
                //         // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //         //     return $query->where('address_to', $workCenterId);
                //         // })
                //         ->get();

                //         // Format data for Highcharts
                //         $results = [];
                //         foreach ($getDetails as $getDetail) {
                //             $results[] = [
                //                 'name' => 'Open',
                //                 'y' => (int)$getDetail->Open,
                //                 'id'=> 1
                //             ];
                //             $results[] = [
                //                 'name' => 'Close',
                //                 'y' => (int)$getDetail->Close,
                //                 'id'=> 2
                //             ];
                //             $results[] = [
                //                 'name' => 'Await',
                //                 'y' => (int)$getDetail->Await,
                //                 'id'=> 3
                //             ];
                //         }
                //     return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Amount']);   
                // }
                elseif ($proposalData == 'proposal_amount')
                {
                    $getDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->where('proposals.disha_verify_yn',1)
                    ->select(
                        DB::raw('SUM(CASE WHEN proposal_status = 1 THEN disha_proposal_amt ELSE 0 END) as Open'),
                        DB::raw('SUM(CASE WHEN proposal_status = 2 THEN disha_proposal_amt ELSE 0 END) as Close'),
                        DB::raw('SUM(CASE WHEN proposal_status = 3 THEN disha_proposal_amt ELSE 0 END) as Await')
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->when($workCenterId != 1 && $userType != 0, function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();

                    // Format data for Highcharts
                    $results = [];
                    foreach ($getDetails as $getDetail) {
                        $results[] = [
                            'name' => 'Open',
                            'y' => (int)$getDetail->Open,
                            'id'=> 1
                        ];
                        $results[] = [
                            'name' => 'Close',
                            'y' => (int)$getDetail->Close,
                            'id'=> 2
                        ];
                        $results[] = [
                            'name' => 'Await',
                            'y' => (int)$getDetail->Await,
                            'id'=> 3
                        ];
                    }
                return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Amount']);   
                }
            break;
            case 'schedule':
                if ($proposalData == 'proposal_count') {
                    $results = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->whereNotNull('proposals.proj_sector')
                        ->select(
                            DB::raw('COUNT(proposals.proj_sector) as y'),
                            'proposals.proj_sector as id',
                            DB::raw('(SELECT schedule_viis.schedule_name FROM schedule_viis WHERE schedule_viis.sc_viis_id = proposals.proj_sector LIMIT 1) as name')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.proj_sector')
                        ->orderBy(DB::raw("CAST(name AS SIGNED)"))
                        ->get();
                
                    $processedSchedules = [];
                    $finalResults = [];
                
                    foreach ($results as $item) {
                        $ids = explode(',', $item->id);
                        foreach ($ids as $id) {
                            $id = trim($id);
                            if (!isset($processedSchedules[$id])) {
                                $viisData = DB::table('schedule_viis')
                                    ->select('sc_viis_no', 'schedule_name')
                                    ->where('sc_viis_id', $id)
                                    ->first();
                
                                $sc_viis_no = $viisData->sc_viis_no ?? '';
                                $schedule_name = $viisData->schedule_name ?? '';
                
                                $processedSchedules[$id] = true;
                
                                $newItem = new \stdClass();
                                $newItem->y = $item->y;
                                $newItem->id = $id;
                                $newItem->name = $schedule_name . ' (Item-' . $sc_viis_no . ')';
                
                                $finalResults[] = $newItem;
                            } else {
                                // If already processed, increment the count
                                foreach ($finalResults as &$result) {
                                    if ($result->id == $id) {
                                        $result->y += $item->y;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                
                    // Sort by proj_sector ID
                    usort($finalResults, function ($a, $b) {
                        return $a->id - $b->id;
                    });
                
                    return response()->json([
                        'status' => 1,
                        'result' => $finalResults,
                        'labelName' => 'Total Proposal'
                    ]);
                }
                
                // elseif ($proposalData == 'proposal_amount') 
                // {
                //     $results = DB::table('proposals')
                //         ->where('proposals.final_save', 1)
                //         ->whereNotNull('proposals.fpr_project_cost')
                //         ->select(
                //             DB::raw('SUM(`proposals`.`fpr_project_cost`) as y'),
                //             'proposals.fpr_project_cost', 'proposals.proj_sector as id',
                //             DB::raw('(SELECT schedule_viis.sc_viis_no FROM schedule_viis WHERE schedule_viis.sc_viis_id = `proposals`.`proj_sector` LIMIT 1) as name')
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                //         })
                //         // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //         //     return $query->where('proposals.address_to', $workCenterId);
                //         // })
                //         ->groupBy('proposals.proj_sector')
                //         ->orderBy(DB::raw("CAST(name AS SIGNED)"))
                //         ->get();

                //         $processedSchedules = [];
                //         $finalResults = [];
                //         foreach ($results as $item) {
                //             $ids = explode(',', $item->id);
                //             foreach ($ids as $id) {
                //                 // Check if this schedule has already been processed
                //                 if (!isset($processedSchedules[$id])) {
                //                     $processedSchedules[$id] = true; 
                //                     $newItem = new \stdClass();
                //                     $newItem->y = $item->y;
                //                     $newItem->id = $id;
                //                     $newItem->name = 'Schedule ' . $id; 
                //                     $finalResults[] = $newItem;
                //                 } else {
                //                     // Update the count of existing schedule
                //                     foreach ($finalResults as &$result) {
                //                         if ($result->id == $id) {
                //                             $result->y += $item->y;
                //                             break;
                //                         }
                //                     }
                //                 }
                //             }
                //         }
    
                //         // Sort $finalResults by schedule IDs
                //         usort($finalResults, function ($a, $b) {
                //             return $a->id - $b->id;
                //         });
                //         return response()->json(['status' => 1, 'result' => $finalResults, 'labelName' => 'Total Amount']);
                // }
                elseif ($proposalData == 'proposal_amount') 
                {
                    $results = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->where('proposals.disha_verify_yn',1)
                        ->whereNotNull('proposals.disha_proposal_amt')
                        ->select(
                            DB::raw('SUM(`proposals`.`disha_proposal_amt`) as y'),
                            'proposals.proj_sector as id',
                            DB::raw('(
                                SELECT schedule_viis.schedule_name 
                                FROM schedule_viis 
                                WHERE schedule_viis.sc_viis_id = proposals.proj_sector 
                                LIMIT 1
                            ) as name')
                        )
                        
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.proj_sector')
                        ->orderBy(DB::raw("CAST(name AS SIGNED)"))
                        ->get();

                        $processedSchedules = [];
                        $finalResults = [];
                        foreach ($results as $item) {
                            $ids = explode(',', $item->id);
                            foreach ($ids as $id) {
                                // Check if this schedule has already been processed
                                if (!isset($processedSchedules[$id])) {
                                    $viisData = DB::table('schedule_viis')
                                    ->select('sc_viis_no', 'schedule_name')
                                    ->where('sc_viis_id', $id)
                                    ->first();

                                    $sc_viis_no = $viisData->sc_viis_no ?? '';
                                    $schedule_name = $viisData->schedule_name ?? '';

                                    $processedSchedules[$id] = true; 
                                    $newItem = new \stdClass();
                                    $newItem->y = $item->y;
                                    $newItem->id = $id;
                                     $newItem->name = $schedule_name . ' (Item-' . $sc_viis_no . ')';
                                    $finalResults[] = $newItem;
                                } else {
                                    // Update the count of existing schedule
                                    foreach ($finalResults as &$result) {
                                        if ($result->id == $id) {
                                            $result->y += $item->y;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
    
                        // Sort $finalResults by schedule IDs
                        usort($finalResults, function ($a, $b) {
                            return $a->id - $b->id;
                        });
                        return response()->json(['status' => 1, 'result' => $finalResults, 'labelName' => 'Total Amount']);
                }
            break;
            case 'address':
                if($proposalData == 'proposal_count')
                {
                    $results = DB::table('proposals')
                    ->where('proposals.final_save', 1)
                    ->select(
                        'proposals.address_to as id',
                        DB::raw("
                            CASE 
                                WHEN address_to != $workCenterId 
                                THEN REPLACE((SELECT addresstos.addressto FROM addresstos WHERE addresstos.adto_id = `proposals`.`address_to` LIMIT 1), 'Work Centre', '') 
                                ELSE '' 
                            END as name
                        "),
                        DB::raw('COUNT(`proposals`.`address_to`) as y')
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                    })
                    ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->groupBy('proposals.address_to')
                    ->get();

                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Address','yAxis' => 'Total Proposal']);
                }
                // elseif ($proposalData == 'proposal_amount') 
                // {
                //     $results = DB::table('proposals')
                //         ->where('final_save', 1)
                //         ->whereNotNull('proposals.fpr_project_cost')
                //         ->select(
                //             DB::raw('SUM(`proposals`.`fpr_project_cost`) as y'),
                //             'proposals.fpr_project_cost', 'proposals.address_to as id',
                //             DB::raw('(SELECT addresstos.addressto FROM addresstos WHERE addresstos.adto_id = `proposals`.`address_to` LIMIT 1) as name')
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                //         })
                //         // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //         //     return $query->where('proposals.address_to', $workCenterId);
                //         // })
                //         ->groupBy('proposals.address_to')
                //         ->orderBy(DB::raw("CAST(name AS SIGNED)"))
                //         ->get();

                //     return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Amount']);
                // }
                elseif ($proposalData == 'proposal_amount') 
                {
                    $results = DB::table('proposals')
                        ->where('final_save', 1)
                        ->where('proposals.disha_verify_yn',1)
                        ->whereNotNull('proposals.disha_proposal_amt')
                        ->select(
                            DB::raw('SUM(`proposals`.`disha_proposal_amt`) as y'),
                            'proposals.disha_proposal_amt', 'proposals.address_to as id',
                            DB::raw("
                            CASE 
                                WHEN address_to != $workCenterId 
                                THEN REPLACE((SELECT addresstos.addressto FROM addresstos WHERE addresstos.adto_id = `proposals`.`address_to` LIMIT 1), 'Work Centre', '') 
                                ELSE '' 
                            END as name
                        "),
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.address_to')
                        ->orderBy(DB::raw("CAST(name AS SIGNED)"))
                        ->get();

                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Amount']);
                }
            break;
            case 'gst_type':
                if($proposalData == 'proposal_count')
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->select(
                            DB::raw('SUM(CASE WHEN gst_type = "GS" THEN 1 ELSE 0 END) as gst'),
                            DB::raw('SUM(CASE WHEN gst_type = "NG" THEN 1 ELSE 0 END) as nonGst')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                        //     return $query->where('address_to', $workCenterId);
                        // })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'name' => 'GST',
                                'y' => (int)$getDetail->gst,
                                'id' => 'GS'
                            ];
                            $results[] = [
                                'name' => 'Non-GST',
                                'y' => (int)$getDetail->nonGst,
                                'id' => 'NG'
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Proposal']);
                }
                // elseif($proposalData == 'proposal_amount')
                // {
                //     $getDetails = DB::table('proposals')
                //         ->where('final_save', 1)
                //         ->select(
                //             DB::raw('SUM(CASE WHEN gst_type = "GS" THEN fpr_project_cost ELSE 0 END) as gst'),
                //             DB::raw('SUM(CASE WHEN gst_type = "NG" THEN fpr_project_cost ELSE 0 END) as nonGst'),
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('final_save_date', '<=', $endDate);
                //         })
                //         // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //         //     return $query->where('address_to', $workCenterId);
                //         // })
                //         ->get();

                //         // Format data for Highcharts
                //         $results = [];
                //         foreach ($getDetails as $getDetail) {
                //             $results[] = [
                //                 'name' => 'GST',
                //                 'y' => (int)$getDetail->gst,
                //             ];
                //             $results[] = [
                //                 'name' => 'Non-GST',
                //                 'y' => (int)$getDetail->nonGst,
                //             ];
                //         }
                //     return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Amount']);  
                // }
                elseif($proposalData == 'proposal_amount')
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->where('disha_verify_yn',1)
                        ->select(
                            DB::raw('SUM(CASE WHEN gst_type = "GS" THEN disha_proposal_amt ELSE 0 END) as gst'),
                            DB::raw('SUM(CASE WHEN gst_type = "NG" THEN disha_proposal_amt ELSE 0 END) as nonGst'),
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'name' => 'GST',
                                'y' => (int)$getDetail->gst,
                            ];
                            $results[] = [
                                'name' => 'Non-GST',
                                'y' => (int)$getDetail->nonGst,
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Amount']);  
                }
            break;
            case 'target_audience':
                if($proposalData == 'proposal_count')
                {
                    $getAudienceDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN women_no > 0 THEN 1 ELSE 0 END) as Women'),
                        DB::raw('SUM(CASE WHEN child_no > 0 THEN 1 ELSE 0 END) as Children'),
                        DB::raw('SUM(CASE WHEN sr_citizen_no > 0 THEN 1 ELSE 0 END) as SrCitizens'),
                        DB::raw('SUM(CASE WHEN handicap_no > 0 THEN 1 ELSE 0 END) as Handicap'),
                        DB::raw('SUM(CASE WHEN lgbtq_no > 0 THEN 1 ELSE 0 END) as Lgbtq'),
                        DB::raw('SUM(CASE WHEN other_no > 0 THEN 1 ELSE 0 END) as Others')
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();
            
                    // Format data for Highcharts
                    $results = [];
                    foreach ($getAudienceDetails as $audienceDetail) {
                        $results[] = [
                            'name' => 'Women',
                            'y'    => (int)$audienceDetail->Women,
                            'id'   => 'women_no'
                        ];
                        $results[] = [
                            'name' => 'Children',
                            'y'    => (int)$audienceDetail->Children,
                            'id'   => 'child_no'
                        ];
                        $results[] = [
                            'name' => 'Senior Citizens',
                            'y' => (int)$audienceDetail->SrCitizens,
                            'id'   => 'sr_citizen_no'
                        ];
                        $results[] = [
                            'name' => 'Handicap',
                            'y' => (int)$audienceDetail->Handicap,
                            'id'   => 'handicap_no'
                        ];
                        $results[] = [
                            'name' => 'LGBTQ',
                            'y' => (int)$audienceDetail->Lgbtq,
                            'id'   => 'lgbtq_no'
                        ];
                        $results[] = [
                            'name' => 'Others',
                            'y' => (int)$audienceDetail->Others,
                            'id'   => 'other_no'
                        ];
                    }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Proposal']);
                }
                // elseif($proposalData == 'proposal_amount')
                // {
                //     $getAudienceDetails = DB::table('proposals')
                //     ->where('final_save', 1)
                //     ->select(
                //         DB::raw('SUM(CASE WHEN women_no > 0 THEN women_amt ELSE 0 END) as Women'),
                //         DB::raw('SUM(CASE WHEN child_no > 0 THEN child_amt ELSE 0 END) as Children'),
                //         DB::raw('SUM(CASE WHEN sr_citizen_no > 0 THEN sr_citizen_amt ELSE 0 END) as SrCitizens'),
                //         DB::raw('SUM(CASE WHEN handicap_no > 0 THEN handicap_amt ELSE 0 END) as Handicap'),
                //         DB::raw('SUM(CASE WHEN lgbtq_no > 0 THEN lgbtq_amt ELSE 0 END) as Lgbtq'),
                //         DB::raw('SUM(CASE WHEN other_no > 0 THEN other_no ELSE 0 END) as Others')
                //     )
                //     ->when(!empty($startDate), function ($query) use ($startDate) {
                //         return $query->whereDate('final_save_date', '>=', $startDate);
                //     })
                //     ->when(!empty($endDate), function ($query) use ($endDate) {
                //         return $query->whereDate('final_save_date', '<=', $endDate);
                //     })
                //     // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //     //     return $query->where('address_to', $workCenterId);
                //     // })
                //     ->get();
                    
                //     // Format data for Highcharts
                //     $results = [];
                //     foreach ($getAudienceDetails as $audienceDetail) {
                //         $results[] = [
                //             'name' => 'Women',
                //             'y'    => (int)$audienceDetail->Women,
                //             'id'   => 'women_no'
                //         ];
                //         $results[] = [
                //             'name' => 'Children',
                //             'y'    => (int)$audienceDetail->Children,
                //             'id'   => 'child_no'
                //         ];
                //         $results[] = [
                //             'name' => 'Senior Citizens',
                //             'y' => (int)$audienceDetail->SrCitizens,
                //             'id'   => 'sr_citizen_no'
                //         ];
                //         $results[] = [
                //             'name' => 'Handicap',
                //             'y' => (int)$audienceDetail->Handicap,
                //             'id'   => 'handicap_no'
                //         ];
                //         $results[] = [
                //             'name' => 'LGBTQ',
                //             'y' => (int)$audienceDetail->Lgbtq,
                //             'id'   => 'lgbtq_no'
                //         ];
                //         $results[] = [
                //             'name' => 'Others',
                //             'y' => (int)$audienceDetail->Others,
                //             'id'   => 'other_no'
                //         ];
                //     }
                //     return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Amount']);
                // }
                elseif($proposalData == 'proposal_amount')
                {
                    $getAudienceDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->where('disha_verify_yn',1)
                    ->select(
                        DB::raw('SUM(CASE WHEN women_no > 0 THEN women_amt ELSE 0 END) as Women'),
                        DB::raw('SUM(CASE WHEN child_no > 0 THEN child_amt ELSE 0 END) as Children'),
                        DB::raw('SUM(CASE WHEN sr_citizen_no > 0 THEN sr_citizen_amt ELSE 0 END) as SrCitizens'),
                        DB::raw('SUM(CASE WHEN handicap_no > 0 THEN handicap_amt ELSE 0 END) as Handicap'),
                        DB::raw('SUM(CASE WHEN lgbtq_no > 0 THEN lgbtq_amt ELSE 0 END) as Lgbtq'),
                        DB::raw('SUM(CASE WHEN other_no > 0 THEN other_no ELSE 0 END) as Others')
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->when($workCenterId != 1 && $userType != 0, function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();
                    
                    // Format data for Highcharts
                    $results = [];
                    foreach ($getAudienceDetails as $audienceDetail) {
                        $results[] = [
                            'name' => 'Women',
                            'y'    => (int)$audienceDetail->Women,
                            'id'   => 'women_no'
                        ];
                        $results[] = [
                            'name' => 'Children',
                            'y'    => (int)$audienceDetail->Children,
                            'id'   => 'child_no'
                        ];
                        $results[] = [
                            'name' => 'Senior Citizens',
                            'y' => (int)$audienceDetail->SrCitizens,
                            'id'   => 'sr_citizen_no'
                        ];
                        $results[] = [
                            'name' => 'Handicap',
                            'y' => (int)$audienceDetail->Handicap,
                            'id'   => 'handicap_no'
                        ];
                        $results[] = [
                            'name' => 'LGBTQ',
                            'y' => (int)$audienceDetail->Lgbtq,
                            'id'   => 'lgbtq_no'
                        ];
                        $results[] = [
                            'name' => 'Others',
                            'y' => (int)$audienceDetail->Others,
                            'id'   => 'other_no'
                        ];
                    }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Amount']);
                }
            break;
            case 'community_beneficery':
                if($proposalData == 'proposal_count')
                {
                    $getBeneficeryDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN schedule_caste_number > 0 THEN 1 ELSE 0 END) as ScheduleCaste'),
                        DB::raw('SUM(CASE WHEN schedule_tribe_number > 0 THEN 1 ELSE 0 END) as ScheduleTribe'),
                        DB::raw('SUM(CASE WHEN obc_number > 0 THEN 1 ELSE 0 END) as Obc'),
                        DB::raw('SUM(CASE WHEN minority_number > 0 THEN 1 ELSE 0 END) as Minority'),
                        DB::raw('SUM(CASE WHEN general_number > 0 THEN 1 ELSE 0 END) as General'),
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('proposals.address_to', $workCenterId);
                    // })
                    ->get();

                    // Format data for Highcharts
                    $results = [];
                    foreach ($getBeneficeryDetails as $getBeneficeryDetail) {
                        $results[] = [
                            'name' => 'Schedule Caste',
                            'y'    => (int)$getBeneficeryDetail->ScheduleCaste,
                            'id'   => 'schedule_caste_number'
                        ];
                        $results[] = [
                            'name' => 'Schedule tribe',
                            'y'    => (int)$getBeneficeryDetail->ScheduleTribe,
                            'id'   => 'schedule_tribe_number'
                        ];
                        $results[] = [
                            'name' => 'Obc',
                            'y' => (int)$getBeneficeryDetail->Obc,
                            'id'   => 'obc_number'
                        ];
                        $results[] = [
                            'name' => 'Minority',
                            'y' => (int)$getBeneficeryDetail->Minority,
                            'id'   => 'minority_number'
                        ];
                        $results[] = [
                            'name' => 'General',
                            'y' => (int)$getBeneficeryDetail->General,
                            'id'   => 'general_number'
                        ];
                    }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Proposal']);
                }
                elseif($proposalData == 'proposal_amount')
                {
                    $getBeneficeryDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN schedule_caste_number > 0 THEN 1 ELSE 0 END) as ScheduleCaste'),
                        DB::raw('SUM(CASE WHEN schedule_tribe_number > 0 THEN 1 ELSE 0 END) as ScheduleTribe'),
                        DB::raw('SUM(CASE WHEN obc_number > 0 THEN obc_amount ELSE 0 END) as Obc'),
                        DB::raw('SUM(CASE WHEN minority_number > 0 THEN minority_amount ELSE 0 END) as Minority'),
                        DB::raw('SUM(CASE WHEN general_number > 0 THEN general_amount ELSE 0 END) as General'),
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();

                    // Format data for Highcharts
                    $results = [];
                    foreach ($getBeneficeryDetails as $getBeneficeryDetail) {
                        $results[] = [
                            'name' => 'Schedule Caste',
                            'y'    => (int)$getBeneficeryDetail->ScheduleCaste,
                            'id'   => 'schedule_caste_number'
                        ];
                        $results[] = [
                            'name' => 'Schedule tribe',
                            'y'    => (int)$getBeneficeryDetail->ScheduleTribe,
                            'id'   => 'schedule_tribe_number'
                        ];
                        $results[] = [
                            'name' => 'Obc',
                            'y' => (int)$getBeneficeryDetail->Obc,
                            'id'   => 'obc_number'
                        ];
                        $results[] = [
                            'name' => 'Minority',
                            'y' => (int)$getBeneficeryDetail->Minority,
                            'id'   => 'minority_number'
                        ];
                        $results[] = [
                            'name' => 'General',
                            'y' => (int)$getBeneficeryDetail->General,
                            'id'   => 'general_number'
                        ];
                    }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Total Amount']);
                }
            break;
            default:
                return response()->json(['status' => 2, 'result' => "Invalid request.", 'labelName' => 'No data found.']);
            break;
        }
    }

    /**
        * This function all display data of Line chart
    */
    public function lineGraph(Request $request)
    {
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE'");

        $stateData = @$request->state_pin;
        $proposalData = @$request->proposal_data;
        $startDate = @$request->start_date;
        $endDate = @$request->end_date;

        $user         = @AuthUser();
        $workCenterId = @$user->work_station;
        $userType     = @$user->user_type;
        // $workCenterId = @$request->workCenterId;

        $proposalsDate = DB::table('proposals')
            ->select('prop_id', 'final_save_date')
            ->when(!empty($startDate), function ($query) use ($startDate) {
                return $query->whereDate('final_save_date', '>=', $startDate);
            })
            ->when(!empty($endDate), function ($query) use ($endDate) {
                return $query->whereDate('final_save_date', '<=', $endDate);
            })
            ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                return $query->where('address_to', $workCenterId);
            })
            ->get();

        switch ($stateData) {
            case 'state':
                if ($proposalData == 'proposal_count') 
                {
                    if ($proposalData == 'proposal_count') {
                        $results = DB::table('proposals_pincodes')
                        ->select(
                            DB::raw('COUNT(DISTINCT `proposals_pincodes`.`propp_id`) as y'),
                            'proposals_pincodes.state_cd as id',
                            DB::raw('(SELECT pincode.state_name FROM pincode WHERE pincode.state_code = `proposals_pincodes`.`state_cd` LIMIT 1) as data')
                        )
                        ->when(!empty($startDate) || !empty($endDate) , function ($query) use ($proposalsDate) {
                            return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                        })
                        ->groupBy('proposals_pincodes.state_cd')
                        ->get();
                        return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'State','yAxis' => 'Total Proposal']);
                    }
                }elseif ($proposalData == 'proposal_amount'){
                    $results = DB::table('proposals_pincodes')
                    ->select(
                        DB::raw('SUM(proposals.disha_proposal_amt) as y'),
                        'proposals_pincodes.state_cd as id',
                        DB::raw('(SELECT pincode.state_name FROM pincode WHERE pincode.state_code = `proposals_pincodes`.`state_cd` LIMIT 1) as data')
                    )
                    ->join('proposals', 'proposals.prop_id', '=', 'proposals_pincodes.propp_id')
                    ->when(!empty($startDate) || !empty($endDate) ||  !empty($workCenterId)  , function ($query) use ($proposalsDate) {
                        return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                    })
                    ->where('proposals.final_save', 1)
                    ->where('proposals.disha_verify_yn', 1)
                    ->groupBy('proposals_pincodes.state_cd')
                    ->get();
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'State', 'yAxis' => 'Approved Proposal']);
                }
            break;
            case 'city':
                if($proposalData == 'proposal_count')
                {
                    $results = DB::table('proposals_pincodes')
                    ->select(
                        DB::raw('COUNT(DISTINCT `proposals_pincodes`.`propp_id`) as y'),
                        'proposals_pincodes.district_cd as id',
                        DB::raw('(SELECT pincode.district_name FROM pincode WHERE pincode.district_code = `proposals_pincodes`.`district_cd` LIMIT 1) as data')
                    )
                    ->when(!empty($startDate) || !empty($endDate) , function ($query) use ($proposalsDate) {
                        return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                    })
                    ->whereNotNull('proposals_pincodes.district_cd')
                    ->where('proposals_pincodes.district_cd', '<>', '')
                    ->groupBy('proposals_pincodes.district_cd')
                    ->get();

                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'City', 'yAxis' => 'Total Proposal']);
                }
            break;
            case 'status':
                if($proposalData == 'proposal_count')
                {
                    $getDetails = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->where('proposals.disha_verify_yn', 1)
                        ->select(
                            DB::raw('SUM(CASE WHEN proposal_status = 1 THEN 1 ELSE 0 END) as Open'),
                            DB::raw('SUM(CASE WHEN proposal_status = 2 THEN 1 ELSE 0 END) as Close'),
                            DB::raw('SUM(CASE WHEN proposal_status = 3 THEN 1 ELSE 0 END) as Await')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'name' => 'Open',
                                'y' => (int)$getDetail->Open,
                                'id' => 1
                            ];
                            $results[] = [
                                'name' => 'Close',
                                'y' => (int)$getDetail->Close,
                                'id' => 2
                            ];
                            $results[] = [
                                'name' => 'Await',
                                'y' => (int)$getDetail->Await,
                                'id' => 3
                            ];

                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Status','yAxis' => 'Total Proposal']);
                }
                // elseif ($proposalData == 'proposal_amount') 
                // {
                //     $getDetails = DB::table('proposals')
                //         ->where('final_save', 1)
                //         ->select(
                //             DB::raw('SUM(CASE WHEN proposal_status = 1 THEN fpr_project_cost ELSE 0 END) as Open'),
                //             DB::raw('SUM(CASE WHEN proposal_status = 2 THEN fpr_project_cost ELSE 0 END) as Close'),
                //             DB::raw('SUM(CASE WHEN proposal_status = 3 THEN fpr_project_cost ELSE 0 END) as Await')
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('final_save_date', '<=', $endDate);
                //         })
                //         // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //         //     return $query->where('address_to', $workCenterId);
                //         // })
                //         ->get();

                //         // Format data for Highcharts
                //         $results = [];
                //         foreach ($getDetails as $getDetail) {
                //             $results[] = [
                //                 'name' => 'Open',
                //                 'y' => (int)$getDetail->Open,
                //                 'id' => 1
                //             ];
                //             $results[] = [
                //                 'name' => 'Close',
                //                 'y' => (int)$getDetail->Close,
                //                 'id' => 2
                //             ];
                //             $results[] = [
                //                 'name' => 'Await',
                //                 'y' => (int)$getDetail->Await,
                //                 'id' => 3
                //             ];
                //         }
                //     return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Status','yAxis' => 'Total Amount']);   
                // }
                elseif ($proposalData == 'proposal_amount') 
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->where('disha_verify_yn',1)
                        ->select(
                            DB::raw('SUM(CASE WHEN proposal_status = 1 THEN disha_proposal_amt ELSE 0 END) as Open'),
                            DB::raw('SUM(CASE WHEN proposal_status = 2 THEN disha_proposal_amt ELSE 0 END) as Close'),
                            DB::raw('SUM(CASE WHEN proposal_status = 3 THEN disha_proposal_amt ELSE 0 END) as Await')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'name' => 'Open',
                                'y' => (int)$getDetail->Open,
                                'id' => 1
                            ];
                            $results[] = [
                                'name' => 'Close',
                                'y' => (int)$getDetail->Close,
                                'id' => 2
                            ];
                            $results[] = [
                                'name' => 'Await',
                                'y' => (int)$getDetail->Await,
                                'id' => 3
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Status','yAxis' => 'Total Amount']);   
                }
            break;
            case 'schedule':
                if ($proposalData == 'proposal_count') {
                    $results = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->whereNotNull('proposals.proj_sector')
                        ->select(
                            DB::raw('COUNT(`proposals`.`proj_sector`) as y'),
                            'proposals.proj_sector as id',
                            DB::raw('(SELECT schedule_viis.sc_viis_no FROM schedule_viis WHERE schedule_viis.sc_viis_id = `proposals`.`proj_sector` LIMIT 1) as name')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.proj_sector')
                        ->orderBy(DB::raw("CAST(name AS SIGNED)"))
                        ->get();
                                              
                    $processedSchedules = [];
                    $finalResults = [];
                    foreach ($results as $item) {
                        $ids = explode(',', $item->id);
                        foreach ($ids as $id) {
                            if (!isset($processedSchedules[$id])) {
                                $visName = DB::table('schedule_viis')->select('sc_viis_no')->where('sc_viis_id', $id)->first();
                                $viis_name = $visName->sc_viis_no;

                                $processedSchedules[$id] = true;
                                $newItem = new \stdClass();
                                $newItem->y = $item->y;
                                $newItem->id = $id;
                                $newItem->data = 'Item ' . $viis_name;
                                $finalResults[] = $newItem;
                            } else {
                                // Update the count of existing schedule
                                foreach ($finalResults as &$result) {
                                    if ($result->id == $id) {
                                        $result->y += $item->y;
                                        break;
                                    }
                                }
                            }
                            
                        }
                    }

                    // Sort $finalResults by schedule IDs
                    usort($finalResults, function ($a, $b) {
                        return $a->id - $b->id;
                    });
                    
                    return response()->json(['status' => 1, 'result' => $finalResults, 'labelName' => 'Schedule', 'yAxis' => 'Total Proposal']);
                }
                // elseif ($proposalData == 'proposal_amount') 
                // {
                //     $results = DB::table('proposals')
                //         ->where('proposals.final_save', 1)
                //         ->whereNotNull('proposals.fpr_project_cost')
                //         ->select(
                //             DB::raw('SUM(`proposals`.`fpr_project_cost`) as y'),
                //             'proposals.fpr_project_cost', 'proposals.proj_sector as id',
                //             DB::raw('(SELECT schedule_viis.sc_viis_no FROM schedule_viis WHERE schedule_viis.sc_viis_id = `proposals`.`proj_sector` LIMIT 1) as data')
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                //         })
                //         // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //         //     return $query->where('proposals.address_to', $workCenterId);
                //         // })
                //         ->groupBy('proposals.proj_sector')
                //         ->orderBy(DB::raw("CAST(data AS SIGNED)"))
                //         ->get();

                //         $processedSchedules = [];
                //         $finalResults = [];
                //         foreach ($results as $item) {
                //             $ids = explode(',', $item->id);
                //             foreach ($ids as $id) {
                //                 if (!isset($processedSchedules[$id])) {
                //                     $processedSchedules[$id] = true;
                //                     $newItem = new \stdClass();
                //                     $newItem->y = $item->y;
                //                     $newItem->id = $id;
                //                     $finalResults[] = $newItem;
                //                 } else {
                //                     // Update the count of existing schedule
                //                     foreach ($finalResults as &$result) {
                //                         if ($result->id == $id) {
                //                             $result->y += $item->y;
                //                             break;
                //                         }
                //                     }
                //                 }
                //             }
                //         }
                 
                //         // Modify label names
                //         foreach ($finalResults as &$result) {
                //             $result->data = 'Schedule ' . $result->id;
                //         }
    
                //         // Sort $finalResults by schedule IDs
                //         usort($finalResults, function ($a, $b) {
                //             return $a->id - $b->id;
                //         });
                        
                //         return response()->json(['status' => 1, 'result' => $finalResults, 'labelName' => 'Schedule', 'yAxis' => 'Total Amount']);
                // }
                elseif ($proposalData == 'proposal_amount') 
                {
                    $results = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->where('proposals.disha_verify_yn', 1)
                        ->whereNotNull('proposals.disha_proposal_amt')
                        ->select(
                            DB::raw('SUM(`proposals`.`disha_proposal_amt`) as y'),
                            'proposals.disha_proposal_amt', 'proposals.proj_sector as id',
                            DB::raw('(SELECT schedule_viis.sc_viis_no FROM schedule_viis WHERE schedule_viis.sc_viis_id = `proposals`.`proj_sector` LIMIT 1) as data')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.proj_sector')
                        ->orderBy(DB::raw("CAST(data AS SIGNED)"))
                        ->get();

                        $processedSchedules = [];
                        $finalResults = [];
                        foreach ($results as $item) {
                            $ids = explode(',', $item->id);
                            foreach ($ids as $id) {
                                if (!isset($processedSchedules[$id])) {
                                    $processedSchedules[$id] = true;
                                    $newItem = new \stdClass();
                                    $newItem->y = $item->y;
                                    $newItem->id = $id;
                                    $finalResults[] = $newItem;
                                } else {
                                    // Update the count of existing schedule
                                    foreach ($finalResults as &$result) {
                                        if ($result->id == $id) {
                                            $result->y += $item->y;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                 
                        // Modify label names
                        foreach ($finalResults as &$result) {
                            $result->data = 'Item ' . $result->id;
                        }
    
                        // Sort $finalResults by schedule IDs
                        usort($finalResults, function ($a, $b) {
                            return $a->id - $b->id;
                        });
                        
                        return response()->json(['status' => 1, 'result' => $finalResults, 'labelName' => 'Schedule', 'yAxis' => 'Total Amount']);
                }
            break;
            case 'address':
                if($proposalData == 'proposal_count')
                {
                    $results = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->select(
                            'proposals.address_to as id',
                            DB::raw('(SELECT addresstos.addressto FROM addresstos WHERE addresstos.adto_id = `proposals`.`address_to` LIMIT 1) as data'),
                            DB::raw('COUNT(`proposals`.`address_to`) as y')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.address_to')
                        ->get();
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Address','yAxis' => 'Total Proposal']);
                }
                // elseif ($proposalData == 'proposal_amount') 
                // {
                //     $results = DB::table('proposals')
                //         ->where('proposals.final_save', 1)
                //         ->whereNotNull('proposals.fpr_project_cost')
                //         ->select(
                //             DB::raw('SUM(`proposals`.`fpr_project_cost`) as y'),
                //             'proposals.fpr_project_cost', 'proposals.address_to as id',
                //             DB::raw('(SELECT addresstos.addressto FROM addresstos WHERE addresstos.adto_id = `proposals`.`address_to` LIMIT 1) as data')
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                //         })
                //         ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //             return $query->where('proposals.address_to', $workCenterId);
                //         })
                //         ->groupBy('proposals.address_to')
                //         ->orderBy(DB::raw("CAST(data AS SIGNED)"))
                //         ->get();

                //     return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Address','yAxis' => 'Total Amount']);
                // }
                elseif ($proposalData == 'proposal_amount') 
                {
                    $results = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->where('proposals.disha_verify_yn', 1)
                        ->whereNotNull('proposals.disha_proposal_amt')
                        ->select(
                            DB::raw('SUM(`proposals`.`disha_proposal_amt`) as y'),
                            'proposals.disha_proposal_amt', 'proposals.address_to as id',
                            DB::raw('(SELECT addresstos.addressto FROM addresstos WHERE addresstos.adto_id = `proposals`.`address_to` LIMIT 1) as data')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.address_to')
                        ->orderBy(DB::raw("CAST(data AS SIGNED)"))
                        ->get();

                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Address','yAxis' => 'Total Amount']);
                }
            break;
            case 'gst_type':
                if($proposalData == 'proposal_count')
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->select(
                            DB::raw('SUM(CASE WHEN gst_type = "GS" THEN 1 ELSE 0 END) as gst'),
                            DB::raw('SUM(CASE WHEN gst_type = "NG" THEN 1 ELSE 0 END) as nonGst')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                        //     return $query->where('address_to', $workCenterId);
                        // })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'data' => 'GST',
                                'y' => (int)$getDetail->gst,
                                'id' => 'GS'
                            ];
                            $results[] = [
                                'data' => 'Non-GST',
                                'y' => (int)$getDetail->nonGst,
                                'id' => 'NG'
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Gst type','yAxis' => 'Total Proposal']);
                }
                // elseif($proposalData == 'proposal_amount')
                // {
                //     $getDetails = DB::table('proposals')
                //         ->where('final_save', 1)
                //         ->select(
                //             DB::raw('SUM(CASE WHEN gst_type = "GS" THEN fpr_project_cost ELSE 0 END) as gst'),
                //             DB::raw('SUM(CASE WHEN gst_type = "NG" THEN fpr_project_cost ELSE 0 END) as nonGst'),
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('final_save_date', '<=', $endDate);
                //         })
                //         // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //         //     return $query->where('address_to', $workCenterId);
                //         // })
                //         ->get();

                //         // Format data for Highcharts
                //         $results = [];
                //         foreach ($getDetails as $getDetail) {
                //             $results[] = [
                //                 'data' => 'GST',
                //                 'y' => (int)$getDetail->gst,
                //                 'id' => 'GS'
                //             ];
                //             $results[] = [
                //                 'data' => 'Non-GST',
                //                 'y' => (int)$getDetail->nonGst,
                //                 'id' => 'NG'
                //             ];
                //         }
                //     return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Gst type','yAxis' => 'Total Amount']);  
                // }
                elseif($proposalData == 'proposal_amount')
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->where('disha_verify_yn',1)
                        ->select(
                            DB::raw('SUM(CASE WHEN gst_type = "GS" THEN disha_proposal_amt ELSE 0 END) as gst'),
                            DB::raw('SUM(CASE WHEN gst_type = "NG" THEN disha_proposal_amt ELSE 0 END) as nonGst'),
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId != 1 && is_null($userType), function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'data' => 'GST',
                                'y' => (int)$getDetail->gst,
                                'id' => 'GS'
                            ];
                            $results[] = [
                                'data' => 'Non-GST',
                                'y' => (int)$getDetail->nonGst,
                                'id' => 'NG'
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Gst type','yAxis' => 'Total Amount']);  
                }
            break;
            case 'target_audience':
                if($proposalData == 'proposal_count')
                {
                    $getAudienceDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN women_no > 0 THEN 1 ELSE 0 END) as Women'),
                        DB::raw('SUM(CASE WHEN child_no > 0 THEN 1 ELSE 0 END) as Children'),
                        DB::raw('SUM(CASE WHEN sr_citizen_no > 0 THEN 1 ELSE 0 END) as SrCitizens'),
                        DB::raw('SUM(CASE WHEN handicap_no > 0 THEN 1 ELSE 0 END) as Handicap'),
                        DB::raw('SUM(CASE WHEN lgbtq_no > 0 THEN 1 ELSE 0 END) as Lgbtq'),
                        DB::raw('SUM(CASE WHEN other_no > 0 THEN 1 ELSE 0 END) as Others')
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();

                    // Format data for Highcharts
                    $results = [];
                    foreach ($getAudienceDetails as $audienceDetail) {
                        $results[] = [
                            'data' => 'Women',
                            'y'    => (int)$audienceDetail->Women,
                            'id'   => 'women_no'
                        ];
                        $results[] = [
                            'data' => 'Children',
                            'y'    => (int)$audienceDetail->Children,
                            'id'   => 'child_no'
                        ];
                        $results[] = [
                            'data' => 'Senior Citizens',
                            'y' => (int)$audienceDetail->SrCitizens,
                            'id'   => 'sr_citizen_no'
                        ];
                        $results[] = [
                            'data' => 'Handicap',
                            'y' => (int)$audienceDetail->Handicap,
                            'id'   => 'handicap_no'
                        ];
                        $results[] = [
                            'data' => 'LGBTQ',
                            'y' => (int)$audienceDetail->Lgbtq,
                            'id'   => 'lgbtq_no'
                        ];
                        $results[] = [
                            'data' => 'Others',
                            'y' => (int)$audienceDetail->Others,
                            'id'   => 'other_no'
                        ];
                    }
                  
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Focused/Target', 'yAxis' => 'Total Proposal']);
                }
                elseif($proposalData == 'proposal_amount')
                {
                    $getAudienceDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN women_no > 0 THEN women_amt ELSE 0 END) as Women'),
                        DB::raw('SUM(CASE WHEN child_no > 0 THEN child_amt ELSE 0 END) as Children'),
                        DB::raw('SUM(CASE WHEN sr_citizen_no > 0 THEN sr_citizen_amt ELSE 0 END) as SrCitizens'),
                        DB::raw('SUM(CASE WHEN handicap_no > 0 THEN handicap_amt ELSE 0 END) as Handicap'),
                        DB::raw('SUM(CASE WHEN lgbtq_no > 0 THEN lgbtq_amt ELSE 0 END) as Lgbtq'),
                        DB::raw('SUM(CASE WHEN other_no > 0 THEN other_no ELSE 0 END) as Others')
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();
                    
                    // Format data for Highcharts
                    $results = [];
                    foreach ($getAudienceDetails as $audienceDetail) {
                        $results[] = [
                            'data' => 'Women',
                            'y'    => (int)$audienceDetail->Women,
                            'id'   => 'women_no'
                        ];
                        $results[] = [
                            'data' => 'Children',
                            'y'    => (int)$audienceDetail->Children,
                            'id'   => 'child_no'
                        ];
                        $results[] = [
                            'data' => 'Senior Citizens',
                            'y' => (int)$audienceDetail->SrCitizens,
                            'id'   => 'sr_citizen_no'
                        ];
                        $results[] = [
                            'data' => 'Handicap',
                            'y' => (int)$audienceDetail->Handicap,
                            'id'   => 'handicap_no'
                        ];
                        $results[] = [
                            'data' => 'LGBTQ',
                            'y' => (int)$audienceDetail->Lgbtq,
                            'id'   => 'lgbtq_no'
                        ];
                        $results[] = [
                            'data' => 'Others',
                            'y' => (int)$audienceDetail->Others,
                            'id'   => 'other_no'
                        ];
                    }
                   
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Focused/Target', 'yAxis' => 'Total Amount']);
                }
            break;
            case 'community_beneficery':
                if($proposalData == 'proposal_count')
                {
                    $getBeneficeryDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN schedule_caste_number > 0 THEN 1 ELSE 0 END) as ScheduleCaste'),
                        DB::raw('SUM(CASE WHEN schedule_tribe_number > 0 THEN 1 ELSE 0 END) as ScheduleTribe'),
                        DB::raw('SUM(CASE WHEN obc_number > 0 THEN 1 ELSE 0 END) as Obc'),
                        DB::raw('SUM(CASE WHEN minority_number > 0 THEN 1 ELSE 0 END) as Minority'),
                        DB::raw('SUM(CASE WHEN general_number > 0 THEN 1 ELSE 0 END) as General'),
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();

                    // Format data for Highcharts
                    $results = [];
                    foreach ($getBeneficeryDetails as $getBeneficeryDetail) {
                        $results[] = [
                            'data' => 'Schedule Caste',
                            'y'    => (int)$getBeneficeryDetail->ScheduleCaste,
                            'id'   => 'schedule_caste_number'
                        ];
                        $results[] = [
                            'data' => 'Schedule tribe',
                            'y'    => (int)$getBeneficeryDetail->ScheduleTribe,
                            'id'   => 'schedule_tribe_number'
                        ];
                        $results[] = [
                            'data' => 'Obc',
                            'y' => (int)$getBeneficeryDetail->Obc,
                            'id'   => 'obc_number'
                        ];
                        $results[] = [
                            'data' => 'Minority',
                            'y' => (int)$getBeneficeryDetail->Minority,
                            'id'   => 'minority_number'
                        ];
                        $results[] = [
                            'data' => 'General',
                            'y' => (int)$getBeneficeryDetail->General,
                            'id'   => 'general_number'
                        ];
                    }
                    
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Community of beneficery', 'yAxis' => 'Total Proposal']);
                }
                elseif($proposalData == 'proposal_amount')
                {
                    $getBeneficeryDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN schedule_caste_number > 0 THEN 1 ELSE 0 END) as ScheduleCaste'),
                        DB::raw('SUM(CASE WHEN schedule_tribe_number > 0 THEN 1 ELSE 0 END) as ScheduleTribe'),
                        DB::raw('SUM(CASE WHEN obc_number > 0 THEN obc_amount ELSE 0 END) as Obc'),
                        DB::raw('SUM(CASE WHEN minority_number > 0 THEN minority_amount ELSE 0 END) as Minority'),
                        DB::raw('SUM(CASE WHEN general_number > 0 THEN general_amount ELSE 0 END) as General'),
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();

                    // Format data for Highcharts
                    $results = [];
                    foreach ($getBeneficeryDetails as $getBeneficeryDetail) {
                        $results[] = [
                            'data' => 'Schedule Caste',
                            'y'    => (int)$getBeneficeryDetail->ScheduleCaste,
                            'id'   => 'schedule_caste_number'
                        ];
                        $results[] = [
                            'data' => 'Schedule tribe',
                            'y'    => (int)$getBeneficeryDetail->ScheduleTribe,
                            'id'   => 'schedule_tribe_number'
                        ];
                        $results[] = [
                            'data' => 'Obc',
                            'y' => (int)$getBeneficeryDetail->Obc,
                            'id'   => 'obc_number'
                        ];
                        $results[] = [
                            'data' => 'Minority',
                            'y' => (int)$getBeneficeryDetail->Minority,
                            'id'   => 'minority_number'
                        ];
                        $results[] = [
                            'data' => 'General',
                            'y' => (int)$getBeneficeryDetail->General,
                            'id'   => 'general_number'
                        ];
                    }
                    
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Community of beneficery', 'yAxis' => 'Total Amount']);
                }
            break;
            default:
                return response()->json(['status' => 2, 'result' => "Invalid request.", 'labelName' => 'No data found.']);
            break;
        }
    }

    /**
        * This function display all data of Line chart
    */
    public function barGraph(Request $request)
    {
        DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE'");

        $stateData = @$request->state_pin;
        $proposalData = @$request->proposal_data;
        $startDate = @$request->start_date;
        $endDate = @$request->end_date;

        $user         = @AuthUser();
        $workCenterId = @$user->work_station;
        $userType     = @$user->user_type;

        // $workCenterId = @$request->workCenterId;
    
        $proposalsDate = DB::table('proposals')
        ->when(!empty($startDate), function ($query) use ($startDate) {
            return $query->whereDate('final_save_date', '>=', $startDate);
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            return $query->whereDate('final_save_date', '<=', $endDate);
        })
        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
            return $query->where('address_to', $workCenterId);
        })
        ->where('proposals.final_save', 1);
        $proposalsAmt = clone $proposalsDate;
        $allAmt = 0;
        if($proposalData == 'proposal_amount'){
            $allAmt = $proposalsAmt->sum('disha_proposal_amt');
            //$allAmt = round($allAmt / 10000000, 2);
        }

        $proposalsDate = $proposalsDate->select('prop_id', 'final_save_date')->get();

        switch ($stateData) {
            case 'state':
                if ($proposalData == 'proposal_count') 
                {
                    $results = DB::table('proposals_pincodes')
                    ->select(
                        DB::raw('COUNT(DISTINCT `proposals_pincodes`.`propp_id`) as y'),
                        'proposals_pincodes.state_cd as id',
                        DB::raw('(SELECT pincode.state_name FROM pincode WHERE pincode.state_code = `proposals_pincodes`.`state_cd` LIMIT 1) as data')
                    )
                    ->when(!empty($startDate) || !empty($endDate) , function ($query) use ($proposalsDate) {
                        return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                    })
                    ->groupBy('proposals_pincodes.state_cd')
                    ->get();

                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'State','yAxis' => 'Total Proposal']);
                }
                elseif ($proposalData == 'proposal_amount'){
                    $results = DB::table('proposals_pincodes')
                            ->select(
                                DB::raw('0 as per'),
                                DB::raw('0 as y'),
                                'proposals_pincodes.state_cd as id',
                                DB::raw('(SELECT pincode.state_name FROM pincode WHERE pincode.state_code = `proposals_pincodes`.`state_cd` LIMIT 1) as data')
                            )
                            ->when(!empty($startDate) || !empty($endDate) ||  !empty($workCenterId)  , function ($query) use ($proposalsDate) {
                                //return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                            })
                            ->whereIn('propp_id', $proposalsDate->pluck('prop_id'))
                            ->groupBy('proposals_pincodes.state_cd')
                            ->get();
                    foreach ($results as $key => $roww) {
                        $stateId = $roww->id;
                        $sum = DB::table('proposals')->where('disha_verify_yn', 1)
                                ->whereIn('prop_id', function($query) use($stateId) {
                                    $query->select('propp_id')
                                        ->from('proposals_pincodes')
                                        ->where('state_cd', $stateId);
                                })
                                ->sum('disha_proposal_amt');
                        if($sum > 1000){
                            $per = sprintf('%0.2f', (($sum * 100) / $allAmt)).'%';
                        }else{
                            $per = 0;
                        }
                        $sum = round($sum / 10000000, 2);
                        
                        $roww->y = $sum;
                        $roww->per = $per;
                    }
                    /*$results = DB::table('proposals_pincodes')
                    ->select(
                        DB::raw('(
                            SELECT SUM(proposals.disha_proposal_amt) 
                            FROM proposals 
                            WHERE proposals.prop_id IN (
                                SELECT GROUP_CONCAT(proposals_pincodes.propp_id)
                            )
                        ) as y'),
                        'proposals_pincodes.state_cd as id',
                        DB::raw('(SELECT pincode.state_name FROM pincode WHERE pincode.state_code = `proposals_pincodes`.`state_cd` LIMIT 1) as name')
                    )
                    ->join('proposals', 'proposals.prop_id', '=', 'proposals_pincodes.propp_id')
                    ->when(!empty($startDate) || !empty($endDate) ||  !empty($workCenterId)  , function ($query) use ($proposalsDate) {
                        return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                    })
                    ->where('proposals.final_save', 1)
                    ->where('proposals.disha_verify_yn', 1)
                    ->groupBy('proposals_pincodes.state_cd')
                    ->get();*/
                    $results = $this->stateAmountGraph($startDate, $endDate, $workCenterId);
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'State', 'yAxis' => 'Proposal Amt (In Cr)']);
                }
            break;
            case 'city':
                if($proposalData == 'proposal_count')
                {
                    $results = DB::table('proposals_pincodes')
                    ->select(
                        DB::raw('COUNT(DISTINCT `proposals_pincodes`.`propp_id`) as y'),
                        'proposals_pincodes.district_cd as id',
                        DB::raw('(SELECT pincode.district_name FROM pincode WHERE pincode.district_code = `proposals_pincodes`.`district_cd` LIMIT 1) as data')
                    )
                    ->when(!empty($startDate) || !empty($endDate) , function ($query) use ($proposalsDate) {
                        return $query->whereIn('propp_id', $proposalsDate->pluck('prop_id'));
                    })
                    ->whereNotNull('proposals_pincodes.district_cd')
                    ->where('proposals_pincodes.district_cd', '<>', '')
                    ->groupBy('proposals_pincodes.district_cd')
                    ->get();

                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'City','yAxis' => 'Total Proposal']);
                }
            break;
            case 'status':
                if($proposalData == 'proposal_count')
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->select(
                            DB::raw('SUM(CASE WHEN proposal_status = 1 THEN 1 ELSE 0 END) as Open'),
                            DB::raw('SUM(CASE WHEN proposal_status = 2 THEN 1 ELSE 0 END) as Close'),
                            DB::raw('SUM(CASE WHEN proposal_status = 3 THEN 1 ELSE 0 END) as Await')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'name' => 'Open',
                                'y' => (int)$getDetail->Open,
                                'id' => 1
                            ];
                            $results[] = [
                                'name' => 'Close',
                                'y' => (int)$getDetail->Close,
                                'id' => 2
                            ];
                            $results[] = [
                                'name' => 'Await',
                                'y' => (int)$getDetail->Await,
                                'id' => 3
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Status','yAxis' => 'Total Proposal']);
                }
                // elseif ($proposalData == 'proposal_amount') 
                // {
                //     $getDetails = DB::table('proposals')
                //         ->where('final_save', 1)
                //         ->select(
                //             DB::raw('SUM(CASE WHEN proposal_status = 1 THEN fpr_project_cost ELSE 0 END) as Open'),
                //             DB::raw('SUM(CASE WHEN proposal_status = 2 THEN fpr_project_cost ELSE 0 END) as Close'),
                //             DB::raw('SUM(CASE WHEN proposal_status = 3 THEN fpr_project_cost ELSE 0 END) as Await')
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('final_save_date', '<=', $endDate);
                //         })
                //         // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //         //     return $query->where('address_to', $workCenterId);
                //         // })
                //         ->get();

                //         // Format data for Highcharts
                //         $results = [];
                //         foreach ($getDetails as $getDetail) {
                //             $results[] = [
                //                 'name' => 'Open',
                //                 'y' => (int)$getDetail->Open,
                //                 'id' => 1
                //             ];
                //             $results[] = [
                //                 'name' => 'Close',
                //                 'y' => (int)$getDetail->Close,
                //                 'id' => 2
                //             ];
                //             $results[] = [
                //                 'name' => 'Await',
                //                 'y' => (int)$getDetail->Await,
                //                 'id' => 3
                //             ];
                //         }
                //     return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Status','yAxis' => 'Total Amount']);   
                // }
                elseif ($proposalData == 'proposal_amount') 
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->where('proposals.disha_verify_yn', 1)
                        ->select(
                            DB::raw('SUM(CASE WHEN proposal_status = 1 THEN disha_proposal_amt ELSE 0 END) as Open'),
                            DB::raw('SUM(CASE WHEN proposal_status = 2 THEN disha_proposal_amt ELSE 0 END) as Close'),
                            DB::raw('SUM(CASE WHEN proposal_status = 3 THEN disha_proposal_amt ELSE 0 END) as Await')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'name' => 'Open',
                                'y' => (int)$getDetail->Open,
                                'id' => 1
                            ];
                            $results[] = [
                                'name' => 'Close',
                                'y' => (int)$getDetail->Close,
                                'id' => 2
                            ];
                            $results[] = [
                                'name' => 'Await',
                                'y' => (int)$getDetail->Await,
                                'id' => 3
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Status','yAxis' => 'Total Amount']);   
                }
            break;
            case 'schedule':
                if ($proposalData == 'proposal_count') {
                    $results = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->whereNotNull('proposals.proj_sector')
                        ->select(
                            DB::raw('COUNT(`proposals`.`proj_sector`) as y'),
                            'proposals.proj_sector as id',
                            DB::raw('(SELECT schedule_viis.sc_viis_no FROM schedule_viis WHERE schedule_viis.sc_viis_id = `proposals`.`proj_sector` LIMIT 1) as name')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.proj_sector')
                        ->orderBy(DB::raw("CAST(name AS SIGNED)"))
                        ->get();
                                              
                        $processedSchedules = [];
                        $finalResults = [];
                        foreach ($results as $item) {
                            $ids = explode(',', $item->id);
                            foreach ($ids as $id) {
                                if (!isset($processedSchedules[$id])) {
                                    $visName = DB::table('schedule_viis')->select('sc_viis_no')->where('sc_viis_id', $id)->first();
                                    $viis_name = $visName->sc_viis_no;

                                    $processedSchedules[$id] = true;
                                    $newItem = new \stdClass();
                                    $newItem->y = $item->y;
                                    $newItem->id = $id;
                                    //$newItem->name = 'Schedule ' . $viis_name;
                                    $newItem->data = 'Item ' . $viis_name;
                                    $finalResults[] = $newItem;
                                } else {
                                    // Update the count of existing schedule
                                    foreach ($finalResults as &$result) {
                                        if ($result->id == $id) {
                                            $result->y += $item->y;
                                            break;
                                        }
                                    }
                                }
                            }
                        }

                    // Sort $finalResults by schedule IDs
                    usort($finalResults, function ($a, $b) {
                        return $a->id - $b->id;
                    });

                    return response()->json(['status' => 1, 'result' => $finalResults, 'labelName' => 'Schedule','yAxis' => 'Total Proposal']);   
                }elseif ($proposalData == 'proposal_amount') 
                {
                    $results = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->where('proposals.disha_verify_yn', 1)
                        ->whereNotNull('proposals.disha_proposal_amt')
                        ->select(
                            DB::raw('0 as per'),
                            DB::raw('ROUND((SUM(`proposals`.`disha_proposal_amt`) / 10000000), 2) as y'),
                            'proposals.disha_proposal_amt', 'proposals.proj_sector as id',
                            DB::raw('(SELECT schedule_viis.sc_viis_no FROM schedule_viis WHERE schedule_viis.sc_viis_id = `proposals`.`proj_sector` LIMIT 1) as data')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.proj_sector')
                        ->orderBy(DB::raw("CAST(data AS SIGNED)"))
                        ->get();
                        $allAmt = $results->sum('y');
                        
                        $processedSchedules = [];
                        $finalResults = [];
                        foreach ($results as $item) {
                            $ids = explode(',', $item->id);
                            foreach ($ids as $id) {
                                if (!isset($processedSchedules[$id])) {
                                    $scheduleGet = DB::table('schedule_viis')->SELECT('sc_viis_no')->where('sc_viis_id', $id)->first();
                                    $processedSchedules[$id] = true;
                                    $newItem = new \stdClass();
                                    $newItem->y = $item->y;
                                    $newItem->id = $scheduleGet->sc_viis_no;
                                    $finalResults[$id] = $newItem;
                                } else {
                                    // Update the count of existing schedule
                                    /*
                                    foreach ($finalResults as &$result) {
                                        if ($result->id == $id) {
                                            $result->y += $item->y;
                                            break;
                                        }
                                    }*/
                                    $oldAmt = $finalResults[$id]->y;
                                    $finalResults[$id]->y = sprintf('%0.2f', ($oldAmt + $item->y));
                                }
                            }
                        }
                
                        // Modify label names
                        foreach ($finalResults as $result) {
                            $result->data = 'Schedule ' . $result->id;
                            $sum = round($result->y, 2);
                            $per = sprintf('%0.2f', (($sum * 100) / $allAmt)).'%';
                            $result->per = $per;
                            $result->y = $sum;
                        }

                        // Sort $finalResults by schedule IDs
                        usort($finalResults, function ($a, $b) {
                            return $a->id - $b->id;
                        });

                    return response()->json(['status' => 1, 'result' => $finalResults, 'labelName' => 'Schedule','yAxis' => 'Total Amount (In Cr)']);   
                }
            break;
            case 'address':
                if($proposalData == 'proposal_count')
                {
                    $results = DB::table('proposals')
                        ->where('final_save', 1)
                        ->select(
                            'proposals.address_to as id',
                            DB::raw('(SELECT addresstos.addressto FROM addresstos WHERE addresstos.adto_id = `proposals`.`address_to` LIMIT 1) as data'),
                            DB::raw('COUNT(`proposals`.`address_to`) as y')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.address_to')
                        ->get();
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Address','yAxis' => 'Total Proposal']);
                }
                elseif ($proposalData == 'proposal_amount') 
                {
                    $results = DB::table('proposals')
                        ->where('proposals.final_save', 1)
                        ->where('proposals.disha_verify_yn', 1)
                        ->whereNotNull('proposals.disha_proposal_amt')
                        ->select(
                            DB::raw('0 as per'),
                            DB::raw('SUM(`proposals`.`disha_proposal_amt`) as y'),
                            'proposals.disha_proposal_amt','proposals.address_to as id',
                            DB::raw('(SELECT addresstos.addressto FROM addresstos WHERE addresstos.adto_id = `proposals`.`address_to` LIMIT 1) as data')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposals.final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposals.final_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->groupBy('proposals.address_to')
                        ->orderBy(DB::raw("CAST(data AS SIGNED)"))
                        ->get();
                    
                    foreach ($results as $key => $roww) {
                        $sum = $roww->y;
                        $per = sprintf('%0.2f', (($sum * 100) / $allAmt)).'%';
                        $sum = round($sum / 10000000, 2);
                        $roww->y = $sum;
                        $roww->per = $per;
                    }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Address','yAxis' => 'Total Amount (In Cr)']);
                }
            break;
            case 'gst_type':
                if($proposalData == 'proposal_count')
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->select(
                            DB::raw('SUM(CASE WHEN gst_type = "GS" THEN 1 ELSE 0 END) as gst'),
                            DB::raw('SUM(CASE WHEN gst_type = "NG" THEN 1 ELSE 0 END) as nonGst')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                            return $query->where('proposals.address_to', $workCenterId);
                        })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'data' => 'GST',
                                'y' => (int)$getDetail->gst,
                                'id' =>'GS'
                            ];
                            $results[] = [
                                'data' => 'Non-GST',
                                'y' => (int)$getDetail->nonGst,
                                'id' =>'NG'
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Gst type','yAxis' => 'Total Count']);
                }
                // elseif($proposalData == 'proposal_amount')
                // {
                //     $getDetails = DB::table('proposals')
                //         ->where('final_save', 1)
                //         ->select(
                //             DB::raw('SUM(CASE WHEN gst_type = "GS" THEN fpr_project_cost ELSE 0 END) as gst'),
                //             DB::raw('SUM(CASE WHEN gst_type = "NG" THEN fpr_project_cost ELSE 0 END) as nonGst'),
                //         )
                //         ->when(!empty($startDate), function ($query) use ($startDate) {
                //             return $query->whereDate('final_save_date', '>=', $startDate);
                //         })
                //         ->when(!empty($endDate), function ($query) use ($endDate) {
                //             return $query->whereDate('final_save_date', '<=', $endDate);
                //         })
                //         // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                //         //     return $query->where('proposals.address_to', $workCenterId);
                //         // })
                //         ->get();

                //         // Format data for Highcharts
                //         $results = [];
                //         foreach ($getDetails as $getDetail) {
                //             $results[] = [
                //                 'data' => 'GST',
                //                 'y' => (int)$getDetail->gst,
                //                 'id' =>'GS'
                //             ];
                //             $results[] = [
                //                 'data' => 'Non-GST',
                //                 'y' => (int)$getDetail->nonGst,
                //                 'id' =>'NG'
                //             ];
                //         }
                //     return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Gst type','yAxis' => 'Total Amount']);  
                // }
                elseif($proposalData == 'proposal_amount')
                {
                    $getDetails = DB::table('proposals')
                        ->where('final_save', 1)
                        ->where('disha_verify_yn',1)
                        ->select(
                            DB::raw('SUM(CASE WHEN gst_type = "GS" THEN disha_proposal_amt ELSE 0 END) as gst'),
                            DB::raw('SUM(CASE WHEN gst_type = "NG" THEN disha_proposal_amt ELSE 0 END) as nonGst'),
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                        //     return $query->where('proposals.address_to', $workCenterId);
                        // })
                        ->get();

                        // Format data for Highcharts
                        $results = [];
                        foreach ($getDetails as $getDetail) {
                            $results[] = [
                                'data' => 'GST',
                                'y' => (int)$getDetail->gst,
                                'id' =>'GS'
                            ];
                            $results[] = [
                                'data' => 'Non-GST',
                                'y' => (int)$getDetail->nonGst,
                                'id' =>'NG'
                            ];
                        }
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Gst type','yAxis' => 'Total Amount']);  
                }
            break;
            case 'target_audience':
                if($proposalData == 'proposal_count')
                {
                    $getAudienceDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN women_no > 0 THEN 1 ELSE 0 END) as Women'),
                        DB::raw('SUM(CASE WHEN child_no > 0 THEN 1 ELSE 0 END) as Children'),
                        DB::raw('SUM(CASE WHEN sr_citizen_no > 0 THEN 1 ELSE 0 END) as SrCitizens'),
                        DB::raw('SUM(CASE WHEN handicap_no > 0 THEN 1 ELSE 0 END) as Handicap'),
                        DB::raw('SUM(CASE WHEN lgbtq_no > 0 THEN 1 ELSE 0 END) as Lgbtq'),
                        DB::raw('SUM(CASE WHEN other_no > 0 THEN 1 ELSE 0 END) as Others')
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();

                    // Format data for Highcharts
                    $results = [];
                    foreach ($getAudienceDetails as $audienceDetail) {
                        $results[] = [
                            'data' => 'Women',
                            'y'    => (int)$audienceDetail->Women,
                            'id'   => 'women_no'
                        ];
                        $results[] = [
                            'data' => 'Children',
                            'y'    => (int)$audienceDetail->Children,
                            'id'   => 'child_no'
                        ];
                        $results[] = [
                            'data' => 'Senior Citizens',
                            'y' => (int)$audienceDetail->SrCitizens,
                            'id'   => 'sr_citizen_no'
                        ];
                        $results[] = [
                            'data' => 'Handicap',
                            'y' => (int)$audienceDetail->Handicap,
                            'id'   => 'handicap_no'
                        ];
                        $results[] = [
                            'data' => 'LGBTQ',
                            'y' => (int)$audienceDetail->Lgbtq,
                            'id'   => 'lgbtq_no'
                        ];
                        $results[] = [
                            'data' => 'Others',
                            'y' => (int)$audienceDetail->Others,
                            'id'   => 'other_no'
                        ];
                    }
                   
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Focused/Target','yAxis' => 'Total Proposal']);   
                }
                elseif($proposalData == 'proposal_amount')
                {
                    $getAudienceDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN women_no > 0 THEN women_amt ELSE 0 END) as Women'),
                        DB::raw('SUM(CASE WHEN child_no > 0 THEN child_amt ELSE 0 END) as Children'),
                        DB::raw('SUM(CASE WHEN sr_citizen_no > 0 THEN sr_citizen_amt ELSE 0 END) as SrCitizens'),
                        DB::raw('SUM(CASE WHEN handicap_no > 0 THEN handicap_amt ELSE 0 END) as Handicap'),
                        DB::raw('SUM(CASE WHEN lgbtq_no > 0 THEN lgbtq_amt ELSE 0 END) as Lgbtq'),
                        DB::raw('SUM(CASE WHEN other_no > 0 THEN other_no ELSE 0 END) as Others')
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();
                    
                    // Format data for Highcharts
                    $results = [];
                    foreach ($getAudienceDetails as $audienceDetail) {
                        $results[] = [
                            'data' => 'Women',
                            'y'    => (int)$audienceDetail->Women,
                            'id'   => 'women_no'
                        ];
                        $results[] = [
                            'data' => 'Children',
                            'y'    => (int)$audienceDetail->Children,
                            'id'   => 'child_no'
                        ];
                        $results[] = [
                            'data' => 'Senior Citizens',
                            'y' => (int)$audienceDetail->SrCitizens,
                            'id'   => 'sr_citizen_no'
                        ];
                        $results[] = [
                            'data' => 'Handicap',
                            'y' => (int)$audienceDetail->Handicap,
                            'id'   => 'handicap_no'
                        ];
                        $results[] = [
                            'data' => 'LGBTQ',
                            'y' => (int)$audienceDetail->Lgbtq,
                            'id'   => 'lgbtq_no'
                        ];
                        $results[] = [
                            'data' => 'Others',
                            'y' => (int)$audienceDetail->Others,
                            'id'   => 'other_no'
                        ];
                    }
        
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Focused/Target','yAxis' => 'Total Amount']);
                }
            break;
            case 'community_beneficery':
                if($proposalData == 'proposal_count')
                {
                    $getBeneficeryDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN schedule_caste_number > 0 THEN 1 ELSE 0 END) as ScheduleCaste'),
                        DB::raw('SUM(CASE WHEN schedule_tribe_number > 0 THEN 1 ELSE 0 END) as ScheduleTribe'),
                        DB::raw('SUM(CASE WHEN obc_number > 0 THEN 1 ELSE 0 END) as Obc'),
                        DB::raw('SUM(CASE WHEN minority_number > 0 THEN 1 ELSE 0 END) as Minority'),
                        DB::raw('SUM(CASE WHEN general_number > 0 THEN 1 ELSE 0 END) as General'),
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();

                    // Format data for Highcharts
                    $results = [];
                    foreach ($getBeneficeryDetails as $getBeneficeryDetail) {
                        $results[] = [
                            'data' => 'Schedule Caste',
                            'y'    => (int)$getBeneficeryDetail->ScheduleCaste,
                            'id'   => 'schedule_caste_number'
                        ];
                        $results[] = [
                            'data' => 'Schedule tribe',
                            'y'    => (int)$getBeneficeryDetail->ScheduleTribe,
                            'id'   => 'schedule_tribe_number'
                        ];
                        $results[] = [
                            'data' => 'Obc',
                            'y' => (int)$getBeneficeryDetail->Obc,
                            'id'   => 'obc_number'
                        ];
                        $results[] = [
                            'data' => 'Minority',
                            'y' => (int)$getBeneficeryDetail->Minority,
                            'id'   => 'minority_number'
                        ];
                        $results[] = [
                            'data' => 'General',
                            'y' => (int)$getBeneficeryDetail->General,
                            'id'   => 'general_number'
                        ];
                    }
                   
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Community of beneficery','yAxis' => 'Total Proposal']);
                }
                elseif($proposalData == 'proposal_amount')
                {
                    $getBeneficeryDetails = DB::table('proposals')
                    ->where('final_save', 1)
                    ->select(
                        DB::raw('SUM(CASE WHEN schedule_caste_number > 0 THEN 1 ELSE 0 END) as ScheduleCaste'),
                        DB::raw('SUM(CASE WHEN schedule_tribe_number > 0 THEN 1 ELSE 0 END) as ScheduleTribe'),
                        DB::raw('SUM(CASE WHEN obc_number > 0 THEN obc_amount ELSE 0 END) as Obc'),
                        DB::raw('SUM(CASE WHEN minority_number > 0 THEN minority_amount ELSE 0 END) as Minority'),
                        DB::raw('SUM(CASE WHEN general_number > 0 THEN general_amount ELSE 0 END) as General'),
                    )
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    // ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    //     return $query->where('address_to', $workCenterId);
                    // })
                    ->get();

                    // Format data for Highcharts
                    $results = [];
                    foreach ($getBeneficeryDetails as $getBeneficeryDetail) {
                        $results[] = [
                            'data' => 'Schedule Caste',
                            'y'    => (int)$getBeneficeryDetail->ScheduleCaste,
                            'id'   => 'schedule_caste_number'
                        ];
                        $results[] = [
                            'data' => 'Schedule tribe',
                            'y'    => (int)$getBeneficeryDetail->ScheduleTribe,
                            'id'   => 'schedule_tribe_number'
                        ];
                        $results[] = [
                            'data' => 'Obc',
                            'y' => (int)$getBeneficeryDetail->Obc,
                            'id'   => 'obc_number'
                        ];
                        $results[] = [
                            'data' => 'Minority',
                            'y' => (int)$getBeneficeryDetail->Minority,
                            'id'   => 'minority_number'
                        ];
                        $results[] = [
                            'data' => 'General',
                            'y' => (int)$getBeneficeryDetail->General,
                            'id'   => 'general_number'
                        ];
                    }
                   
                    return response()->json(['status' => 1, 'result' => $results, 'labelName' => 'Community of beneficery','yAxis' => 'Total Amount']);
                }
            break;
            default:
                return response()->json(['status' => 2, 'result' => "Invalid request.", 'labelName' => 'No data found.']);
            break;
        }
    }

    /**
        * This function display all data of Line chart
    */
    // public function mapChart(Request $request)
    // {
    //     DB::statement("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'");
    //     $startDate = @$request->start_date;
    //     $endDate = @$request->end_date;

    //     $results = DB::table('pincode')
    //         ->select('pincode.state_short_name as id', 'pincode.state_name as name', 'pincode.state_code')
    //         ->selectRaw('FLOOR(1 + RAND() * 5) as value')
    //         ->selectSub(function ($query) {
    //             $query->selectRaw('count(proposals_pincodes.propp_id)')
    //                   ->from('proposals_pincodes')
    //                   ->whereColumn('proposals_pincodes.state_cd', 'pincode.state_code');
    //         }, 'total_proposal')
    //         // ->selectSub(function ($query) {
    //         //     $query->selectRaw('COALESCE(sum(proposals.fpr_project_cost), 0)')
    //         //           ->from('proposals')
    //         //             ->where('proposals.final_save', 1)
    //         //           ->whereIn('proposals.prop_id', function ($query) {
    //         //               $query->selectRaw('GROUP_CONCAT(proposals_pincodes.propp_id)')
    //         //                     ->from('proposals_pincodes')
    //         //                     ->whereColumn('proposals_pincodes.state_cd', 'pincode.state_code');
    //         //           });
    //         // }, 'total_amt')
    //         ->groupBy('pincode.state_code')
    //         ->get();

    //     return response()->json(['status' => 1, 'result' => $results]);
    // }

    /**
        * This function display proposal details
    */
    public function tableDetail(Request $request)
    {
        // Get data from request
        $user    = @AuthUser();
        $id = $request->id;
        $columnFilter = $request->param;
        $startDate = @$request->start_date;
        $endDate = @$request->end_date;
        $workCenterId = @$user->work_station;
        $proposalDataValue = $request->proposalDataValue;
        
      
        // Common function to generate HTML table
        $generateTable = function ($getProposalDetails) {
            $results = '<table border="1" class="table table-bordered"><thead><tr><th>Sno.</th><th>Proposal Title</th><th>Agency Name</th><th>VIP Name</th><th>Proposed Amt</th><th>Approved Amt</th><th>Action</th></tr></thead><tbody>';

            $recordCount = 0;
            if ($getProposalDetails->count() > 0) {
                $i=1;
                foreach ($getProposalDetails as $getProposalDetail) {
                   
                    // if ($recordCount < 5) {
                        $prop_id = $getProposalDetail->prop_id;
                        $viewUrl = route("proposal.view", ["id" => $prop_id]);
                        $results .= '<tr>';
                        $results .= '<td>' . $i. '</td>';
                        // $results .= '<td>' . $getProposalDetail->userProfile->email . '</td>';
                        // $results .= '<td>' . $getProposalDetail->userProfile->user_pan_card_no . '</td>';
                        $results .= '<td>' . strtoupper($getProposalDetail->project_title) . '</td>';
                        $results .= '<td>' . strtoupper($getProposalDetail->agency_name) . '</td>';
                        $results .= '<td>' . strtoupper($getProposalDetail->vip_name) . '</td>';
                        $results .= '<td>' . formatIndianRupees($getProposalDetail->project_cost, 2) . '</td>';
                        $results .= '<td>' . formatIndianRupees($getProposalDetail->disha_proposal_amt, 2) . '</td>';
                        $results .= '<td> <a href="'.$viewUrl.'" class="btn btn-xs btn-success" title="View" ><i class="fa fa-eye"></i> View</a></td>';
                        $results .= '</tr>';
                        $recordCount++;
                    // }
                    $i++;
                }
                
            } else {
                $results .= '<td>' . 'No data found' . '</td>';
            }
            $results .= '</tbody></table>';

            // Check if there are more than 5 records
            $hasMoreRecords = $getProposalDetails->count() >= 5;

            // Include a link if there are more records
            if ($hasMoreRecords) {
                // $results .= '<a href="' . route('proposal.all') . '">View More</a>';
            }
            return $results;
        };

        // Switch statement for different filters
        switch ($columnFilter) {
            case 'state':
                if($proposalDataValue == 'proposal_count')
                {
                    $getProposalPincodeId = DB::table('proposals_pincodes')->where('state_cd', $id)->pluck('propp_id');
                    $getProposalDetails = Proposal::with('userProfile')
                    ->whereIn('prop_id', $getProposalPincodeId)
                    ->where('final_save', 1)
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();
                    
                    return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                } elseif($proposalDataValue == 'proposal_amount')
                {
                    $getProposalPincodeId = DB::table('proposals_pincodes')->where('state_cd', $id)->pluck('propp_id');
                    $getProposalDetails = Proposal::with('userProfile')
                    ->whereIn('prop_id', $getProposalPincodeId)
                    ->where('final_save', 1)
                    ->where('disha_verify_yn' , 1)
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();
                    return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                }
                break;
            case 'city':
                if($proposalDataValue == 'proposal_count')
                {
                    $getProposalPincodeId = DB::table('proposals_pincodes')->where('district_cd', $id)
                    ->pluck('propp_id');
                
                    $getProposalDetails = Proposal::with('userProfile')
                    ->whereIn('prop_id', $getProposalPincodeId)
                    ->where('final_save', 1)
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();
    
                    return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                }
                break;
            case 'status':
                $getProposalDetails = Proposal::with('userProfile')
                ->where('proposal_status', $id)
                ->where('final_save', 1)
                ->where('disha_verify_yn' , 1)
                ->when(!empty($startDate), function ($query) use ($startDate) {
                    return $query->whereDate('final_save_date', '>=', $startDate);
                })
                ->when(!empty($endDate), function ($query) use ($endDate) {
                    return $query->whereDate('final_save_date', '<=', $endDate);
                })
                ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    return $query->where('address_to', $workCenterId);
                })
                ->get();
                return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                break;
            case 'schedule':
                if($proposalDataValue == 'proposal_count')
                {
                    $getProposalDetails = Proposal::with('userProfile')
                    ->where('final_save', 1)
                    ->whereNotNull('proj_sector')
                    ->where('proj_sector', $id)
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();
                    return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                }
                elseif($proposalDataValue == 'proposal_amount')
                {
                    $getProposalDetails = Proposal::with('userProfile')
                    ->where('final_save', 1)
                    ->where('disha_verify_yn' , 1)
                    ->whereNotNull('disha_proposal_amt')
                    ->where('proj_sector', $id)
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('.final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('.final_save_date', '<=', $endDate);
                    })
                    ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();

                    return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                }
                break;
               

            case 'address':
                if($proposalDataValue == 'proposal_count')
                {
                    $getProposalDetails = Proposal::with('userProfile')
                    ->where('final_save', 1)
                    ->where('address_to', $id)
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();
                    return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                }elseif($proposalDataValue == 'proposal_amount'){
                    $getProposalDetails = Proposal::with('userProfile')
                    ->where('final_save', 1)
                    ->where('disha_verify_yn' , 1)
                    ->where('address_to', $id)
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();
                    return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                }
                
                break;
            case 'gst_type':
                $getProposalDetails = Proposal::with('userProfile')
                ->where('final_save', 1)
                ->where('disha_verify_yn' , 1)
                ->where('gst_type', $id)
                ->when(!empty($startDate), function ($query) use ($startDate) {
                    return $query->whereDate('final_save_date', '>=', $startDate);
                })
                ->when(!empty($endDate), function ($query) use ($endDate) {
                    return $query->whereDate('final_save_date', '<=', $endDate);
                })
                ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                    return $query->where('address_to', $workCenterId);
                })
                ->get();
                return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                break;
            case 'target_audience':
                $getProposalDetails = Proposal::with('userProfile')->where('final_save', 1)->where('disha_verify_yn' , 1)
                    ->when($id == 'women_no', function ($query) use ($id) {
                        return $query->where('women_no','>', 0)->orderBy('women_no');
                    })
                    ->when($id == 'child_no', function ($query) use ($id) {
                        return $query->where('child_no','>', 0)->orderBy('child_no');
                    })
                    ->when($id == 'sr_citizen_no', function ($query) use ($id) {
                        return $query->where('sr_citizen_no','>', 0)->orderBy('sr_citizen_no');
                    })
                    ->when($id == 'handicap_no', function ($query) use ($id) {
                        return $query->where('handicap_no','>', 0)->orderBy('handicap_no');
                    })
                    ->when($id == 'lgbtq_no', function ($query) use ($id) {
                        return $query->where('lgbtq_no','>', 0)->orderBy('lgbtq_no');
                    })
                    ->when($id == 'other_no', function ($query) use ($id) {
                        return $query->where('other_no','>', 0)->orderBy('other_no');
                    })
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        return $query->whereDate('final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        return $query->whereDate('final_save_date', '<=', $endDate);
                    })
                    ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                        return $query->where('address_to', $workCenterId);
                    })
                    ->get();
                return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                break;
            case 'community_beneficery':
                    $getProposalDetails = Proposal::with('userProfile')->where('final_save', 1)->where('disha_verify_yn' , 1)
                        ->when($id == 'schedule_caste_number', function ($query) use ($id) {
                            return $query->where('schedule_caste_number','>', 0)->orderBy('schedule_caste_number');
                        })
                        ->when($id == 'schedule_tribe_number', function ($query) use ($id) {
                            return $query->where('schedule_tribe_number','>', 0)->orderBy('schedule_tribe_number');
                        })
                        ->when($id == 'obc_number', function ($query) use ($id) {
                            return $query->where('obc_number','>', 0)->orderBy('obc_number');
                        })
                        ->when($id == 'minority_number', function ($query) use ($id) {
                            return $query->where('minority_number','>', 0)->orderBy('minority_number');
                        })
                        ->when($id == 'general_number', function ($query) use ($id) {
                            return $query->where('general_number','>', 0)->orderBy('general_number');
                        })
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('final_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('final_save_date', '<=', $endDate);
                        })
                        ->when(!empty($workCenterId), function ($query) use ($workCenterId) {
                            return $query->where('address_to', $workCenterId);
                        })
                        ->get();
                    return response()->json(['status' => 1, 'result' => $generateTable($getProposalDetails)]);
                    break;
            default:
                return response()->json(['status' => 0, 'result' => 'Invalid filter']);
                break;
        }
    }

    public function indexList($length='', $search='')
    {
        if(empty($length)){
            $length = 10;
        }
        $data = [];
        //$event_list = DB::table('events')->where('actv_event', 1)->paginate($length);
        $agencyList = DB::table('users')
                    ->where(function ($query) use ($search) {
                        $query->where('user_pan_card_no', 'like', '%'.$search.'%')
                        ->orWhere('vendor_code', 'like', '%'.$search.'%')
                        ->orWhere('pan_card_no', 'like', '%'.$search.'%')
                        ->orWhere('gst_reg_no', 'like', '%'.$search.'%')
                        ->orWhere('certificate_reg_no', 'like', '%'.$search.'%')
                        ->orWhere('gst_exemption_no', 'like', '%'.$search.'%')
                        ->orWhere('canclled_chq_no', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                    })->where('user_type', 2)->where('active_yn', 1)->paginate($length);

        //$agencyList    = DB::table('users')->where('user_type', 2)->where('active_yn', 1)->get();
        $data['agencyList']  = $agencyList;
        $data['list_length'] = $length;
        $data['list_search'] = $search;

        $pg = 'agency_list';
        return view($pg)->with($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function createFile(Request $request)
    {
        $user_email_id  = @AuthUser()->email;
        $user_pan_no    = @AuthUser()->user_pan_card_no;
        $directory_name = @AuthUser()->directory_name ?? '';
        //$directoryPath = 'app/'.$user_email_id.'_'.$user_pan_no;
        $directoryPath = 'app/'.$directory_name;
        
        if ($request->hasFile('doc_file')) {
            $doc_name = $request->doc_name;
            $file = $request->file('doc_file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $newName = $doc_name.'_' . time() . '.' . $extension;
            $file->storeAs($directoryPath, $newName);
            return response()->json(['status' => 1, "message" => "file uploded.", "file_name" => $newName]);
        }else{
            return response()->json(['status' => 2, "message" => "file not uplode.", "file_name" => ""]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeCSR(Request $request)
    {
        //echo '<pre>'; print_r($request->all());
        $user_id        = @AuthUser()->id;
        $user_email_id  = @AuthUser()->email;
        $user_pan_no    = @AuthUser()->user_pan_card_no;
        $directory_name    = @AuthUser()->directory_name ?? '';
        //$directoryPath  = $user_email_id.'_'.$user_pan_no.'/';
        $directoryPath  = $directory_name.'/';
        /*
        $validator = Validator::make($request->all(), [
            'vendor_code' => 'required', 
        ],[
            'vendor_code.required' => 'Vendor code is required field.',
        ]);
        
        if ($validator->fails()) {
            $allErrors = $validator->errors()->all();
            $allErrors = implode('<br>', $allErrors);
            return response()->json(['status' => 2, 'message' => $allErrors]);
        }*/
        $doc_nameAll = $request->doc_name;
        foreach($doc_nameAll as $key => $file){
            if ($request->hasFile("doc_file.".$key)) {
                $file = $request->file("doc_file.".$key);
                $ext = $file->getClientOriginalExtension();
                if ($ext === 'jped' || $ext === 'jpg' || $ext === 'png' || $ext === 'pdf') {

                }else{
                    return response()->json(['status' => 2, 'message' => "Invalid file format"]);
                }
            }else{
                return response()->json(['status' => 2, 'message' => "Select all files"]);
            }
        }
        $rowData = [];
        foreach($doc_nameAll as $key => $file){
            $file_nm = $request->input("doc_name.".$key);
            $file_no = $request->input("doc_file_no.".$key);
            $file = $request->file("doc_file.".$key);
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $newName = $file_nm.'_'.$user_id.'_' . time() . '.' . $extension;
            $file->storeAs($directoryPath, $newName);


            $rowData[$file_nm]          = $directoryPath.$newName;
            $rowData[$file_nm.'_no']    = strtoupper($file_no);
        }
        $rowData['password']       = time(); /// not in use
        $rowData['email']          = $user_email_id;
        $rowData['vendor_code']    = $request->vendor_code;
        $rowData['created_at']     = date('Y-m-d H:i:s');
        $rowData['user_type']      = 2;

        $rowDataQuery = DB::table('users')->where('id', $user_id)->update($rowData);
        if($rowDataQuery){
            $status = 1;
            $message = "Saved Successfully.";
        }else{
            $status = 2;
            $message = "Not Saved.";
        }
        return response()->json(['status' => $status, 'message' => $message]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = [];
        $agency = DB::table('users')->where('id', $id)->first();
        $data['agency'] = $agency;
        return view('agency', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Csr $csr)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function pincodeList(Request $request)
    {
        //print_r($request->all());
        $code      = $request->code;
        $list_type = $request->list_type;
        $stateList = $districtList = $villageList = null;
        switch ($list_type) {
            case 'district':
                $districtList   = districtList('', $code);
                $data = '<option value="">Select DistrictList</option>';
                foreach ($districtList as $key => $district) {
                    $data .= '<option value="'.$district->district_code.'">'.$district->district_name.'</option>';
                }
                return response()->json([
                    'villageList' => $data,
                ]);
                /*
                $stateList      = stateList($pincode);
                $state_code     = $stateList[0]->state_code;
                $district_code  = $districtList[0]->district_code;
                $villageList    = villageList($pincode, $state_code, $district_code);
                //echo '<pre>'; print_r($villageList);
                return response()->json([
                    'stateList' => $stateList,
                    'districtList' => $districtList,
                    'villageList' => $villageList,
                ]);*/
            break;
            case 'village':
                $villageList    = villageList('', '', $code);
                //echo '<pre>'; print_r($villageList);
                $data = '<option value="">Select Area/Taluka</option>';
                foreach ($villageList as $key => $village) {
                    $data .= '<option value="'.$village->pincode_id.'">'.$village->village_taluka.'</option>';
                }
                return response()->json([
                    'villageList' => $data,
                ]);
            break;
            case 'block':
                $blockList    = blockList('', '', $code);
                //echo '<pre>'; print_r($villageList);
                $data = '<option value="">Select Block/Taluka</option>';
                foreach ($blockList as $key => $block) {
                    $data .= '<option value="'.$block->block_id.'">'.$block->block_ps.'</option>';
                }
                return response()->json([
                    'villageList' => $data,
                ]);
            break;

            default:
            // code...
            break;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Csr $csr)
    {
        //
    }

    /**
     * List of all expired documents of agency
    */
    public function expireDocuments(Request $request) 
    {
        $wc_code = @$request->wc_code;
        $pageName = 'expired';
        $data = [];
        $cronController = new CronController();
        $expiredDocumentResults = $cronController->expiredAgencyDocs($pageName, false, $wc_code);
        $view = 'expire_document';
        $data['expiredDocumentResults'] = $expiredDocumentResults;
        $data['wc_code'] = $wc_code;
        return view($view)->with($data);
    }


    /**
     * Listing of total agency/proposal count
    */
    public function wcReports(Request $request)
    {
        $startDate  = @$request->start_date;
        $endDate    = @$request->end_date;
        $workCenter = @$request->work_center;

        if($startDate == null || $startDate == ''){
            // $month = date('m');
            // if($month < 4){
            //     $startDate = (date('Y')-1).'-04-01';
            // }else{
            //     $startDate = date('Y').'-04-01';
            // }
            $startDate = '2024-04-01';
            $endDate = date('Y-m-d');
        }

        // Get Agency/Proposal count
        $agencyProposalDetails = DB::table('addresstos as address')
            ->select(
                'address.adto_id',
                'address.addressto',
                DB::raw('count(distinct agencies.agn_id) as total_agencies'),
                DB::raw('count(distinct CASE 
                    WHEN agencies.agency_verified = 1 
                        AND agencies.financial_save = 1 
                        AND agencies.agency_save_date IS NOT NULL ' .
                        (!empty($startDate) ? " AND agencies.agency_save_date >= '$startDate'" : '') .
                        (!empty($endDate) ? " AND agencies.agency_save_date <= '$endDate'" : '') . 
                    ' THEN agencies.agn_id ELSE NULL END) as verified_agencies'),
                DB::raw('count(distinct proposals.prop_id) as total_proposals'),
                DB::raw('(
                    SELECT count(*)
                    FROM proposals AS proposal
                    WHERE proposal.address_to = address.adto_id
                    AND proposal.final_save = 1
                    AND proposal.pv_status = 2 
                    AND proposal.final_save_date IS NOT NULL' .
                    (!empty($startDate) ? " AND proposal.final_save_date >= '$startDate'" : '') .
                    (!empty($endDate) ? " AND proposal.final_save_date <= '$endDate'" : '') .
                ') as pv_no_count'),
                DB::raw('(
                    SELECT count(*)
                    FROM proposals AS proposal
                    WHERE proposal.address_to = address.adto_id
                    AND proposal.final_save = 1
                    AND proposal.disha_verify_yn = 1
                    AND proposal.final_save_date IS NOT NULL' .
                    (!empty($startDate) ? " AND proposal.final_save_date >= '$startDate'" : '') .
                    (!empty($endDate) ? " AND proposal.final_save_date <= '$endDate'" : '') .
                ') as disha_verify_count'),
            )
            ->leftJoin('agencies', function ($join) use ($startDate, $endDate) {
                $join->on('address.adto_id', '=', 'agencies.work_center_id')
                    ->where('agencies.financial_save', 1)
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        $query->whereDate('agencies.agency_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        $query->whereDate('agencies.agency_save_date', '<=', $endDate);
                    });
            })
            ->leftJoin('proposals', function ($join) use ($startDate, $endDate) {
                $join->on('address.adto_id', '=', 'proposals.address_to')
                    ->where('proposals.final_save', 1)
                    ->whereNotNull('proposals.final_save_date')
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        $query->whereDate('proposals.final_save_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        $query->whereDate('proposals.final_save_date', '<=', $endDate);
                    });
            })
            ->when(!empty($workCenter), function ($query) use ($workCenter) {
                $query->where('address.adto_id', $workCenter);
            })
            ->groupBy('address.adto_id', 'address.addressto')
            ->get();
            
        $workCenterNames = DB::table('addresstos')->where('status',1)->get();

        $data['agencyProposalDetails'] = $agencyProposalDetails;
        $data['workCenterNames'] = $workCenterNames;
        
        // View file name
        $view = 'work_center_performance';
        return view($view)->with($data);
    }

    public function stateAmountGraph($startDate = '', $endDate = '', $workCenterId = 0)
    {
        $user_id        = @AuthUser()->id;
        $tableName      = "temp_proposals_pincodes_".$user_id."_".time();
        DB::statement("CREATE TEMPORARY TABLE {$tableName} (
                            propp_id INT,
                            state_cd INT,
                            budget_percent DECIMAL(10, 2),
                            disha_approved_amt DECIMAL(15, 2),
                            proposal_save_date DATE,
                            workcenter_cd INT
                        )");

        $resultsAllRows = DB::table('proposals_pincodes')
                        ->select(
                            'propp_id',
                            'state_cd',
                            DB::raw('SUM(budget_percent) AS budget_percent'),
                            DB::raw('MAX(disha_approved_amt) AS disha_approved_amt'),
                            DB::raw('MAX(proposal_save_date) AS proposal_save_date'),
                            DB::raw('MAX(workcenter_cd) AS workcenter_cd')
                        )
                        ->when(!empty($startDate), function ($query) use ($startDate) {
                            return $query->whereDate('proposal_save_date', '>=', $startDate);
                        })
                        ->when(!empty($endDate), function ($query) use ($endDate) {
                            return $query->whereDate('proposal_save_date', '<=', $endDate);
                        })
                        ->when($workCenterId > 1, function ($query) use ($workCenterId) {
                            return $query->where('workcenter_cd', $workCenterId);
                        })
                        ->where('disha_approved_amt', '>', 0)
                        ->groupBy('propp_id', 'state_cd')
                        ->orderByDesc('propp_id')->get();
        $dataToInsert = $resultsAllRows->map(function ($row) {
                            return [
                                'propp_id' => $row->propp_id,
                                'state_cd' => $row->state_cd,
                                'budget_percent' => $row->budget_percent,
                                'disha_approved_amt' => $row->disha_approved_amt,
                                'proposal_save_date' => $row->proposal_save_date,
                                'workcenter_cd' => $row->workcenter_cd,
                            ];
                        })->toArray();
        DB::table($tableName)->insert($dataToInsert);
        
        $totalAmount = DB::table($tableName)->sum('disha_approved_amt');
        $results = DB::table($tableName)
                    ->select(
                        'state_cd as id',
                        DB::raw("CAST(ROUND((SUM(disha_approved_amt) / 10000000), 2) AS FLOAT) as y"),
                        DB::raw('(SELECT pincode.state_name FROM pincode WHERE pincode.state_code = ' . $tableName . '.state_cd LIMIT 1) as data'),
                        DB::raw('CONCAT(CAST(ROUND((SUM(disha_approved_amt) / ' . $totalAmount . ') * 100, 2) AS DECIMAL(10, 2)), " %") as per')
                    )->groupBy('state_cd')->get();
        /*
        $perSum = $results->sum(function ($item) {
            return $item->per;
        });
        print_r($perSum); echo '<pre>'; print_r($results);
        */
        DB::statement("DROP TEMPORARY TABLE IF EXISTS ".$tableName);
        return $results;
    }
}