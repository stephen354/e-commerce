<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentCreateRequest;
use App\Models\Payment;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



class PaymentController extends Controller
{
    public function create(PaymentCreateRequest $request)
    {
        $customer = Auth::user();
        $data = $request->validated();
        for ($i = 0; $i < 2; $i++) {
            $cart = DB::table('cart')
                ->leftJoin('product', 'product.id', '=', 'cart.product_id')
                ->select('cart.*', 'product.price')
                ->where('product_id', $data['product_id'][$i])
                ->where('customer_id', $customer->id)
                ->first();

            $data_order = [
                "quantity" => $cart->quantity,
                "price" => $cart->price * $cart->quantity,
                "product_id" => (int)$data['product_id'][$i],
                "payment_id" => $i + 1
            ];
            $data_order = [
                "quantity" => $cart->quantity,
                "price" => $cart->price * $cart->quantity,
                "product_id" => (int)$data['product_id'][$i],
                "payment_id" => $i + 1
            ];

            $order = new Order($data_order);
            $order->save();
        }

        $data_1 = [
            "quantity" => 1,
            "price" => 1,
            "product_id" => 1,
            "payment_id" => 1
        ];
        // $data_p = [
        //     "id" => 1,
        //     "payment_date" => "2/2/10",
        //     "amount" => 2,
        //     "customer_id" => 1,
        //     "status" => "Menunggu Pembayaran",
        // ];
        // $payment = new Payment($data_p);
        // $payment->save();
        // return $payment;

    }
}
