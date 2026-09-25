<?php

namespace App\Http\Controllers;

use App\Models\ProposalHardCopy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Committe;
use App\Http\Controllers\EmailController;
use Illuminate\Support\Facades\Log;

class ProposalHardCopyController extends Controller
{
    /**
        * This function inplements to import sheet of hard copy
    */
    public function fileImport(Request $request)
{
    $data = [];

    $length = $request->length;

    if ($length < 50 || $length == '') {
        $length = session('length');
    }

    session(['length' => $length]);

    if (empty($length)) {
        $length = 100;
    }

    $token = $request->_token;

    if (!empty($token)) {

        // Validate CSV file
        $request->validate([
    'importHardCopyProposal' => 'required|file|extensions:csv|max:5120',
], [
    'importHardCopyProposal.required' => 'File is required field.',
    'importHardCopyProposal.extensions' => 'Only CSV format is supported.',
    'importHardCopyProposal.max' => 'Max file size is 5 MB.',
]);

        $file = $request->file('importHardCopyProposal');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->withErrors([
                'error' => 'File upload failed. Please try again.'
            ]);
        }

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {

            $header = fgetcsv($handle);

            $rowCount = 0;
            $successfulInserts = 0;
            $skippedRecords = [];
            $skippedRecordsCount = 0;

            Log::info("CSV Header: " . implode(',', $header ?: []));

            while (($row = fgetcsv($handle)) !== false) {

                $rowCount++;

                try {

                    Log::info("CSV Row: " . implode(',', $row));

                    // Ensure all 11 columns exist
                    $row = array_pad($row, 11, '');

                    // Sanitize CSV values
                    $receipt_date = trim(preg_replace('/\s+/', ' ', $row[0]));
                    $project_title = trim(preg_replace('/\s+/', ' ', $row[1]));
                    $project_cost = trim(preg_replace('/\s+/', ' ', $row[2]));
                    $project_schedule = trim(preg_replace('/\s+/', ' ', $row[3]));
                    $is_aspirational = trim(preg_replace('/\s+/', ' ', $row[4]));
                    $project_location = trim(preg_replace('/\s+/', ' ', $row[5]));
                    $mopng_reference = trim(preg_replace('/\s+/', ' ', $row[6]));
                    $refering_person = trim(preg_replace('/\s+/', ' ', $row[7]));
                    $implementing_agency = trim(preg_replace('/\s+/', ' ', $row[8]));
                    $vip_type = trim(preg_replace('/\s+/', ' ', $row[9]));
                    $fpr_name = trim(preg_replace('/\s+/', ' ', $row[10]));

                    // Convert date safely
                    $receipt_date = !empty($receipt_date)
                        ? date('Y-m-d', strtotime($receipt_date))
                        : null;

                    if (!empty($project_title)) {

                        // Check existing record
                        $existingHardCopyProposal = ProposalHardCopy::where(
                            'proposal_title',
                            $project_title
                        )->first();

                        // Delete previous record
                        if ($existingHardCopyProposal) {
                            ProposalHardCopy::where(
                                'proposal_title',
                                $project_title
                            )->delete();
                        }

                        // Prepare data for insertion
                        $data = [
                            'project_receipt_date' => $receipt_date,
                            'proposal_title' => $project_title,
                            'proposal_cost' => $project_cost,
                            'proposal_schedule' => $project_schedule,
                            'is_aspirrational_district' => $is_aspirational,
                            'project_location' => $project_location,
                            'mopng_reference' => $mopng_reference,
                            'referring_person_name' => $refering_person,
                            'implementing_agency' => $implementing_agency,
                            'fpr_name' => $fpr_name,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // Set vip_type only if MLA or MP
                        if (in_array($vip_type, ['MLA', 'MP'])) {
                            $data['vip_type'] = $vip_type;
                        }

                        // Insert record
                        try {
                            ProposalHardCopy::create($data);

                            $successfulInserts++;

                            Log::info("Record inserted successfully.");

                        } catch (\Exception $e) {

                            Log::error(
                                "Database insert failed - Error: " . $e->getMessage()
                            );

                            fclose($handle);

                            return redirect()->back()->withErrors([
                                'error' => 'Database insert failed: ' . $e->getMessage()
                            ]);
                        }

                    } else {

                        // Capture skipped records
                        $skippedRecords[] = [
                            'row' => $rowCount,
                            'project_name' => $project_title
                        ];

                        $skippedRecordsCount++;

                        Log::warning(
                            "Project title not found for row {$rowCount}"
                        );
                    }

                } catch (\Exception $e) {

                    Log::error(
                        "Unexpected error processing CSV row - Error: " . $e->getMessage()
                    );
                }
            }

            fclose($handle);

            // Final logging
            Log::info("Total rows processed: {$rowCount}");
            Log::info("Total records inserted successfully: {$successfulInserts}");
            Log::info("Total records skipped: {$skippedRecordsCount}");

            return redirect()->back()->with([
                'success' => "CSV file imported successfully. Processed {$rowCount} rows, inserted {$successfulInserts}, skipped {$skippedRecordsCount}",
            ]);

        } else {

            Log::error("Failed to open the CSV file.");

            return redirect()->back()->withErrors([
                'error' => 'Could not read the file. Please try again.'
            ]);
        }
    }

    // Get data from database
    $getHardCopyProposalDetails = ProposalHardCopy::paginate($length);

    $data['getHardCopyProposalDetails'] = $getHardCopyProposalDetails;

    // Return view
    return view('hard_copy_proposal', $data);
}

