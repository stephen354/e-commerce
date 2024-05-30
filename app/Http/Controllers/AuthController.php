<?php

namespace App\Http\Controllers;

use App\Http\Middleware\LoginMiddleware;
use App\Http\Requests\CustomerLoginRequest;
use App\Models\Customer;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function login(Request $request)
    {

        // dd($request);
        // $data = $request->validated();

        // $credentials = $request->only('email', 'password');

        // if (!$token = Auth::guard('api')->attempt($credentials)) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Email atau Password Anda salah'
        //     ], 401);
        // }

        // $customer = Customer::where('email', $data['email'])->first();

        // $customer->token_login = $token;

        // $customer->save();

        Session::put("login", TRUE);
        // Session::put("token", $token);
        // \Illuminate\Support\Facades\Cookie::queue("token", $token);
        return redirect('/api/documentation');
    }

    public function logout()
    {

        $removeToken =  Cookie::get('token');
        Cookie::queue('token', null);
        if ($removeToken) {
            $user_data = Customer::where('token_login', Session::get('token'))->first();
            $user_data['token_login'] = null;
            $user_data->save();

            Session::flush();
            return redirect('/login');
        }
    }
}
