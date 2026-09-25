<?php

use App\Models\MpList;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;

if (!function_exists('AuthUser')) {
    function AuthUser()
    {
        $sessionUser = session('user');
        $userId = data_get($sessionUser, 'id', session('user_id'));

        if (!$userId) {
            return null;
        }

        session(['user_id' => $userId]);

        if (is_object($sessionUser) || is_array($sessionUser)) {
            return (object) $sessionUser;
        }

        return DB::table('users')->where('id', $userId)->first();
    }
}

if (!function_exists('userDetails')) {
    function userDetails($userId, $userType = '')
    {
        $sessionData = Session::get('userlogintype');

        if (empty($userId)) {
            $sessionUser = session('user');
            $userId = data_get($sessionUser, 'id', session('user_id'));
        }

        if (!$userId) {
            return null;
        }

        $query = DB::table('users')->where('users.id', $userId);

        if (Schema::hasTable('role_types')) {
            $query->leftJoin('role_types', 'users.user_type', '=', 'role_types.role_id');
        }

        if ($userType == 'T') {
            $select = [
                DB::raw("CONCAT(users.temp_user_name, ' [', 'Delegate', ']') as user_name"),
                'users.temp_designation as designation',
                'users.temp_cpf_no',
                'users.temp_phone_number',
                'users.temp_official_email',
            ];

            if (Schema::hasTable('role_types')) {
                $select[] = 'role_types.role_type';
            } else {
                $select[] = 'users.user_type as role_type';
            }

            return $query->select($select)->first();
        }

        if (Schema::hasTable('role_types')) {
            return $query->select('users.*', 'role_types.role_type')->first();
        }

        return $query->select('users.*', 'users.user_type as role_type')->first();
    }
}

if (!function_exists('scheduleViiList')) {
    function scheduleViiList($id = '')
    {
        $scheduleList = App\Models\ScheduleVii::query()
            ->select('sc_viis_id', 'sc_viis', 'active_yn')
            ->where('active_yn', 1);

        if ($id > 0) {
            return $scheduleList->where('sc_viis_id', $id)->first();
        }

        return $scheduleList->get();
    }
}

if (!function_exists('getRouteUrls')) {
    function getRouteUrls($middleware = 'adminGaurd')
    {
        $routes = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            if (in_array($middleware, $route->middleware(), true)) {
                $routes[] = $route->uri();
            }
        }

        return $routes;
    }
}

if (!function_exists('getMpList')) {
    function getMpList($id = 0, $active_status = 1)
    {
        if ($id > 0) {
            return MpList::query()
                ->where('id', $id)
                ->where('active_status', $active_status)
                ->first();
        }

        return MpList::query()
            ->whereNotNull('vip_name')
            ->where('vip_name', '!=', '')   
            ->where('active_status', 1)
            ->orderBy('vip_name', 'ASC')
            ->get();
    }
}
if (!function_exists('getCommiteeList')) {
    function getCommiteeList() 
    { 
        
        $commiteeLists = DB::table('committes')->orderBy('committee_name', 'ASC')->where('active_status', 1)->get();
        return $commiteeLists;
    }
}

if (!function_exists('committeeList')) {
    function committeeList($from = null, $id = null)
    {
        $from = strtoupper($from ?? 'MASTER');

        if ($from === 'MASTER') {
            $getDetails = App\Models\Committee::query()
                ->where('active_status', 1)
                ->get();

            if ($id > 0) {
                return $getDetails->firstWhere('id', $id) ?: null;
            }

            return $getDetails;
        }

        $getDetails = DB::table('vip_committees')
            ->select('committes.id', 'committes.committee_name')
            ->where('vip_committees.active_status', 1)
            ->leftJoin('committes', 'vip_committees.committe_id', '=', 'committes.id')
            ->distinct()
            ->get();

        if ($id > 0) {
            return $getDetails->firstWhere('id', $id) ?: null;
        }

        return $getDetails;
    }
}

