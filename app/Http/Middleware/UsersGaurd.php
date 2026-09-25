<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\Functions;
use Illuminate\Support\Facades\Http;

class UsersGaurd
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
        $active_yn          = @$user->active_yn;
        //$user_vendor_code   = $user->vendor_code;
        //echo '123'; print_r($user); die;
        if($user_user_type == 5){
            //// for fpr 
            $user_pan_card_no = 'NAA';
        }
        if($user_id <= 0 || $user_id == null || $user_pan_card_no == null){
            return redirect()->route('login');
        }
        $pgArray = ['proposal.index'];
        if($user_user_type == 99 && $active_yn == 1 && in_array($currentPageName, $pgArray)){
            return redirect()->route('proposal.dashboard_user');
        }
        return $next($request);
    }
}
