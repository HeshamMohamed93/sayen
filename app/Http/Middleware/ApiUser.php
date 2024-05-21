<?php

namespace App\Http\Middleware;
use Closure;
use Auth;
use JWTAuth;

class ApiUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public $attributes;

    public function handle($request, Closure $next, $guard='api-users')
    {
        if($user = Auth::guard($guard)->user())
        {
            if($user->phone_verified == '0')
            {
                return response()->json(['message' => trans('api.not_verified_account'), 'code' => 401]);
            }
            else if($user->active == '0')
            {
                return response()->json(['message' => trans('api.not_active_account'), 'code' => 400]);
            }
            else
            {
                $request->merge(array("user" => $user));            
            }
        }
        else
        {
            return response()->json(['message' => trans('api.user_not_exist'), 'code' => 400]);
        }

        return $next($request);
    }
}
