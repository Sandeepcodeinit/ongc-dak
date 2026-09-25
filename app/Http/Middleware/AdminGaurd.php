<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\Functions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminGaurd
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $currentPageName = Route::currentRouteName();
        $user = AuthUser();
        $user_id            = @$user->id;
        $user_user_type     = @$user->user_type;
        $user_pan_card_no   = @$user->user_pan_card_no;
        //$user_vendor_code   = $user->vendor_code;
        //print_r($user);
        if($user_id <= 0 || $user_id == null || $user_user_type < 0 || $user_user_type > 10){
            //print_r($user_id.'---'.$user_user_type); die;
            return redirect()->route('login');
        }
        if($user_user_type > 0 && $user_user_type < 11){
            $pageRoute = Route::currentRouteName();
            $pageAllow = [];
            
            $pageAllow[] = "milestone_pie";
            $pageAllow[] = "old_milestone_pie";
            $pageAllow[] = "old_milestone_bar";
            $pageAllow[] = "old_milestone.details";
            $pageAllow[] = "proposal.apv";
            $pageAllow[] = "todo.save";
            $pageAllow[] = "col.field";
            $pageAllow[] = "doc.verification";
            $pageAllow[] = "doc.verify_list";
            $pageAllow[] = "agency.view";
            $pageAllow[] = "proposal.all";
            $pageAllow[] = "proposal.view";
            $pageAllow[] = "agency.index";
            $pageAllow[] = "agency.apv";
            $pageAllow[] = "agency.show";
            $pageAllow[] = "doc.agency_submit_docs";
            $pageAllow[] = "getMpListDetails";
            $pageAllow[] = "address.list";
            $pageAllow[] = "notification.list";
            $pageAllow[] = "address.list";
            $pageAllow[] = "password.index";
            $pageAllow[] = "password.change";
            $pageAllow[] = "password.reset";
            $pageAllow[] = "expired_doc.view";
            $pageAllow[] = "expired_doc.assign";
            $pageAllow[] = "expired_doc.forward";
            $pageAllow[] = "hard_copy_proposal";
            /* Above is common for all admin users */
            
            switch ($user_user_type) {
                case 1:
                    /// DHR
                    $pageAllow[] = "dashboard.reports";
                    $pageAllow[] = "dashboard.admin";
                    $pageAllow[] = "grant_amt.import";
                    $pageAllow[] = "csr.expire";
                    $pageAllow[] = "dashboard.pieGraph";
                    $pageAllow[] = "dashboard.lineGraph";
                    $pageAllow[] = "dashboard.barGraph";
                    $pageAllow[] = "proposal.all";
                    $pageAllow[] = "proposal.view";
                    $pageAllow[] = "agency.index";
                    $pageAllow[] = "agency.apv";
                    $pageAllow[] = "agency.show";
                    $pageAllow[] = "agency.view";
	                $pageAllow[] = "dashboard.tableDetail";
                    $pageAllow[] = "old_milestone.import";
                    $pageAllow[] = "agencyPie";
                    $pageAllow[] = "proposalPie";
                break;
                case 5:
                case 6:
                    /// FPR & Implants
                    $pageAllow[] = "doc.submit_docs";
                    $pageAllow[] = "doc.submit_agency_docs";
                    $pageAllow[] = "doc.agency_submit_docs";
                    $pageAllow[] = "agency.edit";
                    $pageAllow[] = "proposal.fpr_edit";
                    $pageAllow[] = "activity.store";
                    $pageAllow[] = "activity.svaeStatus";
                    $pageAllow[] = "activity.svaeFprStatus";
                    $pageAllow[] = "activity.svaeStatus_agency";
                    $pageAllow[] = "fuc.edit";
                    $pageAllow[] = "fuc.save";
                    $pageAllow[] = "grant_amt.add";
                    $pageAllow[] = "grant_amt.save";
                    $pageAllow[] = "grant_amt.edit";
                    $pageAllow[] = "progress_images.verify";
                    $pageAllow[] = "images_verify.update";
                    $pageAllow[] = "expired_doc.communicate";
                    $pageAllow[] = "doc_re_edit";
                    $pageAllow[] = "save.mopng";
                    $pageAllow[] = "proposal.current_status";
                    $pageAllow[] = "zip.allFilesTest";
                break;
                case 4:
                    /// TL
                    $pageAllow[] = "agency.assign";
                    $pageAllow[] = "fpr.list";
                    $pageAllow[] = "proposal.assign";
                break;
                case 3:
                    /// Cheif
                    $pageAllow[] = "agency.assign";
                    $pageAllow[] = "fpr.list";
                    $pageAllow[] = "proposal.assign";
                    $pageAllow[] = "agency.workCenterAssign";
                    $pageAllow[] = "agency.isAllowed";
                    $pageAllow[] = "wc.reassign";
                    $pageAllow[] = "save.mopng";
                    $pageAllow[] = "getEmailList";
                    $pageAllow[] = "emailUpdate";
                    $pageAllow[] = "panUpdate";
                    $pageAllow[] = "grant_amt.import";
                    $pageAllow[] = "grant_amt.import_save";
                    $pageAllow[] = "getEmailListFind";
                    $pageAllow[] = "dashboard.pieGraph";
                    $pageAllow[] = "dashboard.lineGraph";
                    $pageAllow[] = "dashboard.barGraph";
                    $pageAllow[] = "dashboard.mapChart";
                    $pageAllow[] = "dashboard.admin";
		            $pageAllow[] = "dashboard.tableDetail";
                    $pageAllow[] = "hard_copy_proposal";
                    $pageAllow[] = "hard_copy_proposal.add";
                    $pageAllow[] = "hard_copy_proposal.store";
                    $pageAllow[] = "hard_copy_proposal.edit";
                    $pageAllow[] = "hard_copy_proposal.update";
                    $pageAllow[] = "proposal.apv";
                break;

                case 9:
                    /// Audit
                    $pageAllow[] = "proposal.apv";
                    $pageAllow[] = "agency.apv";
                    $pageAllow[] = "proposal.view";
                    $pageAllow[] = "agency.index";
                    $pageAllow[] = "agency.show";
                break;

                case 8:
                    /// Hard copy
                    $pageAllow[] = "hard_copy_proposal";
                    $pageAllow[] = "hard_copy_proposal.add";
                    $pageAllow[] = "hard_copy_proposal.store";
                    $pageAllow[] = "hard_copy_proposal.edit";
                    $pageAllow[] = "hard_copy_proposal.update";
                break;

                case 8:
                    /// Asset Basin Manager
                    $pageAllow[] = "proposal.all";
                    $pageAllow[] = "proposal.view";
                    $pageAllow[] = "agency.index";
                break;

                default:
                # code...
                break;
            }

            if (!in_array($pageRoute, $pageAllow)) {
                $message = "Unauthorised access";
                Log::channel('file_access')->info('Unauthorised access : Route - '.$pageRoute. ', User Type - '.$user_user_type);
                if($request->ajax()){
                    return response()->json(['status' => 2, 'message' => $message]);
                }
                return redirect()->route('proposal.all')->with("error", $message);
            }
        }
        session(['user_id' => $user_id]);
        return $next($request);
    }
}