    /**
     * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $actionType = $request->action ?? 'filter';
        $filter_session_name = "proposal_hardcopy_filters";
        if($actionType == 'reset'){
            $request->session()->forget($filter_session_name);
            return redirect()->route('hard_copy_proposal');
        }

        $methodType = strtoupper($request->method());
        if($methodType == 'POST'){
            $inputDataAll = $request->all();
        }else{
            $inputDataAll = session($filter_session_name, []);
        }
        $inputData = (object) $inputDataAll;
        session()->put($filter_session_name, $inputDataAll);
        $data = [];

        $length         = $inputData->length ?? 100;
        $proposalTitle  = $inputData->proposal_title ?? '';
        $agencyTitle    = $inputData->agency_name ?? '';
        $is_mopng       = $inputData->is_mopng ?? '';
        $vip_type       = $inputData->vip_person_name ?? '';
        $mp_name        = $inputData->mp_name ?? '';
        $mla_name       = $inputData->other_vip_name ?? '';
        $vip_commitee   = $inputData->vip_commitee ?? '';

        // Get data from database
        $getHardCopyProposalDetails =  ProposalHardCopy::when(!empty(trim($proposalTitle)), function ($query) use ($proposalTitle) {
                return $query->where('proposal_title', 'like', '%' . trim($proposalTitle) . '%');
            })
            ->when(!empty(trim($agencyTitle)), function ($query) use ($agencyTitle) {
                return $query->where('implementing_agency', 'like', '%' . trim($agencyTitle) . '%');
            })
            ->when(trim($is_mopng) != '', function ($query) use ($is_mopng) {
                if ($is_mopng == '1') {
                    // Filter for mopng districts
                    $query->where('mopng_reference', '=', 'Yes');
                } elseif ($is_mopng == '0') {
                    // Filter for no mopng districts
                    $query->where('mopng_reference', '=', 'No');
                }
            })
            ->when(trim($vip_type) != '', function ($query) use ($vip_type) {
                if ($vip_type == 'MP') {
                    $query->where('vip_type', '=', 'MP');
                } elseif ($vip_type == 'MLA') {
                    $query->where('vip_type', '=', 'MLA');
                }
                elseif ($vip_type == 'Other') {
                    $query->where('vip_type', '=', 'Other');
                }
            })
            ->when(!empty(trim($mp_name)), function ($query) use ($mp_name) {
                return $query->where('mp_id', 'like', '%' . trim($mp_name) . '%');
            })
            ->when(!empty(trim($mla_name)), function ($query) use ($mla_name) {
                return $query->where('referring_person_name', 'like', '%' . trim($mla_name) . '%');
            })
            ->when(trim($vip_commitee) != '', function ($query) use ($vip_commitee) {
                return $query->whereRaw("FIND_IN_SET(?, commitee_id)", [$vip_commitee]);
            })
            ->paginate($length);

        
        $data['getHardCopyProposalDetails'] = $getHardCopyProposalDetails;
        $data['searchData']   = 1;
        $data['inputData'] = (array) $inputData;
        $data['mp_name'] = $mp_name;
        $data['vip_commitee'] = $vip_commitee;

        // Return view
        return view('hard_copy_proposal')->with($data);
    }

    /**
        * This function inplements to create forms for hard copy
    */
    public function create()
    {
        // Keep the legacy helper as the source of truth for committee dropdown values.
        $getCommiteeDetails = getCommiteeList();

        $data['getCommiteeDetails'] = $getCommiteeDetails;

        // Return view
        return view('add_hard_copy')->with($data);
    }

