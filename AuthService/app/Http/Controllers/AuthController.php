<?php

namespace App\Http\Controllers;

use App\Http\Requests\Registration\EmailRegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'registration']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
        $user->save();
//        event(new Registered($user));


        $exchange = 'router';
        $queue = 'msgs';

        $connection = new AMQPStreamConnection(
            config('queue.rabbitmq.host'),
            config('queue.rabbitmq.port'),
            config('queue.rabbitmq.user'),
            config('queue.rabbitmq.password'),
            config('queue.rabbitmq.vhost')
        );
        $channel = $connection->channel();

        $channel->queue_declare($queue, false, true, false, false);

        $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

        $channel->queue_bind($queue, $exchange);

        $messageBody = [
            'name' => 'user created',
            'data' => $user
        ];
        $message = new AMQPMessage(json_encode($messageBody), array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $channel->basic_publish($message, $exchange);

        $channel->close();
        $connection->close();

        return response()->json(['message' => 'Письмо с подтверждением было отправлено на ваш почтовый ящик'], 201);
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
