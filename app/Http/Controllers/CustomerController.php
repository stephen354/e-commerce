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
use PhpParser\Node\Stmt\Echo_;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomerController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('auth:api', ['except' => ['/customer/login', '/customer/register']]);
    // }
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Post(
     *     path="/api/customer",
     *     tags={"Customer"},
     *     summary="Create customer",
     *     description="This can only be done by the logged in user.",
     *     operationId="register",
     *     @OA\Response(
     *         response="200",
     *         description="successful operation",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="first_name", type="string"),
     *              @OA\Property(property="last_name", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="password", type="string"),
     *              @OA\Property(property="address", type="string"),
     *              @OA\Property(property="phone", type="string"),
     *          )
     *     ),
     *     @OA\RequestBody(
     *         description="Create Customer object",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="first_name", type="string"),
     *              @OA\Property(property="last_name", type="string"),
     *              @OA\Property(property="email", type="string"),
     *              @OA\Property(property="password", type="string"),
     *              @OA\Property(property="address", type="string"),
     *              @OA\Property(property="phone", type="string"),
     *          )
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
     *     path="/api/customer/login",
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
        return $customer;
    }

    /**
     * @OA\Delete(
     *     path="/api/customer/logout/{email}",
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
        return $user_data;
        if (!$user_data) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "Email Not Found"
                    ]
                ]
            ], 401));
        }
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
