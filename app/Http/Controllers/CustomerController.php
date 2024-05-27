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
    /**
     * @OA\Post(
     *     path="/Customer",
     *     tags={"Customer"},
     *     summary="Create customer",
     *     description="This can only be done by the logged in user.",
     *     operationId="register",
     *     @OA\Response(
     *         response="200",
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     ),
     *     @OA\RequestBody(
     *         description="Create Customer object",
     *         @OA\JsonContent(ref="#/components/schemas/Customer")
     *     )
     * )
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

    /**
     * @OA\Post(
     *     path="/Customer/login",
     *     tags={"Customer"},
     *     summary="Login customer",
     *     description="This can only be done by the logged in user.",
     *     operationId="login",
     *  
     *     @OA\Response(
     *         response="201",
     *         description="successful operation",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="password", type="string")
     *          )
     *     ),
     *     @OA\RequestBody(
     *         description="Create Customer object",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email", type="string", example="example@gmail.com"),
     *              @OA\Property(property="password", type="string")
     *          )
     *     )
     * )
     */

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

    /**
     * @OA\Delete(
     *     path="/Customer/logout/{email}",
     *     tags={"Customer"},
     *     summary="Logout customer",
     *     description="This can only be done by the logged in user.",
     *     operationId="logout",
     *    @OA\Parameter(
     *         name="Email",
     *         in="path",
     *         description="Email Customer",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="successful operation",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="data", type="boolean", example=true),
     *          )
     *     ),
     * )
     */

    public function logout($email)
    {
        $user_data = Customer::where('email', $email)->first();
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
