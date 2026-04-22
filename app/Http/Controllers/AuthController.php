<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Services\App\AuthService;
use App\Services\Json\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function register_user(Request $request)
    {
        try {
            $register_user = $request->validate([
                "name" => "required|string|max:50",
                "email" => "required|email|max:100",
                "password" => "required|string|min:8|max:255"
            ]);

            return $this->authService->register_user($register_user);

        } catch (ValidationException $e) {
            return JsonResponse::unprocessable_entity('Unprocessable Entity', $e->errors());
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['exception' => $e]);
            return JsonResponse::internal_server_error('Something went wrong', null);
        }
    }

    public function login_user(Request $request)
    {
        try {
            $request->validate([
                "email" => "required|email|max:100",
                "password" => "required|string|min:8|max:255"
            ]);

            return $this->authService->login_user($request);
        } catch (ValidationException $e) {
            return JsonResponse::unprocessable_entity('Unprocessable Entity', $e->errors());
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['exception' => $e]);
            return JsonResponse::internal_server_error('Something went wrong', $e->getMessage());
        }
    }

    public function forgot_password(Request $request)
    {
        try {
            $user_email = $request->validate([
                "email" => "required|email|max:100"
            ]);

            return $this->authService->forgot_password($user_email);
        } catch (ValidationException $e) {
            return JsonResponse::unprocessable_entity('Unprocessable Entity', $e->errors());
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['exception' => $e]);
            return JsonResponse::internal_server_error('Something went wrong', $e->getMessage());
        }
    }

    public function reset_password(Request $request)
    {
        try {
            $forgot_opt = $request->validate([
                "email" => "required|email|max:100",
                "otp_code" => "required|string|max:100",
                "password" => "required|string|min:8|max:255"
            ]);

            return $this->authService->reset_password($forgot_opt);
        } catch (ValidationException $e) {
            return JsonResponse::unprocessable_entity('Unprocessable Entity', $e->errors());
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['exception' => $e]);
            return JsonResponse::internal_server_error('Something went wrong', $e->getMessage());
        }
    }

    public function logout_user(Request $request)
    {
        try {
            return $this->authService->logout_user($request);
        } catch (ValidationException $e) {
            return JsonResponse::unprocessable_entity('Unprocessable Entity', $e->errors());
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['exception' => $e]);
            return JsonResponse::internal_server_error('Something went wrong', $e->getMessage());
        }
    }

    public function current_user(Request $request)
    {
        try {
            $user = $request->user();
            if ($user === null) {
                return JsonResponse::unauthorized('Unauthenticated', null);
            }

            return JsonResponse::success('User fetched', [
                'name' => $user->name,
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage(), ['exception' => $e]);
            return JsonResponse::internal_server_error('Something went wrong', $e->getMessage());
        }
    }
}

?>
