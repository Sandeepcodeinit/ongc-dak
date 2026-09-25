<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\API\Functions;
use Illuminate\Support\Facades\Http;

class LegacyGaurd
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
        $user_type          = @$user->user_type;
        //$user_vendor_code   = $user->vendor_code;
        //print_r($user);
        if($user_id <= 0 || $user_id == null || $user_type <> 7){
            //print_r($user_id.'---'.$user_user_type); die;
            return redirect()->route('login');
        }
        session(['user_id' => $user_id]);
        return $next($request);
    }
}
