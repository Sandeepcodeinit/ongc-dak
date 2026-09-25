<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\Functions;
use Illuminate\Support\Facades\Http;

class GeneralGaurd
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
        if($user_id <= 0 || $user_id == null || empty($user_id) || empty($user_user_type)){
            //dd($user_id);
            Session::flush();
            Auth::logout();
            @session()->forget('user_id');
            return '';
        }else{
            if($user_user_type == 99){
                //return '/dashboard/my';
				return redirect('/dashboard/my');
            }else {
                //return ;
				return redirect('/home');
            }
        }
        return $next($request);
    }
}
