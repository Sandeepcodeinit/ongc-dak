<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Exception;
use App\Models\Proposal;
use App\Models\DishaToCsr;

class DishaActionController extends Controller
{
    public function pvFind(Request $request)
    {
        $pv_no = trim($request->pv_no ?? '');
        $pan_no = trim($request->pan_no ?? '');
        $from_dt = trim($request->from_dt ?? '');
        $to_dt = trim($request->to_dt ?? '');
        $applyFiltersCount = 0;
        $applyFiltersList = [];

        $queryProposal = Proposal::query()->where('final_save', 1)->where('pv_status', 2);

        if ($pv_no) {
            $queryProposal = $queryProposal->where('pv_number', $pv_no);
            $applyFiltersCount++;
            $applyFiltersList['pv_no'] = $pv_no;
        }
        if ($pan_no) {
            $pan_no = strtoupper($pan_no);
            $queryProposal = $queryProposal->whereHas('userProfile', function ($q) use ($pan_no) {
                $q->where('user_pan_card_no', $pan_no);
            });
            $applyFiltersCount++;
            $applyFiltersList['pan_no'] = $pan_no;
        }
        if ($from_dt && $to_dt && strtotime($from_dt) > 10000 && strtotime($to_dt) > 10000) {
            $queryProposal = $queryProposal->whereBetween(DB::raw('DATE(updated_at)'), [$from_dt, $to_dt]);
            $applyFiltersCount++;
            $applyFiltersList['from_dt'] = $from_dt;
            $applyFiltersList['to_dt'] = $to_dt;
        }else{
            if($from_dt && strtotime($from_dt) > 10000){
                $queryProposal = $queryProposal->whereDate('updated_at', '=', $from_dt);
                $applyFiltersCount++;
                $applyFiltersList['from_dt'] = $from_dt;
            }
            if($to_dt && strtotime($to_dt) > 10000){
                $queryProposal = $queryProposal->whereDate('updated_at', '=', $to_dt);
                $applyFiltersCount++;
                $applyFiltersList['to_dt'] = $to_dt;
            }
        }
        if($applyFiltersCount <= 0){
            $queryProposal = $queryProposal->limit(300);
        }
        $queryProposal = $queryProposal->select('prop_id', 'user_id', 'pv_number', 'pv_status', 'pv_created_at', 'reference_number', 'created_at', 'updated_at');
        $queryProposal = $queryProposal->with(['userProfile:id,user_name,email,user_pan_card_no']);
        $queryProposal = $queryProposal->orderBy('updated_at', 'desc');
        $queryProposal = $queryProposal->get();

        $result = $queryProposal->map(function ($proposal) {
            $pv_created_at = $proposal->pv_created_at ?? null;
            if($pv_created_at){
                $pv_created_at = date('Y-m-d\TH:i:sP', strtotime($pv_created_at));
            }
            return [
                'pv_number'  => $proposal->pv_number,
                //'pan_no' => $proposal->userProfile?->user_pan_card_no ?? 'NA',
                'created_at' => $pv_created_at,
            ];
        })->values();

        $resultCount = count($result) ?? 0;
        if($resultCount > 0){
            $jsonStatus = 200;
            $status = 1;
            $message = 'Records found.';
            $result_status = true;
            $result_data = $result;
        }else{
            $jsonStatus = 404;
            $status = 0;
            $message = 'No records found.';
            $result_status = false;
            $result_data = [];
        }

        $date = date('c');
        // Option 2: Explicit format string
        $date = date('Y-m-d\TH:i:sP');

        return response()->json([
            'status' => $status,
            'message' => $message,
            'exists' => $result_status,
            'data' => $result_data,
            'total_records' => $resultCount,
            'filters' => $applyFiltersList ?? NULL,
            'response_time' => $date,
        ], $jsonStatus);
    }

