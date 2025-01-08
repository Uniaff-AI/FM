<?php
namespace App\Users\Controllers\Authentication;

use App\Users\DTO\CreateUserData;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Users\Actions\CreateNewUserAction;
use App\Users\Requests\RegisterUserRequest;
use Illuminate\Contracts\Auth\StatefulGuard;

class RegisterUserController extends Controller
{
    public function __construct(
        protected CreateNewUserAction $createNewUser,
        protected StatefulGuard $guard,
    ) {
    }

    public function __invoke(RegisterUserRequest $request): JsonResponse
    {
        // Проверка, включена ли регистрация
        if (! intval(get_settings('registration'))) {
            return response()->json([
                'type'    => 'error',
                'message' => 'User registration is not allowed',
            ], 401);
        }

        // Маппинг данных регистрации
        $data = CreateUserData::fromArray([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => $request->input('password'),
            // Удалите, если не используется
            // 'role' => $request->input('role'),
            // 'oauth_provider' => $request->input('oauth_provider'), // если не требуется
            // 'avatar' => $request->input('avatar'), // если не требуется
        ]);

        // Регистрация пользователя
        try {
            $user = ($this->createNewUser)($data);
        } catch (\Exception $e) { // Общий перехват исключений
            \Log::error('Registration failed: ' . $e->getMessage());
            return response()->json([
                'type'    => 'error',
                'message' => 'User registrations are temporarily disabled',
            ], 409);
        }

        event(new Registered($user));

        // Вход пользователя, если верификация отключена
        if (! $user->password || ! intval(get_settings('user_verification'))) {
            $this->guard->login($user);
        }

        return response()->json([
            'type'    => 'success',
            'message' => 'User successfully registered.',
        ], 201);
    }
}