if (!function_exists('getAgencyList')) {
    function getAgencyList($id = 0, $active_status = 1)
    {
        $defaultAgencyList = collect([
            (object) ['id' => 1, 'agency_name' => 'ONGC', 'agency_email' => 'info@ongc.in', 'active_status' => 1],
            (object) ['id' => 2, 'agency_name' => 'Indian Oil', 'agency_email' => 'contact@indianoil.in', 'active_status' => 1],
            (object) ['id' => 3, 'agency_name' => 'Coal India', 'agency_email' => 'info@coalindia.in', 'active_status' => 1],
            (object) ['id' => 4, 'agency_name' => 'Bharat Petroleum', 'agency_email' => 'support@bharatpetroleum.in', 'active_status' => 1],
            (object) ['id' => 5, 'agency_name' => 'NTPC', 'agency_email' => 'helpdesk@ntpc.co.in', 'active_status' => 1],
        ]);

        if ($id > 0) {
            $record = DB::table('agencies')->where('agn_id', $id)->first();
            if ($record) {
                return $record;
            }

            return $defaultAgencyList->firstWhere('id', $id) ?: null;
        }

        $records = DB::table('agencies')
            ->whereNotNull('agency_name')
            ->where('agency_name', '!=', '')
            ->select('agn_id as id', 'agency_name', DB::raw('COALESCE(agency_ngo_pemailid, "") as agency_email'), 'agency_verified as active_status')
            ->orderBy('agency_name', 'ASC')
            ->get();

        if ($records->isNotEmpty()) {
            return $records;
        }

        return $defaultAgencyList;
    }
}

if (!function_exists('agencyList')) {
    function agencyList($from = null, $id = null)
    {
        $from = strtoupper($from ?? 'MASTER');

        if ($from === 'MASTER') {
            $getDetails = DB::table('agencies')
                ->whereNotNull('agency_name')
                ->where('agency_name', '!=', '')
                ->select('agn_id as id', 'agency_name', DB::raw('COALESCE(agency_ngo_pemailid, "") as agency_email'))
                ->get();

            if ($getDetails->isEmpty()) {
                $getDetails = collect([
                    (object) ['id' => 1, 'agency_name' => 'ONGC'],
                    (object) ['id' => 2, 'agency_name' => 'Indian Oil'],
                    (object) ['id' => 3, 'agency_name' => 'Coal India'],
                    (object) ['id' => 4, 'agency_name' => 'Bharat Petroleum'],
                    (object) ['id' => 5, 'agency_name' => 'NTPC'],
                ]);
            }

            if ($id > 0) {
                return $getDetails->firstWhere('id', $id) ?: null;
            }

            return $getDetails;
        }

        $getDetails = DB::table('agencies')
            ->whereNotNull('agency_name')
            ->where('agency_name', '!=', '')
            ->select('agn_id as id', 'agency_name', DB::raw('COALESCE(agency_ngo_pemailid, "") as agency_email'))
            ->orderBy('agency_name', 'ASC')
            ->get();

        if ($getDetails->isEmpty()) {
            $getDetails = collect([
                (object) ['id' => 1, 'agency_name' => 'ONGC'],
                (object) ['id' => 2, 'agency_name' => 'Indian Oil'],
                (object) ['id' => 3, 'agency_name' => 'Coal India'],
                (object) ['id' => 4, 'agency_name' => 'Bharat Petroleum'],
                (object) ['id' => 5, 'agency_name' => 'NTPC'],
            ]);
        }

        if ($id > 0) {
            return $getDetails->firstWhere('id', $id) ?: null;
        }

        return $getDetails;
    }
}

if (!function_exists('getCommiteeName')) {
    function getCommiteeName($committeeIds = '')
    {
        if (empty($committeeIds)) {
            return null;
        }

        $committeeIdArray = array_filter(array_map('trim', explode(',', $committeeIds)), 'strlen');
        if (empty($committeeIdArray)) {
            return null;
        }

        $committeeNames = DB::table('committes')
            ->whereIn('id', $committeeIdArray)
            ->where('active_status', 1)
            ->pluck('committee_name')
            ->toArray();

        return implode(', ', $committeeNames);
    }
}


if (!function_exists('proposalStatus')) {
    function proposalStatus()
    { 
        $proposalStatus = [
            1 => "Agency under registration",
            2 => "Proposal send for civil vetting",
            3 => "Proposal under finance vetting",
            4 => "Proposal submitted for approval of competent authority",
            5 => "Approved",
            7 => "DISHA proposal under preparation",
            6 => "Not Considered"
        ];

        return $proposalStatus;
    }
}

if (!function_exists('proposalHardCopyStatusList')) {
    function proposalHardCopyStatusList()
    {
        return proposalStatus();
    }
}

