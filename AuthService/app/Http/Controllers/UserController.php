<?php

namespace Auth\Http\Controllers;

use Auth\Events\EventService;
use Auth\Events\UserCreatedEvent;
use Auth\Events\UserDeletedEvent;
use Auth\Events\UserUpdatedEvent;
use Auth\Http\Requests\UserUpdateRequest;
use Auth\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UserUpdateRequest $request)
    {

        $user = $request->user();
        $user->update($request->validated());

        $eventService = new EventService('userCUD');
        $event = UserUpdatedEvent::fromUserData([
            'publicId' => $user->public_id,
            'email' => $user->email,
            'name' => $user->name,
        ], 'AuthService');
        $eventService->emit($event->toArray());

        return response()->json(auth()->user());
    }

    public function delete(Request $request)
    {
        $user = $request->user();
        $user->delete();
        $eventService = new EventService('userCUD');
        $event = UserDeletedEvent::fromUserData([
            'publicId' => $user->public_id,
            'email' => $user->email,
            'name' => $user->name,
        ], 'AuthService');
        $eventService->emit($event->toArray());
        return response(status: 204);
    }


    public function registration(Request $request)
    {
        $input = $this->validate($request, [
            'email' => ['required', 'email', 'unique:users'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ]);
        $user = new User();
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->password = Hash::make($input['password']);
        $user->public_id = uniqid(more_entropy: true);
        $user->save();

        $eventService = new EventService('registered');
        $event = UserCreatedEvent::fromUserData([
            'publicId' => $user->public_id,
            'email' => $user->email,
            'name' => $user->name,
        ], 'AuthService');
        $eventService->emit($event->toArray());

        return response()->json(['message' => 'User registered'], 201);
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }


}