    /**
        * This function inplements to save form data 
    */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'proposal_title' => 'required',
                'project_receipt_date' => 'required',
                'proposal_cost' => 'required',
            ], [
                'proposal_title.required' => 'Proposal Titleis a required field.',
                'project_receipt_date.required' => 'Project Receipt Date is a required field.',
                'proposal_cost.required' => 'Proposal cost is a required field.',
            ]);
            
            if ($validator->fails()) {
                $allErrors = $validator->errors()->all();
                $allErrors = implode('<br>', $allErrors);
                return response()->json(['status' => 2, 'message' => $allErrors]);
            }

            $file = $request->file('proposal_pdf');
            $pdfPath = '';

            if ($file) {
                // Validate the incoming request
                $request->validate([
                    'proposal_pdf' => 'mimes:pdf|max:51200',
                ]);
    
                // Get the original name of the uploaded file
                $originalName = $file->getClientOriginalName();
    
                // Generate a unique filename with a timestamp and user ID
                $timestamp = now()->timestamp;
                $filename = 'pdf_' . $originalName . $timestamp . '.pdf';
    
                // Define the directory path
                $directory = public_path('hardCopy');
    
                // Create the directory if it doesn't exist
                if (!File::exists($directory)) {
                    File::makeDirectory($directory, 0777, true, true);
                }
                // Store the file in the emailChange directory with the generated filename
                $pdfPath = $file->storeAs('hardCopy', $filename, 'public');
            }
           
            $hardCopy = new ProposalHardCopy(); 
            $hardCopy->proposal_title            = $request->proposal_title;
            $hardCopy->agency_email              = $request->agency_email;
            $hardCopy->project_receipt_date      = $request->project_receipt_date;
            $hardCopy->proposal_cost             = $request->proposal_cost;
            $hardCopy->is_aspirrational_district = $request->is_aspirrational_district; 
            $hardCopy->project_location          = $request->project_location;
            $hardCopy->mopng_reference           = $request->mopng_reference;
            $hardCopy->mp_id                     = $request->mp_id ?? 0;
            $hardCopy->referring_person_name     = $request->referring_person_name;
            $hardCopy->vip_type                  = $request->vip_type;
            $hardCopy->implementing_agency       = $request->implementing_agency;
            $hardCopy->fpr_name                  = $request->fpr_name;
            $hardCopy->district                  = $request->district;
            $hardCopy->proposal_status           = $request->proposal_status;
            $hardCopy->remarks                   = $request->remarks;

            if($request->has('proposal_schedule'))
            {
                $hardCopy->proposal_schedule = implode(',', $request->proposal_schedule);
            }
            if ($request->has('commitee_id')) {
                $hardCopy->commitee_id = implode(',', $request->commitee_id);
            }
            
            $hardCopy->proposal_pdf = $pdfPath;
            $hardCopy->save();

            $senderEmail = $hardCopy->agency_email;

            if (!empty($senderEmail) && class_exists(\App\Http\Controllers\EmailController::class))
            {
                $email = new EmailController();
                $emailMessage = "Kindly note that proposals in hard copy or through emails are not considered. </b> Hence, you are requested to register the agency on ONGC-CSR portal for necessary proposal submission. </b> https://ongccsr.co.in/";
                $emailContent = '<!DOCTYPE html><html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Proposal Submission on CSR Portal</title>
                        </head>
                        <body style="font-family: Arial, sans-serif;">
                        <div style="width: 95%; margin: 0 auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
                            <h2 style="color: #333; text-align: center;">Proposal Submission on CSR Portal</h2>
                            <p style="color: #555; text-align: center;">' . $emailMessage . '</p>
                            <br><br>
                            <p style="color: #555; text-align: center;">Thank you.</p>
                        </div></body></html>';
                $subject = "Proposal Submission on CSR Portal";
                $send = $email->sendMail($emailContent, $subject, $senderEmail);
            }
            elseif (!empty($senderEmail))
        {
            Log::warning('EmailController not found — skipped sending proposal-submission email.', [
                'proposal_id' => $hardCopy->id,
                'to' => $senderEmail,
            ]);
        }

            $status = 1;
            $message = 'Saved successfully.';
            return response()->json(['status' => $status, 'message' => $message, 'error' => '']);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => 'Error saving data.', 'error' => $e->getMessage()]);
        }
    }

    /**
        * This function inplements to edit form data 
    */
    public function edit($id)
    {
        $getHardCopyDetails = ProposalHardCopy::where('id', $id)->first();

        // Match the legacy project behavior: use the helper fallback if the table is empty.
        $getCommiteeDetails = getCommiteeList();

        $data['getHardCopyDetails'] = $getHardCopyDetails;
        $data['getCommiteeDetails'] = $getCommiteeDetails;

        // Check if data is retrieved successfully
        if (!$data) {
            abort(404); 
        }

        // Return view
        return view('edit_hard_copy')->with($data);
    }

    /**
        * This function inplements to update form data 
    */
    public function update(Request $request, $id)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'proposal_title' => 'required',
            'project_receipt_date' => 'required',
            'proposal_cost' => 'required',
        ], [
            'proposal_title.required' => 'Proposal Title is a required field.',
            'project_receipt_date.required' => 'Project Receipt Date is a required field.',
            'proposal_cost.required' => 'Proposal cost is a required field.',
        ]);

        if ($validator->fails()) {
            $allErrors = implode('<br>', $validator->errors()->all());
            return response()->json(['status' => 2, 'message' => $allErrors]);
        }

        try {
            $hardCopy = ProposalHardCopy::findOrFail($id);

            // Handle file upload
            if ($request->hasFile('proposal_pdf')) {
                // Delete the existing file if it exists
                if ($hardCopy->proposal_pdf && Storage::exists('public/' . $hardCopy->proposal_pdf)) {
                    Storage::delete('public/' . $hardCopy->proposal_pdf);
                }

                // Upload the new file
                $file = $request->file('proposal_pdf');
                $filename = 'pdf_' . now()->timestamp . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('hardCopy', $filename, 'public');

                // Update the file path in the model
                $hardCopy->proposal_pdf = $path;
            }

            // Update fields
            $fields = [
                'proposal_title','agency_email', 'project_receipt_date', 'proposal_cost',
                'proposal_schedule', 'is_aspirrational_district', 'project_location',
                'mopng_reference', 'referring_person_name', 'vip_type', 
                'implementing_agency', 'fpr_name', 'district', 'remarks', 'mp_id', 'proposal_status'
            ];

            foreach ($fields as $field) {
                if ($request->filled($field)) {
                    $hardCopy->$field = $request->$field;
                }
            }

            // Handle `vip_type` and associated fields
            if ($request->filled('vip_type')) {
                $hardCopy->vip_type = $request->vip_type;

                if ($request->vip_type === 'MP') {
                    // Save `mp_id` and clear `referring_person_name`
                    $hardCopy->mp_id = $request->filled('mp_id') ? $request->mp_id : null;
                    $hardCopy->referring_person_name = null;
                } elseif ($request->vip_type === 'MLA' || $request->vip_type === 'Other') {
                    // Save `referring_person_name` and clear `mp_id`
                    $hardCopy->referring_person_name = $request->filled('referring_person_name') ? $request->referring_person_name : null;
                    $hardCopy->mp_id = 0;
                } else {
                    // Clear both fields for "Other" or invalid `vip_type`
                    $hardCopy->mp_id = 0;
                    $hardCopy->referring_person_name = null;
                }
            }

            // Handle committee IDs
            if ($request->has('commitee_id')) {
                $hardCopy->commitee_id = !empty($request->commitee_id) 
                    ? implode(',', $request->commitee_id) 
                    : null; // Clear the field if no committees are selected
            } else {
                // Explicitly clear the field if `commitee_id` is not in the request
                $hardCopy->commitee_id = null;
            }

            // Handle Schedule
            if ($request->has('proposal_schedule')) {
                $hardCopy->proposal_schedule = !empty($request->proposal_schedule) 
                    ? implode(',', $request->proposal_schedule) 
                    : null; 
            } else {
                $hardCopy->proposal_schedule = null;
            }

            // Save the updated model
            $hardCopy->save();

            return response()->json(['status' => 1, 'message' => 'Updated successfully.', 'error' => '']);
        } catch (\Exception $e) {
            Log::error('Error updating ProposalHardCopy: ' . $e->getMessage());
            return response()->json(['status' => 0, 'message' => 'Error saving data.', 'error' => $e->getMessage()]);
        }
    }
}
