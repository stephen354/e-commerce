<?php

namespace App\Http\Controllers;

use App\Http\Requests\RatingCreateRequest;
use App\Models\Rating;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function create(RatingCreateRequest $request)
    {
        Auth::user();
        $data = $request->validated();
        // cek data
        $cekRating = Rating::where('customer_id', $data['customer_id'])->where('order_id', $data['order_id'])->count();
        if ($cekRating) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "Rating be created"
                    ]
                ]
            ]), 404);
        }
        $order = DB::table('order')
            ->leftJoin('payment', 'payment.id', '=', 'order.payment_id')
            ->select('order.*')
            ->where('order.id', $data['order_id'])
            ->where('payment.status', "selesai")
            ->first();
        if (!$order) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "not found"
                    ]
                ]
            ]), 404);
        }
        $rating = new Rating($data);
        $rating->save();


        // rata2 rating
        $product = Product::where('id', $order->product_id)->first();
        $rate = DB::table('rating')
            ->leftJoin('order', 'order.id', '=', 'rating.order_id')
            ->leftJoin('product', 'product.id', '=', 'order.product_id')
            ->select('rating.*')
            ->where('product.id', $order->product_id)
            ->get();
        $i = 0;
        $total = 0;
        foreach ($rate as $r) {
            $total += $r->rate;
            $i++;
        }
        $product->rate = $total / $i;
        $product->save();
        return $rating;
    }
}