    public function sendToCsr(Request $request)
    {
        //dd($request->all());
        $status = 0;
        $message = 'No Action Performed.';
        $result_status = false;
        $result_data = [];
        $jsonStatus = 500;
        try {
            $validator = Validator::make($request->all(), [
                'pv_number'  => 'required|string|max:50|exists:proposals,pv_number',
                'disha_data' => 'required|array',

                'disha_data.aa_date'  => 'nullable|date_format:Y-m-d',
                'disha_data.fc_date'  => 'nullable|date_format:Y-m-d',
                'disha_data.es_date'  => 'nullable|date_format:Y-m-d',
                'disha_data.moa_date' => 'nullable|date_format:Y-m-d',

                'disha_data.moa_amount' => 'nullable|numeric',
            ], [
                'pv_number.required'  => 'PV Number is required.',
                'pv_number.string'  => 'PV Number must be a valid string.',
                'pv_number.max'  => 'PV Number must not exceed 50 characters.',
                'pv_number.exists'   => 'The provided PV Number does not exist.',
                'disha_data.required' => 'Disha Data is required.',
                'disha_data.array' => 'Disha Data must be a valid json array.',
                'disha_data.aa_date.date_format'  => 'AA Date must be in YYYY-MM-DD format.',
                'disha_data.fc_date.date_format'  => 'FC Date must be in YYYY-MM-DD format.',
                'disha_data.es_date.date_format'  => 'ES Date must be in YYYY-MM-DD format.',
                'disha_data.moa_date.date_format' => 'MOA Date must be in YYYY-MM-DD format.',

                'disha_data.moa_amount.required_if' => 'MOA Amount is required when MOA Date is provided.',
                'disha_data.moa_amount.numeric'     => 'MOA Amount must be a valid number.',
            ]);

            $validator->after(function ($validator) use ($request) {

                $allowedKeys = [
                    'aa_date',
                    'fc_date',
                    'es_date',
                    'moa_date',
                    'moa_amount',
                ];

                $dishaData = $request->input('disha_data', []);

                /*
                |--------------------------------------------------------------------------
                | Only allow predefined keys
                |--------------------------------------------------------------------------
                */
                $extraKeys = array_diff(array_keys($dishaData), $allowedKeys);

                if (!empty($extraKeys)) {
                    $validator->errors()->add(
                        'disha_data',
                        'Invalid key(s): ' . implode(', ', $extraKeys)
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | MOA Date & MOA Amount must be provided together
                |--------------------------------------------------------------------------
                */
                $moaDateProvided = isset($dishaData['moa_date'])
                    && $dishaData['moa_date'] !== null
                    && $dishaData['moa_date'] !== '';

                $moaAmountProvided = isset($dishaData['moa_amount'])
                    && $dishaData['moa_amount'] !== null
                    && $dishaData['moa_amount'] !== '';

                if ($moaDateProvided !== $moaAmountProvided) {
                    $validator->errors()->add(
                        'disha_data',
                        'MOA Date and MOA Amount must be provided together.'
                    );
                }
            });

            if ($validator->fails()) {
                $allErrors = $validator->errors();
                $allErrorsString = implode('<br>', $allErrors->all());
                return response()->json([
                    'status' => 0,
                    'message' => $allErrorsString,
                    'result_status' => false,
                    //'result_data' => $allErrors,
                ], 400);
            }
            DB::beginTransaction();
            $pv_no = trim($request->pv_number ?? '');
            $disha_data = $request->disha_data ?? [];
            $disha_file_number = trim($request->disha_file_number ?? NULL);

            $dishaToCsr = DishaToCsr::where('pv_number', $pv_no)->first();
            $isRecordUpdate = 0;
            if ($dishaToCsr) {
                $existingData = $dishaToCsr->disha_data ?? [];
                // Make sure it is an array
                if (!is_array($existingData)) {
                    $existingData = json_decode($existingData, true) ?? [];
                }

                // Check if any requested key already exists
                $existingKeys = array_intersect(
                                    array_keys($disha_data),
                                    array_keys($existingData)
                                );
                if (!empty($existingKeys)) {
                    $keys = array_values($existingKeys);
                    $keysStr = implode(', ', $keys);
                    return response()->json([
                        'status' => 0,
                        'message' => 'Conflict: The key (' . $keysStr . ') already exist.',
                        'result_status' => false,
                        'result_data' => $existingData,
                    ], 409);
                }
                $dishaData = array_merge($existingData, $disha_data);
                //$dishaData = array_unique($dishaData);
                
                $dishaToCsr->disha_data = $dishaData;
                $dishaToCsr->action = 2; // Updated
                $dishaToCsr->update_count += 1;
                $dishaToCsr->updated_at = now();
                $isRecordUpdate = 1;
            }else{
                $dishaToCsr = new DishaToCsr();
                $dishaToCsr->pv_number = $pv_no;
                $dishaToCsr->disha_data = $disha_data;
                $dishaToCsr->action = 1; // New
                $dishaToCsr->update_count = 0;
                $isRecordUpdate = 0;
            }
            $dishaToCsr->disha_file_number = $disha_file_number;
            $runQuery = $dishaToCsr->save();
            if ($runQuery) {
                DB::commit();
                $jsonStatus = 200;
                $status = 1;
                $message = $isRecordUpdate > 0 ? 'Record updated successfully.' : 'Record saved successfully.';
                $result_status = true;
            } else {
                DB::rollBack();
                $jsonStatus = 500;
                $status = 0;
                $message = 'Failed to save record.';
                $result_status = false;
            }
        } catch (Throwable $th) {
            DB::rollBack();
            $jsonStatus = 500;
            $status = 0;
            $message = 'An error occurred while processing the request.';
            $result_data = $th->getMessage();
            $result_status = false;
        } catch (Exception $e) {
            DB::rollBack();
            $jsonStatus = 500;
            $status = 0;
            $message = 'An error occurred while processing the request.';
            $result_data = $e->getMessage();
            $result_status = false;
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'result_status' => $result_status,
            'result_data' => $result_data,
        ], $jsonStatus);
    }

    public function getDishaRecords(Request $request)
    {
        $pv_no = trim($request->pv_no ?? '');
        $dishaGetQuery = DishaToCsr::query();
        if(!empty($pv_no)) {
            $dishaGetQuery = $dishaGetQuery->where('pv_number', $pv_no);
        }
        $dishaGetRun = $dishaGetQuery->limit(100)
                        ->select('pv_number', 'disha_data', 'disha_file_number', 'created_at', 'updated_at')
                        ->orderBy('dtc_id', 'desc')->get();
        if($dishaGetRun->count() > 0) {
            $status = 1;
            $message = 'Success';
            $result_count = $dishaGetRun->count();
            $result_data = $dishaGetRun->toArray();
            $jsonStatus = 200;
        }else{
            $status = 0;
            $message = 'No record found.';
            $result_data = [];
            $result_count = 0;
            $jsonStatus = 404;
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
            'total_count' => $result_count,
            'data' => $result_data,
        ], $jsonStatus);
    }
}
