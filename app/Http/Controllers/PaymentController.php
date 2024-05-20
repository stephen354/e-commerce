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
    public function create(PaymentCreateRequest $request)
    {
        $customer = Auth::user();
        $data = $request->validated();
        $amount = 0;

        $customer->id = $data['customer_id'];
        // -------------- validasi pesanan -----------
        $count_payment = Payment::where('customer_id', $customer->id)->where('status', "menunggu pembayaran")->count();
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
            "customer_id" => $customer->id,
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
                ->where('customer_id', $customer->id)
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

            $cart = Cart::where('product_id', (int)$data['product_id'][$i])->where('customer_id', $customer->id)->first();
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
        $customer = Auth::user();
        $payment = Payment::where('customer_id', $customer->id)->where('id', $id)->first();
        $payment->delete();
    }
    //payment all order
    private function show(int $id)
    {
        Auth::user();
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
    public function getPayment($id)
    {
        $customer = Auth::user();
        $payment = DB::table('order')
            ->leftJoin('product', 'product.id', '=', 'order.product_id')
            ->leftJoin('payment', 'payment.id', '=', 'order.payment_id')
            ->select('product.*', 'order.*', 'payment.*', 'order.id')
            ->where('payment.id', $id)
            ->where('payment.customer_id', $customer->id)
            ->get();
        return $payment;
    }
    // show all payment berdasarkan customer_id
    public function allpayment(int $id)
    {
        $customer = Auth::user();
        $payment = DB::table('payment')
            ->where('payment.customer_id', $id)
            ->get();
        return $payment;
    }

    public function cancelOrder(PaymentUpdateRequest $request)
    {
        $data = $request->validated();
        Auth::user();
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

    public function updateBayar(PaymentUpdateRequest $request)
    {
        Auth::user();
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
    public function updateKemas(PaymentUpdateRequest $request)
    {
        Auth::user();
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
    public function updatekirim(PaymentUpdateRequest $request)
    {
        Auth::user();
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
    public function updateSelesai(PaymentUpdateRequest $request)
    {
        Auth::user();
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
