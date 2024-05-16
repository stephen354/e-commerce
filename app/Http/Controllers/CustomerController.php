<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerLoginRequest;
use App\Http\Requests\CustomerRegisterRequest;
use App\Models\Customer;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(CustomerRegisterRequest $request)
    {
        $data = $request->validated();

        if (Customer::where('email', $data['email'])->count() == 1) {
            throw new HttpResponseException(response([
                "errors" => [
                    "email" => [
                        "email already registered"
                    ]
                ]
            ]), 400);
        }

        $customer = new Customer($data);
        $customer->password = Hash::make($data['password']);
        $customer->save();

        return $this->success($customer);
    }
    public function login(CustomerLoginRequest $request)
    {
        $data = $request->validated();

        $customer = Customer::where('email', $data['email'])->first();
        if (!$customer || !hash::check($data['password'], $customer->password)) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "username or password wrong"
                    ]
                ]
            ], 401));
        }
        $customer->token_login = Str::uuid()->toString();
        $customer->save();

        return $this->success($customer);
    }

    public function logout()
    {
        $customer = Auth::user();
        $customer->token_login = null;

        $user_data = Customer::where('email', $customer->email)->first();
        $user_data['token_login'] = null;
        $user_data->save();

        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    private function success($data)
    {
        return throw new HttpResponseException(response([
            $data
        ]));
    }
}
