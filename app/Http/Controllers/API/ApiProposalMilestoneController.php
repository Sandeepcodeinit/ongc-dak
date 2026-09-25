<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Proposal;
use App\Models\Agency;
use App\Models\ProposalAmtMilestone;
use App\Models\ProgressImages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class ApiProposalMilestoneController extends Controller
{
    // /
    public function proposalList(Request $request)
    {
        $status = 500;
        $message = "";
        $result = [];
        try {    
            $user = $request->user();
            $userId = $user->id;
            $userPan = $user->user_pan_card_no;
            $max_milestone = env('MAX_INPUT_MILESTONE');
            $max_image = env('MAX_IMG_FILE');

            $result['user_id'] = $userId;
            $result['pan_no'] = $userPan;
            $result['max_milestone'] = $max_milestone;
            $result['max_image'] = $max_image;
            $proposal_list = Proposal::select('prop_id', 'agency_name', 'project_title', 'reference_number', 'pv_number', 'disha_id', 'project_id', 'disha_verify_yn')
                                        ->where('user_id', $userId)->where('final_save', 1)->where('propsal_submit_by', 0)
                                        ->get();

            $proposal_listCnt = $proposal_list->count();
            if($proposal_listCnt > 0){
                $status = 200;
                $message = "Record found.";
                $result['proposal_list'] = $proposal_list;
            }else{
                $status = 500;
                $message = "Record not found.";
                $result['proposal_list'] = null;
            }
            return response()->json(['status' => $status, "message" => $message, "result" => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, "message" => $e->getMessage()]);
        }
    }

    
    public function proposalMilestone(Request $request)
    {
        $prop_id = $request->prop_id;
        //$prop_id = 85;
        $status = 500;
        $message = "";
        $result = [];
        try {    
            $milestones = Proposal::select('prop_id', 'agency_name', 'project_title', 'reference_number', 'pv_number', 'disha_id', 'project_id', 'disha_verify_yn')
                                ->with('milestones')
                                ->where('prop_id', $prop_id)->where('final_save', 1)
                                ->first();
            if($milestones){
                $message = "Milestone found.";
                $milestonesCnt = $milestones->milestones->count();
                if($milestonesCnt <= 0){
                    $message = "No milestone found.";
                }
                $status = 200;
            }else{
                $result[] = null;
                $message = "No record found.";
            }    
            return response()->json(['status' => $status, "message" => $message, "result" => $milestones]);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, "message" => $e->getMessage()]);
        }
    }

    
    public function milestoneImages(Request $request)
    {
        $prop_id = $request->prop_id;
        $grant_sr_no = $request->sr_no;
        //$prop_id = 85;
        $status = 500;
        $message = "";
        $result = [];
        try {   
            $images = ProgressImages::select('pi_id', 'pi_milestone_sr_no', 'pi_image', 'pi_verify_status', 'pi_verify_by_remarks')
                                ->where('pi_milestone_sr_no', $grant_sr_no)->where('pi_proposal_id', $prop_id)->where('pi_delete_yn', 0)
                                ->get();
             $imagesCnt = $images->count();
             if($imagesCnt > 0){
                $result['total_images'] = $imagesCnt;
                $result['all_images'] = $images;
                $status = 200;
                $message = "Image found.";
             }else{
                $status = 500;
                $message = "Image not found.";
                $result = null;
             }

            return response()->json(['status' => $status, "message" => $message, "result" => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, "message" => $e->getMessage()]);
        }
    }
    
    public function milestoneImageDel(Request $request)
    {
        //$prop_id = $request->prop_id;
        $pi_id = $request->pi_id;
        $status = 500;
        $message = "";
        try {   
            $images = ProgressImages::find($pi_id);
            if($images){
                $images->pi_updated_at  = date('Y-m-d H:i:s');
                $images->pi_delete_yn   = 1;
                $imagesRun = $images->save();
                if($imagesRun){
                    $status = 200;
                    $message = "Image deleted.";
                }else{
                    $message = "Image not delete.";
                }
            }else{
                $message = "Image not found.";
            }
            return response()->json(['status' => $status, "message" => $message]);
        } catch (\Exception $e) {
            return response()->json(['status' => 500, "message" => $e->getMessage()]);
        }
    }
    
    public function milestoneImageSave(Request $request)
    {
        $status = 500;
        $message = "";
        try {   
            $validator = Validator::make($request->all(), [
                'images' => 'required',  // Ensure 'file' is an array
                'milestone_sr_no' => 'required|integer',
                'proposal_id' => 'required|integer',
                'latitude' => 'required',
                'longitude' => 'required',
            ],[
                "images.required" => "Image is required field.",
                "milestone_sr_no.required" => "Milestone sr. no. is required field.",
                "proposal_id.required" => "Select an proposal.",
                "latitude.required" => "Latitude is required field.",
                "longitude.required" => "Longitude is required field."
            ]);
            if ($validator->fails()) {
                $allErrors = $validator->errors()->all();
                $allErrors = implode('<br>', $allErrors);
                return response()->json(['status' => 2, 'message' => $allErrors]);
            }
            $session_id = "werwr";
            $ipAddress = $request->ip_address ?? NULL;
            $device_name = $request->device_name ?? NULL;
            $device_user_name = $request->device_user_name ?? $device_name;
            $proposal_id = $request->proposal_id;
            $sr_no = $request->milestone_sr_no;
            $images = $request->images;
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $date_time = $request->date_time;
            $max_file = env('MAX_IMG_FILE');
            
            if ($images && $proposal_id > 0) {
                $userData = DB::table('proposals')->where('prop_id', $proposal_id)
                            ->leftJoin('users', 'proposals.user_id', '=', 'users.id')
                            ->first();

                $today = date('Y-m-d H:i:s');
                $user_id        = $userData->id;
                $user_email_id  = $userData->email;
                $user_pan_no    = $userData->user_pan_card_no;
                $project_id     = $userData->project_id ?? null;
                $directory_name = $userData->directory_name ?? '';
                if(!empty($date_time)){
                    $created_at     = date($date_time);
                }else{
                    $created_at     = $today;
                }
                $upload_through = 1;
                //dd($userData);
                //$directoryPath  = $user_email_id . '_' . $user_pan_no;
                //$directoryPath  = preg_replace("/[^a-zA-Z0-9_]/", "", $directoryPath) . '/';
                $directoryPath  = $directory_name . '/';

                $storagePath = storage_path("app/" . $directoryPath);
                $directoryLink  = asset("storage/app/" . $directoryPath) . '/';
                $field_name = "milestone_img_sr".$sr_no."_prop".$proposal_id;

                $queryFind = ProgressImages::where('pi_milestone_sr_no', $sr_no)
                                ->where('pi_proposal_id', $proposal_id)->where('pi_delete_yn', 0);
                $totlPics = $queryFind->count();
                $remain_pics = $max_file - $totlPics;

                $imagesAll = count($images);
                if($imagesAll > $remain_pics){
                    $message = "You have only ".$remain_pics." files allowd to upload. You upload more file, not saved.";
                    return response()->json(['status' => 2, 'message' => $message]);
                }
                $saveAllImages = [];
                foreach ($images as $key => $base64Image){
                    /*
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $file_name = $newName = $field_name.'_' . $user_id . '_' . time() . '_'.rand(10,99).'.' . $extension;
                    $file->storeAs($directoryPath, $newName);
                    */
                    $base64Image = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
                    // Decode the base64 string into binary data
                    $image_base64 = base64_decode($base64Image);
                    // Define a file path to store the image
                    $file_name = $newName = $field_name.'_' . $user_id . '_' . time() . '_'.rand(10,99).'.jpg';
                    // Use Laravel's Storage facade or save directly to public folder
                    $filePath = $directoryLink . $newName;

                    $fileStoragePath = $storagePath . $newName;
                    file_put_contents($fileStoragePath, $image_base64);
                    
                    if (file_exists($fileStoragePath)) {
                        chmod($fileStoragePath, 0666);
                    }
                    $savaData = [];
                    $savaData['pi_milestone_sr_no'] = $sr_no;
                    $savaData['pi_image'] = $filePath;
                    $savaData['pi_proposal_id'] = $proposal_id;
                    $savaData['pi_project_id'] = $project_id;
                    $savaData['pi_created_at'] = $created_at;
                    $savaData['pi_ip_address'] = $ipAddress;
                    $savaData['pi_session_id'] = $session_id;
                    $savaData['pi_device_name'] = $device_name;
                    $savaData['pi_device_user_name'] = $device_user_name;
                    $savaData['latitude'] = $latitude;
                    $savaData['longitude'] = $longitude;
                    $savaData['upload_through'] = $upload_through;
                    $saveAllImages[] = $savaData;
                }
                $saveQuery = ProgressImages::insert($saveAllImages);
                if($saveQuery){
                    $exception = "saved";
                    $status = 200;
                    $message = 'File uploaded successfully.';
                }else{
                    $exception = "failed";
                    $status = 500;
                    $message = 'File not uploaded.';
                }
                return response()->json([
                    'status' => $status,
                    'message' => $message
                ]);
            }else{
                return response()->json([
                    'status' => 500,
                    'message' => 'File upload failed.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 500, "message" => $e->getMessage()]);
        }
    }
}