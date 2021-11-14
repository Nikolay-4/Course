<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Payload;

class CheckAuthToken
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        try {
            app(JWTAuth::class)->setToken($token);
            /** @var Payload $payload */
            $payload = app(JWTAuth::class)->getPayload();
            $pubId = $payload->get('public_id');
            $roles = $payload->get('roles');
            $user = User::query()->where('public_id', '=', $pubId)->firstOrFail();
            $user->roles = $roles;
            auth()->setUser($user);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(array('message' => 'token_expired'), 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(array('message' => 'token_invalid'), 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(array('message' => 'token_absent'), 401);
        }

        return $next($request);
    }
}
