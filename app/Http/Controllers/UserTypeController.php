<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\EmailUpdate;
use App\Models\Agency;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\LogLoginController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Carbon;

class UserTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter_session_name = "user_filters";
        $methodType = strtoupper($request->method());
        if($methodType == 'POST'){
            $inputDataAll = $request->all();
        }else{
            $inputDataAll = session($filter_session_name, []);
            if ($request->has('length')) {
                $s_length = (int) $request->length ?? 10;
                $inputDataAll['length'] = $s_length;
            }
            if ($request->has('search')) {
                $s_search = $request->search ?? '';
                $inputDataAll['search'] = $s_search;
            }
            if ($request->has('wc_code')) {
                $s_wc_code = (int) $request->wc_code ?? 0;
                $inputDataAll['wc_code'] = $s_wc_code;
            }
            if ($request->has('role_code')) {
                $s_role_code = (int) $request->role_code ?? 0;
                $inputDataAll['role_code'] = $s_role_code;
            }
        }
        $inputData = (object) $inputDataAll;
        session()->put($filter_session_name, $inputDataAll);

        $length = $inputData->length ?? 10;
        $search = $inputData->search ?? '';
        $wc_code = $inputData->wc_code ?? 0;
        $role_code = $inputData->role_code ?? 0;
        
        $users = User::with(['roleType', 'addressTo', 'headUser'])
                    ->where(function ($query) use ($search) {
                        $query->where('users.user_name', 'like', '%'.$search.'%')
                        ->orWhere('users.email', 'like', '%'.$search.'%')->orWhere('users.primary_email', 'like', '%'.$search.'%');
                        //->orWhere('role_types.role_type', 'like', '%'.$search.'%');
                    })
                    ->when($wc_code > 0, function ($query) use ($wc_code) {
                        return $query->where('users.work_station', $wc_code); /// for agency
                    })
                    ->when($role_code > 0, function ($query) use ($role_code) {
                        return $query->where('users.user_type', $role_code); /// for agency
                    })
                    ->whereBetween('users.user_type', [1, 10])->orderBy('users.user_name')
                    ->paginate($length);
        //dd($users);
        $data = [];
        $data['user'] = $users;
        $data['list_length'] = $length;
        $data['list_search'] = $search;
        $data['list_wc_cd'] = $wc_code;
        $data['list_role_cd'] = $role_code;
        $data['inputData'] = (array) $inputData;

        return view('user_list', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function show($ae, $id='')
    {
        $data = [];
        $logsCount = 0;
        if($ae=='edit' && $id>0){
            $users = User::where('id', $id)->first();
            $data['users'] = $users;

            $logsData = DB::table('log_logins')->where('description', 'temp_user')->where('login_by', $id)->orderByDesc('ll_id')->limit(25)->get();
            $logsCount = $logsData->count();
            $data['logsData'] = $logsData;
        }
        $data['logsCount'] = $logsCount;
        return view('user_type',$data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function lock($id, $action)
    {
       $user = User::find($id);
       if($user){
        if($action == 'lock'){
        $user->active_yn = 3;
        $message = 'User locked successfully';
        }elseif($action == 'unlock'){
            $user->active_yn = 1;
            $message = 'User unlocked successfully';
        }else {
            
            return redirect()->route('user_list.index')->with('error', 'Invalid action');
        }
        $user->save();
        return redirect()->route('user_list.index')->with('success', $message);

       }
       return redirect()->route('user_list.index')->with('error', 'User not found');

     }
    //  public function unlock($id)
    //  {

    //     $user = User::find($id);
    //     if($user){
    //      $user->active_yn = 1;
    //      $user->save();
    //      return redirect()->route('user_list.index')->with('success', 'User unlocked Successfully');
 
    //     }
    //   }

    public function save(Request $request)
    {
        $cd = $request->cd;

        $validatedData = $request->validate([
            'role' => 'required',
            'user_name' => 'required',
            'work_station' => [
                function ($attribute, $value, $fail) use ($request) {
                    $role = $request->input('role');

                    if (($role > 2 || $role < 1) && $role != 9 && $value == null) {
                        $fail("Work center field is required.");
                    }
                },
            ],
            'team_leader' => [
                function ($attribute, $value, $fail) use ($request) {
                    $role = $request->input('role');

                    if (($role == 5) && $value == null) {
                        $fail("Team leader field is required.");
                    }
                },
            ],
            'email' => "required|email|unique:users,email,{$cd}",
            'official_email' => "required|email",
            'phone_number' => "required",
            'designation' => "required",
        ],[
            "role.required" => "Role is required field.", 
            "user_name.required" => "Name is required field.", 
            "email.required" => "Login email is required field.", 
            "official_email.required" => "Official email is required field.", 
            "phone_number.required" => "Phone number is required field.", 
            "designation.required" => "Designation is required field.", 
        ]);
        
        $login_user_id       = @AuthUser()->id;
        $login_user_type     = @AuthUser()->user_type;

        // Call Log controller
        $logs = new LogController();
        $log_message = 'TL Changed';
        $data = $request->all();

        $logData = new Request([
            'log_message' => $log_message,
            'user_id' => $login_user_id,
            'requestData' => $data
        ]);
       
        // Call a function from LogController 
        if($cd > 0)
        {
            $logsSave = $logs->adminLog($logData);
        }
      
        $password = Str::random(7);
        $password = 'admin#240';
        $hashedPassword = Hash::make($password);
        $emailid = strtolower($request->email);
        $work_station = $request->work_station;
        $work_station = $work_station > 0 ? $work_station : 0;
        $team_leader  = $request->team_leader;
        $team_leader  = $team_leader > 0 ? $team_leader : 0;
        $cpf_no = $request->cpf_no;
        $cpf_no = $cpf_no > 0 ? $cpf_no : null;
        $official_email = $request->official_email;
        $official_email = strtolower(trim($official_email));
        $designation = trim($request->designation);

        $active_for  = 'R';
        $date_fr     = @$request->date_fr;
        $date_to     = @$request->date_to;
        if($active_for == 'R'){
            $date_fr = $date_to = null;
        }
        $users = [
            'user_name' => $request->user_name,
            'primary_email' => $official_email,
            'phone_number' => $request->phone_number,
            'email' => $emailid,
            'phone_number' => $request->phone_number,
            'cpf_no' => $cpf_no,
            'user_type' => $request->role,
            'designation' => $designation,
            'head_id' => $team_leader,
            'work_station' => $work_station,
            'active_yn' => 1
        ];
        $users['active_for']    = $active_for;
        $users['date_fr']       = $date_fr;
        $users['date_to']       = $date_to;
        $todate = date('d/m/Y h:i A');
        if($cd > 0){
            $userInfoOld = userDetails($cd);
            $userInfoOld = json_encode($userInfoOld);
            $users['updated_at'] = date('Y-m-d H:i:s');
            $rowDataQuery = DB::table('users')->where('id',$cd)->update($users);
            $log_description = "user_update";
            $userInfoNew = userDetails($cd);
            $userInfoNew = json_encode($userInfoNew);
            $log_other_info = "User details updated on date ".$todate.". Old user details : ".$userInfoOld." New user details : ".$userInfoNew;
        }else{
            $users['created_at'] = date('Y-m-d H:i:s');
            $users['password'] = $hashedPassword;
            $rowDataQuery = DB::table('users')->insertGetId($users);
            $userInfoNew = userDetails($rowDataQuery);
            $userInfoNew = json_encode($userInfoNew);
            $log_description = "user_new_cerate";
            $log_other_info = "New user created on date ".$todate.". User details : ".$userInfoNew;
        }

        if($rowDataQuery){
            if($cd <= 0 || $cd == ''){
                $loginData = [
                    'cur_time' => time(),
                    'user_id' => $rowDataQuery,
                    'email_id' => $emailid,
                    'expiry' => 60 * 24 * 180,
                    'frm_page' => 'reset',
                ];
                $loginData = json_encode($loginData);
                $loginData = str_rot13($loginData);
                $loginData = base64_encode($loginData);
    
                $url = route('forgot_password.link', ["info" => $loginData]);

                $email = new EmailController();
                $login_link = route('login');
                $content = '<!DOCTYPE html><html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Login Details</title>
                    </head>
                    <body style="font-family: Arial, sans-serif;">
                    <div style="width: 95%; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
                        <h2 style="color: #333; text-align: center;">CSR Login Details</h2>
                        <p style="color: #555; text-align: left;">Dear User, login is: </p>
                        <p style="color: #555; text-align: left;"><b>Official Email-id : </b>'.$official_email.'</p>
                        <p style="color: #555; text-align: left;"><b>Login Email-id : </b>'.$emailid.'</p>
                        <p style="color: #555; text-align: left;"><b>Login Password : </b>'.$password.'</p>
                        <p style="color: #555; text-align: left;"><b>Login Link : </b><a href="'.$login_link.'" target="_blank" style="color:#6666CC;">CSR Login</a></p>
                        <br><br>

                        <p style="color: #555; text-align: left;">
                        Please click here for <a href="'.$url.'" target="blank"> Create New Password</a>
                        </p>
                        <p style="color: #555; text-align: center;">Thank you.</p>
                    </div></body></html>';
                    /*
                        <h1 style="text-align: center; font-size: 18px; margin: 10px 0;"><font style="color: #007BFF;">Password : '.$password.'</font></h1>
                    */
                $subject = "Login Details";
                $send = $email->sendMail($content, $subject, $emailid);
            }
            $logs_login = new LogLoginController();
            $api_data = new Request([
                'description' => $log_description,
                'other_info' => $log_other_info,
                'login_by' => $login_user_id,
                'user_type' => $login_user_type,
            ]);
            $logsSave = $logs_login->store($api_data);
            $msg = "Saved successfully.";
            if($cd > 0){
                $msg = "Update successfully.";
            }
            return redirect()->route('user_list.index')->with('success', $msg);
        }else{
            $msg = 'Saved not saved.';
            if($cd > 0){
                $msg = 'Saved not updated.';
            }
            return redirect()->route('user_list.index')->with('error', $msg);
        }
    }


    public function LegacyDashboard(Request $request)
    {
        $data = [];
        $list_length = $request->input('length') ?? 10;
        $list_search = $request->input('search');
        $user_id = @AuthUser()->id;
        $proposalList = DB::table('proposals')->where('propsal_submit_by', $user_id)
                        ->select('proposals.*', 'users.*', 'fpr.user_name as fpr_name', 'spr.user_name as spr_name')
                        ->selectRaw('(SELECT log_message FROM logs WHERE logs.proposal_id = proposals.prop_id AND logs.log_other = "agency" ORDER BY  logs.logId DESC LIMIT 1) AS agency_status')
                        ->selectRaw('(SELECT count(grant_sr_no) FROM proposal_amt_milestones WHERE proposal_amt_milestones.grant_prop_id = proposals.prop_id Group BY proposal_amt_milestones.grant_prop_id) AS no_of_milestone')
                        ->leftJoin('users', 'proposals.user_id', '=', 'users.id')
                        ->leftJoin('users as fpr', 'proposals.head_assign_to_fpr', '=', 'fpr.id')
                        ->leftJoin('users as spr', 'proposals.chief_assign_to_head', '=', 'spr.id')
                        ->when(!empty($list_search), function ($query) use ($list_search) {
                            $query->where('proposals.project_title', 'like', '%' . $list_search . '%')
                                ->orWhere('users.user_pan_card_no', 'like', '%' . $list_search . '%')
                                ->orWhere('users.email', 'like', '%' . $list_search . '%')
                                ->orWhere('proposals.agency_name', 'like', '%' . $list_search . '%')
                                ->orWhere('proposals.reference_number', 'like', '%' . $list_search . '%');
                        })->paginate($list_length);
        $data['proposalList'] = $proposalList;
        $data['list_length'] = $list_length;
        $data['list_search'] = $list_search;
        return view('legacy_dashboard', $data);
    }

    public function checkPanNo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email', 
        ],[
            'email.required' => 'Email id is required field.',
            'email.email' => 'Enter valid email id.',
        ]);
    
        if ($validator->fails()) {
            $allErrors = $validator->errors()->all();
            $allErrors = implode('<br>', $allErrors);
            return response()->json(['status' => 2, 'message' => $allErrors, 'pan_no' => '', 'agencyId' => 0]);
        }

        $userInfo = @AuthUser();
        $work_center_id = $userInfo->work_station;

        $email_id = $request->email;
        // where('user_type', 99)->
        //->where('agencies.work_center_id', $work_center_id)
        $agency = User::where('users.user_type', 99)->where('users.email', $email_id)
                    ->where('users.active_yn', 1)->where('agencies.agency_verified', 1)
                    ->leftjoin('agencies', 'users.user_pan_card_no', '=', 'agencies.pan_card_no')
                    ->first();

        $agencyId   = @$agency->id;
        $pan_no     = @$agency->user_pan_card_no;
        $allowForProposal     = @$agency->is_agency_allowed;
        $message = '';
        $status = 1;
        if($agencyId > 0){
            /*
            if($allowForProposal < 1){
                $status = 2;
                $message = "Agency is not allowed for submit the proposal.";
            }else{
                $status = 1;
                $message = "Allowed for proposal submission.";
            }*/
            $status = 1;
            $message = "Allowed for proposal submission.";
        }else{
            $status = 2;
            $message = "Agency is not exist.";
            $agencyId = 0;
        }

        return response()->json(['status' => $status, 'message' => $message, 'pan_no' => $pan_no, 'agency_id' => $agencyId]);
    }

    public function createNewProposal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_id' => 'required|email', 
            'pan_no' => 'required',
        ],[
            'email_id.required' => 'Email id is required field.',
            'email_id.email' => 'Enter valid email id.',
            'pan_no.required' => 'PAN no. is required field.',
        ]);
    
        if ($validator->fails()) {
            $allErrors = $validator->errors()->all();
            $allErrors = implode('<br>', $allErrors);
            return response()->json(['status' => 2, 'message' => $allErrors]);
        }

        $user_id            = @AuthUser()->id;
        $user_type          = @AuthUser()->user_type;

        $agency_id    = $request->agency_code;
        $email_id     = $request->email_id;
        $pan_no       = $request->pan_no;

        if($agency_id > 0){
            session(['user_id' => $agency_id, 'legacy_user_id' => $user_id]);
            $logs_login = new LogLoginController();
            $api_data = new Request([
                'description' => "login",
                'other_info' => "Legacy user login as agency. agency id - ".$agency_id,
                'login_by' => $user_id,
                'user_type' => $user_type,
            ]);
            $logsSave = $logs_login->store($api_data);
            $url = route('proposal.agency', ['ae' => 'add']);
            return response()->json(['status' => 1, 'message' => "Success", 'url' => $url]);
        }else{
            session()->forget(['user_id', 'legacy_user_id']);
            return response()->json(['status' => 2, 'message' => "This agency not exist. Please enter correct agency details.", 'url' => '']);
        }
        /*
        if($agency_id > 0){
            $agencyCheck = User::where('email', $email_id)->where('user_pan_card_no', $pan_no)
                        ->where('id', $agency_id)->exists();
            if($agencyCheck){
                session(['user_id' => $agency_id, 'legacy_user_id' => $user_id]);
                $logs_login = new LogLoginController();
                $api_data = new Request([
                    'description' => "login",
                    'other_info' => "Legacy user login as agency. agency id - ".$agency_id,
                    'login_by' => $user_id,
                    'user_type' => $user_type,
                ]);
                $logsSave = $logs_login->store($api_data);
                $url = route('proposal.agency', ['ae' => 'add']);
                return response()->json(['status' => 1, 'message' => "Success", 'url' => $url]);
            }else{
                return response()->json(['status' => 2, 'message' => "This agency not exist. Please enter correct agency details.", 'url' => '']);
            }
        }else{
            $validator2 = Validator::make($request->all(), [
                'pan_card' => 'required|file|mimes:pdf|max:2048', 
            ],[
                'pan_card.required' => 'Pan card file is required field.',
            ]);
        
            if ($validator2->fails()) {
                $allErrors = $validator2->errors()->all();
                $allErrors = implode('<br>', $allErrors);
                return response()->json(['status' => 2, 'message' => $allErrors, 'url' => '']);
            }
            if ($request->hasFile('pan_card')) {
                $directoryPath = $email_id.'_'.$pan_no;
                $directoryPath = preg_replace("/[^a-zA-Z0-9_]/", "", $directoryPath).'/';
                $file = $request->file('pan_card');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $newName = 'userpancard_' . time() . '.' . $extension;
                $file->storeAs($directoryPath, $newName);
                $folderPath = storage_path("app/" . $directoryPath);
                File::chmod($folderPath, 0755);
                $fileName = $directoryPath.$newName;
                $rowData = [];
                $rowData['email']               = $email_id;
                $rowData['user_pan_card_no']    = $pan_no;
                $rowData['user_pan_card']       = $fileName;
                $rowData['password']            = time();
                $rowData['user_type']           = 99;
                $rowData['created_at']          = date('Y-m-d H:i:s');
                $rowData['created_by']          = $user_id;
                //dd($rowData);
                $rowQuery = DB::table('users')->insertGetId($rowData);
               
                if ($rowQuery) {
                    $logs_login = new LogLoginController();
                    $api_data = new Request([
                        'description' => "create",
                        'other_info' => "Legacy user create agency & login as agency. agency id - ".$rowQuery,
                        'login_by' => $user_id,
                        'user_type' => 99,
                    ]);
                    $logsSave = $logs_login->store($api_data);
                    $url = route('proposal.agency', ['ae' => 'add']);
                    session(['user_id' => $rowQuery, 'legacy_user_id' => $user_id]);
                    return response()->json(['status' => 1, 'message' => "Success", 'url' => $url]);
                }else{
                    return response()->json(['status' => 2, 'message' => "Some problem occur. Please try again", 'url' => '']);
                }
            }else{
                return response()->json(['status' => 2, 'message' => "Please choose pan card file.", 'url' => '']);
            }
        }*/
    }
    
    public function progressImages($prop_id)
    {
        $proposal  = DB::table('proposals')->where('prop_id', $prop_id)->first();
        $proposal_user_id = $proposal->user_id;

        $user_id            = @AuthUser()->id;
        $user_type          = @AuthUser()->user_type;
        session(['user_id' => $proposal_user_id, 'legacy_user_id' => $user_id]);
        return redirect()->route('progress_images.index', ["prop_id" => $prop_id]);
    }

    public function actionProposal($ae, $prop_id)
    {
        $proposal  = DB::table('proposals')->where('prop_id', $prop_id)->first();
        $agency_id = $proposal->user_id;

        $user_id            = @AuthUser()->id;
        $user_type          = @AuthUser()->user_type;

        if($ae == 'edit'){
            session(['user_id' => $agency_id, 'legacy_user_id' => $user_id]);
            return redirect()->route('proposal.agency', ["ae" => "edit", "id" => $prop_id]);
        }else if($ae == 'delete'){
            $delQuery = DB::table('proposals')->where('prop_id', $prop_id)->delete();
            if ($delQuery) {
                $type = 'success';
                $message = 'Delete successfully';
                $findPQuery = DB::table('proposals_pincodes')->where('propp_id', $prop_id)->count();
                if($findPQuery > 0){
                    DB::table('proposals_pincodes')->where('propp_id', $prop_id)->delete();
                }
            } else {
                $type = 'error';
                $message = 'Not Deleted. Try again.';
            }
            return redirect()->route('legacy.dashboard')->with($type, $message);
        }else{
            $type = 'error';
            $message = 'Invalid action.';
            return redirect()->back()->with($type, $message);
        }
    }

    /**
    * Display view to pan-card form and get email as per the pan-card
    */
    public function getEmailList(Request $request)
    {
        $panCard = $request->pan_card ?? '';
      
        $getEmailLists = [];
        $data = [];
        $action_type = 'list';
        if ($panCard != null) {
            $getEmailLists = User::where('user_pan_card_no', $panCard)->where('user_type', 99)->get();
    
            if ($getEmailLists->isEmpty()) 
            { 
                // Redirect back with error message
                return redirect()->back()->with('error', 'Entered PAN Card does not exist.'); 
            }
            $action_type = 'search';
        }
       
        $data['getEmailLists'] = $getEmailLists;
        $data['panCard'] = $panCard;
        $data['action_type'] = $action_type;
        // Return to view
        return view('email_update', $data);
    }

    /**
        * Update email in database 
    */
    public function emailUpdate(Request $request)
    {
        $oldEmail = @$request->email_update;
        $newEmail = @$request->updated_email;
        $panCard  = @$request->pan_card_no;
        $userId = $request->userId;
        $remarks = @$request->remarks;
        $createBy = AuthUser()->id ?? 0;
        $ipAddress = request()->ip();
        $sessionId = session()->getId();
        $device_name = gethostname();
        $device_user_name = get_current_user();

        $file = $request->file('attachment');
        $pdfPath = '';

        if ($file) {
            // Validate the incoming request
            $request->validate([
                'attachment' => 'mimes:pdf|max:2048',
            ]);

            // Get the original name of the uploaded file
            $originalName = $file->getClientOriginalName();

            // Generate a unique filename with a timestamp and user ID
            $timestamp = now()->timestamp;
            $filename = 'pdf_' . $originalName . '_user_' . $userId . '_' . $timestamp . '.pdf';

            // Define the directory path
            $directory = public_path('emailChange');

            // Create the directory if it doesn't exist
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0777, true, true);
            }
            // Store the file in the emailChange directory with the generated filename
            $pdfPath = $file->storeAs('emailChange', $filename, 'public');
        }
    
        // Check if the new email already exists in the database
        $emailExists = User::where('email', $newEmail)->exists();

        if ($emailExists) {
            // If yes
            return redirect()->back()->with('error', 'This email is already exist.');
        } else {
            $checkEmail = User::where('email', $oldEmail)->first();
            if ($checkEmail->primary_ac == 1) {
                // Update on agency table
               $agencyData =  Agency::where('agency_ngo_pemailid', $oldEmail)->update(['agency_ngo_pemailid' => $newEmail]);
            }else{
                $checkEmailExists = Agency::where('agency_ngo_pemailid', $oldEmail)->where('pan_card_no', $panCard)->exists();

                if($checkEmailExists)
                {
                    $agencyData =  Agency::where('agency_ngo_pemailid', $oldEmail)->update(['agency_ngo_pemailid' => $newEmail]);
                }
            }
        
            $updateEmail = User::where('id', $userId)->update(['email' => $newEmail]);

            // Also save data on 'email_update' table
            $emailUpdate = new EmailUpdate();
            $emailUpdate->old_email = $oldEmail;
            $emailUpdate->new_email = $newEmail;
            $emailUpdate->pan_card = $panCard;
            $emailUpdate->type =  'email_change';
            $emailUpdate->remarks = $remarks;
            $emailUpdate->created_by = $createBy;
            $emailUpdate->attachment = $pdfPath;
            $emailUpdate->ip_address = $ipAddress;
            $emailUpdate->device_name = $device_name;
            $emailUpdate->device_user_name = $device_user_name;
            $emailUpdate->login_session_id = $sessionId;
            $emailUpdate->save();

            $findAgency = Agency::where('pan_card_no', $panCard)->first();
            if($findAgency){
                $agencyIid = $findAgency->agn_id;
                $logs = new LogController();
                $log_message = "Email address updated : ";
                $log_message .= "From old email <b>".$oldEmail."</b> to new email <b>".$newEmail."</b>";
                $api_data = new Request([
                    'log_message' => $log_message,
                    'user_id' => $createBy,
                    'agency_id' => $agencyIid,
                    'log_other' => 'agency'
                ]);
                $logsSave = $logs->agencyLog($api_data);
            }
        
            return redirect()->back()->with('success', 'Email Updated.');
        }
    }

    public function panUpdate(Request $request)
    {
        //dd($request->all());
        $oldPanNo = $request->pan_card_no ?? '';
        $newPanNo = $request->updated_pan ?? '';
        $panUpdate = $request->pan_update ?? 0;
        $panStatus = $request->pan_status ?? 2;
        $remarks = $request->remarks ?? '';
        
        $validator2 = Validator::make($request->all(), [
            'pan_update' => 'required|in:0,1', 
            'pan_status' => 'required|in:2,1', 
            'pan_card_no' => 'required|string|max:10|different:updated_pan|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'updated_pan' => 'required|string|max:10|different:pan_card_no|unique:users,user_pan_card_no|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            'remarks' => 'required|string|max:240',
            'pan_attachment' => 'required_if:pan_status,1'
        ],[
            'pan_update.required' => 'Please select pan update option.',
            'pan_update.in' => 'Invalid pan update option.',
            'pan_status.required' => 'Please select pan status option.',
            'pan_status.in' => 'Invalid pan status option.',
            'pan_card_no.required' => 'Please enter old pan no.',
            'pan_card_no.different' => 'Old pan no. & New pan no. will be different.',
            'pan_card_no.regex'    => 'Old pan no. is not valid.',
            'updated_pan.different' => 'Old pan no. & New pan no. will be different.',
            'updated_pan.unique' => 'This new pan no. is already exist.',
            'updated_pan.required' => 'New pan no. is required field.',
            'updated_pan.regex'    => 'Enter a valid PAN number (e.g., ABCDE1234F).',
            'remarks.required' => 'Remarks is required field.',
            'pan_attachment.required_if' => 'Please upload the pan card attachment file.',
        ]);
    
        if ($validator2->fails()) {
            $allErrors = $validator2->errors()->all();
            $allErrors = implode('<br>', $allErrors);
            return response()->json(['status' => 2, 'message' => $allErrors, 'url' => '']);
        }
        $createBy = AuthUser()->id ?? 0;
        $status = 2;
        $message = 'Action not performed.';
        $logError = '';
        try {
            DB::beginTransaction();
            if($panUpdate == 1) {
                $getPanLists = User::where('user_pan_card_no', $oldPanNo)->where('user_type', 99)
                                ->select('id', 'primary_ac', 'user_type', 'user_pan_card_no', 'user_pan_card', 'directory_name')->get();
                if($getPanLists->isEmpty()) 
                { 
                    return redirect()->back()->with('error', 'Entered old PAN Card does not exist.'); 
                }
                $panUserIds = $getPanLists->pluck('id')->all();
                $primaryP = $getPanLists->firstWhere('primary_ac', 1);
                if($primaryP){
                    $directory_name = $primaryP->directory_name;
                }else{
                    $directory_name = $getPanLists->first()->directory_name;
                }
                $panData = [];
                $panData['user_pan_card_no'] = $newPanNo;
                $panLog = "<br><br>Update PAN Number";
                if($panStatus == 1 && $request->hasFile('pan_attachment')) {
                    $directoryPath = $directory_name.'/';
                    $file = $request->file('pan_attachment');
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $newName = 'userpancard_' . time() . '.' . $extension;
                    $file->storeAs($directoryPath, $newName);
                    $folderPath = storage_path("app/" . $directoryPath);
                    File::chmod($folderPath, 0755);
                    $fileName = $directoryPath.$newName;
                    $panData['user_pan_card'] = $fileName;
                    $panLog .= " & File ";
                }else{
                    $fileName = '';
                    $panLog .= " Only ";
                }
                $updatePan = User::whereIn('id', $panUserIds)->update($panData);
                if($updatePan){
                    $checkAgency = Agency::where('pan_card_no', $oldPanNo)->first();
                    if($checkAgency){
                        $agencyIid = $checkAgency->agn_id;
                        Agency::where('pan_card_no', $oldPanNo)->update(['pan_card_no' => $newPanNo]);

                        $logs = new LogController();
                        $log_message = "PAN updated : ";
                        $log_message .= "From old pan <b>".$oldPanNo."</b> to new pan <b>".$newPanNo."</b>";
                        $api_data = new Request([
                            'log_message' => $log_message,
                            'user_id' => $createBy,
                            'agency_id' => $agencyIid,
                            'log_other' => 'agency'
                        ]);
                        $logsSave = $logs->agencyLog($api_data);
                    }

                    // Also save data on 'email_update' table
                    $createBy = @AuthUser()->id;
                    $ipAddress = request()->ip();
                    $sessionId = session()->getId();
                    $device_name = gethostname();
                    $device_user_name = get_current_user();

                    $emailUpdate = new EmailUpdate();
                    $emailUpdate->old_email = $oldPanNo;
                    $emailUpdate->new_email = $newPanNo;
                    $emailUpdate->pan_card = $oldPanNo;
                    $emailUpdate->type =  'pan_update';
                    $emailUpdate->remarks = $remarks.$panLog;
                    $emailUpdate->created_by = $createBy;
                    $emailUpdate->attachment = $fileName ?? '';
                    $emailUpdate->ip_address = $ipAddress;
                    $emailUpdate->device_name = $device_name;
                    $emailUpdate->device_user_name = $device_user_name;
                    $emailUpdate->login_session_id = $sessionId;
                    $emailUpdate->save();

                    DB::commit();
                    $status = 1;
                    $message = 'Pan no. updated successfully.';
                }else{
                    DB::rollBack();
                    $status = 2;
                    $message = 'Pan no. not updated. Try again.';
                }
            }else{
                $status = 2;
                $message = 'Pan no. not required to update.';
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $status = 2;
            $logError = "Error : ".$e->getMessage();
            $message = "Something went wrong. Please try again.";
        }
        $url = route('getEmailList');
        return response()->json(['status' => $status, 'message' => $message, 'error' => $logError, 'url' => $url]);
    }

    public function passwordIndex()
    {
        return view('password_change');
    }
    
    public function passwordChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|different:current_password',
            'confirm_password' => 'required|min:8',
        ],[
            'current_password.required' => 'Current password is required field.',
            'new_password.required' => 'New password is required field.',
            'new_password.min' => 'New password will be min 8 char long.',
            'new_password.different' => 'New password & Current password will be different.',
            'new_password.confirmed' => 'New password & Confirm password will be same.',
            'confirm_password.required' => 'Confirm password is required field.',
            'confirm_password.min' => 'Confirm password will be min 8 char long.',
        ]);
    
        if ($validator->fails()) {
            $allErrors = $validator->errors()->all();
            $allErrors = implode('<br>', $allErrors);
            $type = 'error';
            return redirect()->back()->withErrors($allErrors)->withInput();
        }
        
        $current_password    = $request->current_password;
        $new_password    = $request->new_password;  
        
        $user       = @AuthUser();
        $regularUserPsd = $user->password;
       
        $user_id    = $user->id;
        $sessionData = Session::get('userlogintype');
        if($sessionData == 'T')
        {
            if (Hash::check($new_password, $regularUserPsd)) {
                $validator = ['message' => 'Something went wrong, Please change your password'];
                return redirect()->back()->withErrors($validator)->withInput();
            }
        }

        if ($request->new_password !== $request->confirm_password) {
            return redirect()->back()->withErrors('New password and Confirm password do not match.')->withInput();
        }
        
        if (!Hash::check($current_password, $user->password)) {
            // Current password does not match the one in the database
            return redirect()->back()->withErrors('Current password is incorrect.')->withInput();
        }
        $hashedPassword = Hash::make($new_password);

        $rowData = [];
        $rowData['password'] = $hashedPassword;
        $rowData['updated_at'] = now();

        $runPassword = DB::table('users')->where('id', $user_id)->update($rowData);
        if($runPassword){
            $status = "success";
            $message = "Password change successfully.";
        }else{
            $status = 'error';
            $message = "Password not changed.";
        }
        return redirect()->route('password.index')->with([$status => $message]);
    }

    public function passwordReset($id)
    {
        $login_id       = @AuthUser()->id;
        $login_type     = @AuthUser()->user_type;

        $getUser = DB::table('users')->where('id', $id)->first();
        $userId = $getUser->id;
        $emailId = $getUser->email;
        $primary_email = $getUser->primary_email;
        /*
        if(trim($primary_email) != ''){
            $main_email = $primary_email;
            $email_type = 'official';
        }else{
            $main_email = $emailId;
            $email_type = 'login';
        }
        */
        $main_email = $emailId;
        $email_type = 'login';
        $main_email = strtolower($main_email);
        $loginFun = new LoginController();

        $api_data = new Request([
            'expiry' => 60 * 24 * 180,
            'frm_page' => 'reset',
            'emailid' => $main_email,
            'email_type' => $email_type
        ]);
        //echo '<pre>'; print_r($api_data); die;
        $resetPass = $loginFun->forgotPassword($api_data);
        $resetPass = $resetPass->getData();
        //echo '<pre>'; print_r($resetPass); die;
        $status = $resetPass->status;
        $message = $resetPass->message;
        $type = 'error';
        if($status == '1'){
            $type = 'success';
            $logs_login = new LogLoginController();
            $api_data = new Request([
                'description' => "password_reset_link",
                'other_info' => "Send the password reset link on email : ".$main_email." for user id : ".$userId,
                'login_by' => $login_id,
                'user_type' => $login_type,
            ]);
            $logsSave = $logs_login->store($api_data);
        }
        return redirect()->back()->with($type, $message);
    }

    /**
     * This function use to handle save temporary user details on database
    */
    public function tempsave(Request $request)
    {
        $login_id       = @AuthUser()->id;
        $id = $request->cd;
        $login_email = $request->login_email;
        $emailid = strtolower($request->temp_official_email);

        $validatedData = $request->validate([
            'temp_user_name' => 'required',
            'temp_official_email' => "required|email",
            'temp_phone_number' => "required",
            'temp_designation' => "required",
            'date_fr' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d', 
        ],
        [
            "temp_user_name.required" => "Name is required field.", 
            "temp_official_email.required" => "Official email is required field222.", 
            "temp_phone_number.required" => "Phone number is required field.", 
            "temp_designation.required" => "Designation is required field.", 
            "temp_cpf_no.required" => "CPF is required field.", 
            "date_fr.required" => "Date from is required field.", 
            "date_fr.date_format" => "Enter valid date format.", 
            "date_to.required" => "Date to is required field.", 
            "date_to.date_format" => "Enter valid date format."
        ]);

        $password = 'temp@123';
        $hashedPassword = Hash::make($password);

        $temp_user_name = $request->temp_user_name;
        $temp_official_email = $request->temp_official_email;
        $temp_phone_number = $request->temp_phone_number;
        $temp_designation = $request->temp_designation;
        $temp_cpf_no = $request->temp_cpf_no;
        $date_fr = $request->date_fr;
        $date_to = $request->date_to;
        $active_for = $request->active_for;

        $tempUsers = [
            'temp_user_name' => $temp_user_name,
            'temp_official_email' => $temp_official_email,
            'temp_phone_number' => $temp_phone_number,
            'temp_designation' => $temp_designation,
            'temp_cpf_no' => $temp_cpf_no,
            'date_fr' => $date_fr,
            'date_to' => $date_to,
            'temp_password' => $hashedPassword,
            'active_for' => $active_for
        ];

        $date_fr1 = date('d/m/Y', strtotime($date_fr));
        $date_to1 = date('d/m/Y', strtotime($date_to));

        $log_description = [];
        $log_description[] = "<b>User Name : </b>".$temp_user_name;
        $log_description[] = "<b>Official Email : </b>".$temp_official_email;
        $log_description[] = "<b>Phone Number : </b>".$temp_phone_number;
        $log_description[] = "<b>Designation : </b>".$temp_designation;
        if(trim($temp_cpf_no) != ''){
            $log_description[] = "<b>CPF No. : </b>".$temp_cpf_no;
        }
        $log_description[] = "<b>Login Date. : </b>".$date_fr1." - ".$date_to1;
        $log_description   = implode(', ', $log_description);
    
        if (Carbon::parse($date_fr)->lessThanOrEqualTo(Carbon::today())) {
            $updateRun = DB::table('users')->where('id', $id)->update($tempUsers);
        }
        else{
            return redirect()->back()->with('error', 'Update not allowed for future login date.');
        }

        if($updateRun){
            $login_link = route('login');
            $email = new EmailController();
            $content = '<!DOCTYPE html><html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Login Details</title>
                    </head>
                    <body style="font-family: Arial, sans-serif;">
                    <div style="width: 95%; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
                        <h2 style="color: #333; text-align: center;">CSR Login Details</h2>
                        <p style="color: #555; text-align: center;">Dear User, Your login details are: </p>
                        <p style="color: #555; text-align: center;"><b>Official Email-id : </b>'.$emailid.'</p>
                        <p style="color: #555; text-align: center;"><b>Login Email-id : </b>'.$login_email.'</p>
                        <p style="color: #555; text-align: center;"><b>Login Password : </b>'.$password.'</p>
                        <p style="color: #555; text-align: center;"><b>Login Link : </b><a href="'.$login_link.'" target="_blank" style="color:#6666CC;">CSR Login</a></p>
                        <br>
                        <p style="color: #555; text-align: center;">You can reset password after login.</p>
                        <br>
                        <p style="color: #555; text-align: center;">Thank you.</p>
                    </div></body></html>';
            $subject = "Login Details for Temporary User";
            $send = $email->sendMail($content, $subject, $emailid);

            $logs_login = new LogLoginController();
            $api_data = new Request([
                'description' => "temp_user",
                'other_info' => $log_description,
                'login_by' => $id,
                'user_type' => $login_id,
            ]);
            //  'user_type' => $login_id, - here we store user_id instead of user_type
            $logsSave = $logs_login->store($api_data);

            $msg = "Update successfully.";
        }else{
            $msg = "No new changes occurs.";
        }
    
        return redirect()->route('user_list.index')->with('success', $msg);
    }

    /**
     * This function handle deletion of temporary user
    */
    public function tempdelete($user_id)
    {
        echo $user_id;

        $tempUsers = [
            'temp_user_name' => null,
            'temp_official_email' => null,
            'temp_phone_number' => null,
            'temp_designation' => null,
            'temp_cpf_no' => null,
            'date_fr' => null,
            'date_to' => null,
            'temp_password' => null
        ];

        $updateRun = DB::table('users')->where('id', $user_id)->update($tempUsers);
        if($updateRun){
            $status = 'success';
            $msg = "Delete successfully.";
        }else{
            $status = 'error';
            $msg = "Not delete. Try again";
        }

        return redirect()->route('user_type.show', ['ae' => 'edit', 'id' => $user_id])->with($status, $msg);
    }
}
