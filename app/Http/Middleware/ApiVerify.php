<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class ApiVerify extends BaseMiddleware
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        try{
            $user = JWTAuth::parseToken()->authenticate();
        }catch(\Excpetion $error){
            if($error instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['error' => 'Invalid Token'], 401);
            }else if($error instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['error' => 'Expired Token'], 401);
            }else{
                return response()->json(['error' => 'Token Not Found'], 401);
            }
        }
      
        return $next($request);
    }
}