if (!function_exists('proposalScheduleList')) {
    function proposalScheduleList($id = '')
    {
        $query = App\Models\ScheduleVii::query()
            ->select('sc_viis_id', 'sc_viis', 'schedule_name', 'active_yn')
            ->where('active_yn', 1);

        if ($id !== '' && $id !== null && (int) $id > 0) {
            return $query->where('sc_viis_id', (int) $id)->first();
        }

        return $query->orderBy('sc_viis_id', 'ASC')->get();
    }
}

if (!function_exists('formatIndianRupees')) {
    function formatIndianRupees($amount = 0, $type = '')
    {
        $amount = (float) $amount;

        if ($type === 'Y') {
            return '₹ ' . number_format($amount, 2, '.', ',');
        }

        if ($amount == 0) {
            return '₹ 0';
        }

        return '₹ ' . number_format($amount, 2, '.', ',');
    }
}

if (!function_exists('agencyDetails')) {
    function agencyDetails($value = '', $type = 'PAN')
    {
        if (empty($value) || !Schema::hasTable('agencies')) {
            return (object) ['chief_verified_status' => 0];
        }

        $columns = Schema::getColumnListing('agencies');
        $query = DB::table('agencies');

        if (strtoupper($type) === 'PAN') {
            $term = trim((string) $value);
            $hasPan = in_array('pan_card_no', $columns, true);
            $hasGst = in_array('pan_gst', $columns, true);
            $hasUserPanCard = in_array('user_pan_card_no', $columns, true);
            $hasUserPanGst = in_array('user_pan_gst', $columns, true);

            if ($hasPan || $hasGst || $hasUserPanCard || $hasUserPanGst) {
                $query->where(function ($q) use ($term, $columns) {
                    foreach (['pan_card_no', 'pan_gst', 'user_pan_card_no', 'user_pan_gst'] as $field) {
                        if (in_array($field, $columns, true)) {
                            $q->orWhere($field, $term);
                        }
                    }
                });
            } else {
                return (object) ['chief_verified_status' => 0];
            }
        } else {
            $query->where('agency_name', 'like', '%' . $value . '%');
        }

        $agency = $query->first();

        if (!$agency) {
            return (object) ['chief_verified_status' => 0];
        }

        return (object) [
            'chief_verified_status' => (int) ($agency->chief_verified_status ?? $agency->agency_verified ?? 0),
            'agency_name' => $agency->agency_name ?? '',
            'agency_email' => $agency->agency_ngo_pemailid ?? $agency->agency_email ?? '',
            'data' => $agency,
        ];
    }
}

if (!function_exists('getAgencyAllowed')) {
    function getAgencyAllowed($pan = '')
    {
        if (empty($pan) || !Schema::hasTable('agencies')) {
            return 0;
        }

        $columns = Schema::getColumnListing('agencies');
        $query = DB::table('agencies');

        $hasPan = in_array('pan_card_no', $columns, true);
        $hasGst = in_array('pan_gst', $columns, true);
        $hasUserPanCard = in_array('user_pan_card_no', $columns, true);
        $hasUserPanGst = in_array('user_pan_gst', $columns, true);

        if (!$hasPan && !$hasGst && !$hasUserPanCard && !$hasUserPanGst) {
            return 0;
        }

        $query->where(function ($q) use ($pan, $columns) {
            foreach (['pan_card_no', 'pan_gst', 'user_pan_card_no', 'user_pan_gst'] as $field) {
                if (in_array($field, $columns, true)) {
                    $q->orWhere($field, $pan);
                }
            }
        });

        return $query->exists() ? 1 : 0;
    }
}

if (!function_exists('checkAgency')) {
    function checkAgency($email = '')
    {
        if (empty($email) || !Schema::hasTable('agencies')) {
            return false;
        }

        $columns = Schema::getColumnListing('agencies');
        $query = DB::table('agencies');
        $hasMatch = false;

        foreach (['agency_ngo_pemailid', 'agency_email', 'email'] as $field) {
            if (in_array($field, $columns, true)) {
                $query->orWhere($field, $email);
                $hasMatch = true;
            }
        }

        if (!$hasMatch) {
            return false;
        }

        return $query->exists();
    }
}

if (!function_exists('notificationCountDetails')) {
    function notificationCountDetails()
    {
        return [
            'notificationCount' => 0,
            'notifications' => collect(),
        ];
    }
}
