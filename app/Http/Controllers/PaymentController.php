<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentCreateRequest;
use App\Http\Requests\PaymentUpdateRequest;
use App\Models\Payment;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;



class PaymentController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/payment",
     *      tags={"Payment"},
     *      summary="Create new payment",
     *      description="Create a new payment",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="product_id", type="array",@OA\Items(
     *                  type="object",
     *                  @OA\Property(property="product[0]", type="string",example="1"),
     *                  @OA\Property(property="product[1]", type="string",example="2")
     *               )),
     *              @OA\Property(property="customer_id", type="string",example="1"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     * 
     *              example={{ 
     *                  "id": 1,
     *                  "quantity": 1,
     *                  "price": 3000000,
     *                  "payment_id": 2,
     *                  "product_id": 1,
     *                  "name": "Samsung A24",
     *                  "payment_date": "2024-05-22",
     *                  "customer_id": 1
     *              },{
     *                  "id": 2,
     *                  "quantity": 1,
     *                  "price": 30000000,
     *                  "payment_id": 2,
     *                  "product_id": 2,
     *                  "name": "Samsung S24",
     *                  "payment_date": "2024-05-22",
     *                  "customer_id": 1
     *              }},          
     *              @OA\Items(
     *              type="object",
     *               @OA\Property(property="id", type="string"),
     *              @OA\Property(property="quantity", type="string"),
     *              @OA\Property(property="price", type="string"),
     *              @OA\Property(property="payment_id", type="int"),
     *              @OA\Property(property="product_id", type="int"),
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(property="payment_date", type="date"),
     *              @OA\Property(property="customer_id", type="int"),
     *              )
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/

    public function create(PaymentCreateRequest $request)
    {
        $data = $request->validated();
        $amount = 0;
        // -------------- validasi pesanan -----------
        $count_payment = Payment::where('customer_id', $data['customer_id'])->where('status', "menunggu pembayaran")->count();
        if ($count_payment) {
            if (!$data) {
                throw new HttpResponseException(response([
                    "errors" => [
                        "payment" => [
                            "Menunggu membayar terlebih dahulu"
                        ]
                    ]
                ]), 400);
            }
        }
        $mytime = Carbon::now();
        $data_payment = [
            "payment_date" => $mytime->toDateTimeString(),
            "customer_id" => $data['customer_id'],
            "status" => "menunggu pembayaran",
            "amount" => $amount
        ];

        $payment = new Payment($data_payment);
        $payment->save();

        // ------------ hapus data cart
        for ($i = 0; $i < count($data['product_id']); $i++) {
            $cart = DB::table('cart')
                ->leftJoin('product', 'product.id', '=', 'cart.product_id')
                ->select('cart.*', 'product.price')
                ->where('product_id', $data['product_id'][$i])
                ->where('customer_id', $data['customer_id'])
                ->first();

            $this->checkoutFailed($cart, $payment);
            $data_order = [
                "quantity" => $cart->quantity,
                "price" => $cart->price * $cart->quantity,
                "product_id" => (int)$data['product_id'][$i],
                "payment_id" => $payment->id
            ];

            $amount += $cart->price * $cart->quantity;
            $order = new Order($data_order);
            $order->save();

            $cart = Cart::where('product_id', (int)$data['product_id'][$i])->where('customer_id', $data['customer_id'])->first();
            $cart->delete();
        }
        $data_payment['amount'] = $amount;

        //------------ update amount payment
        $payment->fill($data_payment);
        $payment->save();

        return $this->show($payment['customer_id']);
    }

    public function delete(int $id)
    {

        $payment = Payment::where('id', $id)->first();
        $payment->delete();
    }
    //api/payment all order
    private function show(int $id)
    {

        $payment_return = DB::table('order')
            ->leftJoin('product', 'product.id', '=', 'order.product_id')
            ->leftJoin('payment', 'payment.id', '=', 'order.payment_id')
            ->select('order.*', 'product.name', 'payment.payment_date', 'payment.customer_id')
            ->where('payment.status', "menunggu pembayaran")
            ->where('payment.customer_id', $id)
            ->get();
        return $payment_return;
    }
    // show order all order
    /** 
     * @param int $id
     * @OA\Get(
     *     path="/api/payment/show/{Id}",
     *     tags={"Payment"},
     *     summary="Show Order in Payment by ID",
     *     description="Returns a single payment",
     *     @OA\Parameter(
     *         name="Id",
     *         in="path",
     *         description="Id Payment",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *              @OA\JsonContent(
     *              type="array",
     *              title="data",
     *               example={{
     *                  "id":"2",
     *                  "name": "Samsung S24",
     *                  "description": "Samsung S24 memiliki...",
     *                  "price": "4000000",
     *                  "stock": "10",
     *                  "rate": "4",
     *                  "category_id": "1",
     *                  "quantity": "1",
     *                  "payment_id": "1",
     *                  "product_id": "1",
     *                  "payment_date": "1",
     *                  "amount": "3000000",
     *                  "token": "62e86759-fb4d-4370-ac97-92b4a2ea7a1f",
     *                  "status": "Dikemas",
     *                   "customer_id": 1
     *                }, {
     *                  "id":"2",
     *                  "name": "Samsung S23",
     *                  "description": "Samsung S23 memiliki...",
     *                  "price": "2800000",
     *                  "stock": "10",
     *                  "rate": "4",
     *                  "category_id": "1",
     *                  "quantity": "1",
     *                  "payment_id": "1",
     *                  "product_id": "2",
     *                  "payment_date": "1",
     *                  "amount": "3000000",
     *                  "token": "62e86759-fb4d-4370-ac97-92b4a2ea7a1f",
     *                  "status": "Dikemas",
     *                   "customer_id": 1
     *  
     *                }},
     *              @OA\Items(
     *              type="object",
     *              title="data[0]",
     *              @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="name", type="string",example="Samsung S24"),
     *              @OA\Property(property="description", type="string",example="samsung memiliki ....."),
     *              @OA\Property(property="price", type="int", example="34000000"),
     *              @OA\Property(property="stock", type="int",example="8"),
     *              @OA\Property(property="rate", type="int",example="5"),
     *              @OA\Property(property="category_id", type="int",example="1"),
     *              @OA\Property(property="quantity", type="int"),
     *              @OA\Property(property="payment_id", type="int"),
     *              @OA\Property(property="product_id", type="int"),
     *              @OA\Property(property="payment_date", type="date"),
     *              @OA\Property(property="amount", type="double"),
     *              @OA\Property(property="token", type="string"),
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="customer_id", type="int"),
     * 
     *           )
     *             
     *           )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplier"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     * )
     * */
    public function getPayment($id)
    {
        $payment = DB::table('order')
            ->leftJoin('product', 'product.id', '=', 'order.product_id')
            ->leftJoin('payment', 'payment.id', '=', 'order.payment_id')
            ->select('product.*', 'order.*', 'payment.*', 'order.id')
            ->where('payment.id', $id)
            ->get();
        return $payment;
    }
    // show all payment berdasarkan customer_id

    /** 
     * @param int $id
     * @OA\Get(
     *     path="/api/payment/{id}",
     *     tags={"Payment"},
     *     summary="Show Payment by ID Customer",
     *     description="Returns payment",
     *     @OA\Parameter(
     *         name="Id",
     *         in="path",
     *         description="Id Customer",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *              @OA\JsonContent(
     *              type="array",
     *              title="data",
     *               example={{
     *                  "id": 1,
     *                   "payment_date": "2024-05-20",
     *                   "amount": 68000000,
     *                   "token": "4565f927-267d-44a2-ae72-7f1bece4c70b",
     *                   "status": "Selesai",
     *                   "customer_id": 1,
     *               },
     *               {
     *                   "id": 2,
     *                   "payment_date": "2024-05-22",
     *                   "amount": 3000000,
     *                   "token": "62e86759-fb4d-4370-ac97-92b4a2ea7a1f",
     *                   "status": "Dikemas",
     *                   "customer_id": 1,
     *               }},
     *              @OA\Items(
     *              type="object",
     *              title="data[0]",
     *              @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="payment_date", type="date"),
     *              @OA\Property(property="amount", type="double"),
     *              @OA\Property(property="token", type="string"),
     *              @OA\Property(property="status", type="string"),
     *              @OA\Property(property="customer_id", type="int")
     * 
     *           )
     *             
     *           )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID supplier"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     * )
     * */
    public function allpayment(int $id)
    {
        $payment = DB::table('payment')
            ->where('payment.customer_id', $id)
            ->get();
        return $payment;
    }
    /**
     * @OA\Put(
     *      path="/api/payment/cancelorder",
     *      tags={"Payment"},
     *      summary="Cancel payment",
     *      description="Cancel payment",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="customer_id", type="string",example="1"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     * 
     *              example={{ 
     *               "id": 2,
     *               "payment_date": "2024-05-22",
     *               "amount": 3000000,
     *               "token": null,
     *               "status": "Cancel Order",
     *               "customer_id": 1,
     *              }},          
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="payment_date", type="date"),
     *              @OA\Property(property="amount", type="double"),
     *              @OA\Property(property="token", type="string"),
     *              @OA\Property(property="status", type="string"),
     *            @OA\Property(property="customer_id", type="int"),
     *          
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function cancelOrder(PaymentUpdateRequest $request)
    {
        $data = $request->validated();

        $payment = Payment::where('id', $data['id'])
            ->where('customer_id', $data['customer_id'])
            ->where('status', "menunggu pembayaran")
            ->first();
        $data = [
            "status" => "Cancel Order"
        ];
        $this->ProductNotFound($payment);
        $payment->fill($data);
        $payment->save();

        return $payment;
    }

    /**
     * @OA\Put(
     *      path="/api/payment/updatebayar",
     *      tags={"Payment"},
     *      summary="Bayar payment",
     *      description="Bayar payment",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="customer_id", type="string",example="1"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     * 
     *              example={{ 
     *               "id": 2,
     *               "payment_date": "2024-05-22",
     *               "amount": 3000000,
     *               "token": "62e86759-fb4d-4370-ac97-92b4a2ea7a1f",
     *               "status": "Diterima",
     *               "customer_id": 1,
     *              }},          
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="payment_date", type="date"),
     *              @OA\Property(property="amount", type="double"),
     *              @OA\Property(property="token", type="string"),
     *              @OA\Property(property="status", type="string"),
     *            @OA\Property(property="customer_id", type="int"),
     *          
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function updateBayar(PaymentUpdateRequest $request)
    {

        $data = $request->validated();
        $product = DB::table('order')
            ->leftJoin('product', 'product.id', '=', 'order.product_id')
            ->leftJoin('payment', 'payment.id', '=', 'order.payment_id')
            ->select('order.quantity', 'product.*')
            ->where('payment.id', $data['id'])
            ->get();

        $this->ProductNotFound($product);

        //-----------ngecek stock

        foreach ($product as $p) {
            if ($p->quantity > $p->stock) {
                throw new HttpResponseException(response([
                    "errors" => [
                        "stock" => [
                            "stock" . $p->name . " tidak cukup"
                        ]
                    ]
                ]), 400);
            }
        }
        //----------- add token  
        $cekBayar = Payment::where('customer_id', $data['customer_id'])->where('status', "Diterima")->where('id', $data['id'])->first();
        if ($cekBayar) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "Sudah Dibayar"
                    ]
                ]
            ]), 404);
        }
        $payment = Payment::where('customer_id', $data['customer_id'])
            ->where('status', "menunggu pembayaran")
            ->where('id', $data['id'])
            ->first();
        $data = [
            "status" => "Diterima",
            "token" => (string) Str::uuid()
        ];
        $this->ProductNotFound($payment);

        $payment->fill($data);
        $payment->save();
        //---------- kurangi stock
        foreach ($product as $p) {
            $stock = Product::where('id', $p->id)->first();
            $data_stock = [
                "stock" => $stock->stock - $p->quantity
            ];
            $stock->fill($data_stock);
            $stock->save();
        }

        return $payment;
    }

    /**
     * @OA\Put(
     *      path="/api/payment/updatekemas",
     *      tags={"Payment"},
     *      summary="Update Kemasan Order",
     *      description="Update Kemasan Order",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="customer_id", type="string",example="1"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     * 
     *              example={{ 
     *               "id": 2,
     *               "payment_date": "2024-05-22",
     *               "amount": 3000000,
     *               "token": "62e86759-fb4d-4370-ac97-92b4a2ea7a1f",
     *               "status": "Dikemas",
     *               "customer_id": 1,
     *              }},          
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="payment_date", type="date"),
     *              @OA\Property(property="amount", type="double"),
     *              @OA\Property(property="token", type="string"),
     *              @OA\Property(property="status", type="string"),
     *            @OA\Property(property="customer_id", type="int"),
     *          
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function updateKemas(PaymentUpdateRequest $request)
    {

        $data = $request->validated();
        $payment = Payment::where('id', $data['id'])
            ->where('customer_id', $data['customer_id'])
            ->where('status', "Diterima")
            ->first();
        $this->ProductNotFound($payment);
        $data = [
            "status" => "Dikemas"
        ];
        $payment->fill($data);
        $payment->save();

        return $payment;
    }

    /**
     * @OA\Put(
     *      path="/api/payment/updatekirim",
     *      tags={"Payment"},
     *      summary="Update Kirim Order",
     *      description="Update Kirim Order",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="customer_id", type="string",example="1"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     * 
     *              example={{ 
     *               "id": 2,
     *               "payment_date": "2024-05-22",
     *               "amount": 3000000,
     *               "token": "62e86759-fb4d-4370-ac97-92b4a2ea7a1f",
     *               "status": "Dikirim",
     *               "customer_id": 1,
     *              }},          
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="payment_date", type="date"),
     *              @OA\Property(property="amount", type="double"),
     *              @OA\Property(property="token", type="string"),
     *              @OA\Property(property="status", type="string"),
     *            @OA\Property(property="customer_id", type="int"),
     *          
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function updatekirim(PaymentUpdateRequest $request)
    {

        $data = $request->validated();
        $payment = Payment::where('id', $data['id'])
            ->where('customer_id', $data['customer_id'])
            ->where('status', "Dikemas")
            ->first();
        $this->ProductNotFound($payment);
        $data = [
            "status" => "Dikirim"
        ];
        $payment->fill($data);
        $payment->save();

        return $payment;
    }
    /**
     * @OA\Put(
     *      path="/api/payment/selesai",
     *      tags={"Payment"},
     *      summary="Update Selesai Order",
     *      description="Update Selesai Order",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="customer_id", type="string",example="1"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     * 
     *              example={{ 
     *               "id": 2,
     *               "payment_date": "2024-05-22",
     *               "amount": 3000000,
     *               "token": "62e86759-fb4d-4370-ac97-92b4a2ea7a1f",
     *               "status": "Selesai",
     *               "customer_id": 1,
     *              }},          
     *              @OA\Property(property="id", type="int"),
     *              @OA\Property(property="payment_date", type="date"),
     *              @OA\Property(property="amount", type="double"),
     *              @OA\Property(property="token", type="string"),
     *              @OA\Property(property="status", type="string"),
     *            @OA\Property(property="customer_id", type="int"),
     *          
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function updateSelesai(PaymentUpdateRequest $request)
    {

        $data = $request->validated();
        $payment = Payment::where('id', $data['id'])
            ->where('customer_id', $data['customer_id'])
            ->where('status', "Dikirim")
            ->first();
        $this->ProductNotFound($payment);
        $data = [
            "status" => "Selesai"
        ];
        $payment->fill($data);
        $payment->save();

        return $payment;
    }

    private function ProductNotFound($data)
    {
        if (!$data) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "not found"
                    ]
                ]
            ]), 404);
        }
    }
    private function checkoutFailed($data, $payment)
    {
        if (!$data) {
            $payment = Payment::where('id', $payment['id'])
                ->where('status', "menunggu pembayaran")->first();
            $payment->delete();
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "Payment Failed - Product Not Found - Please create product on cart"
                    ]
                ]
            ]), 404);
        }
    }
}
