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

class RatingController extends Controller
{
    public function create(RatingCreateRequest $request)
    {
        $customer = Auth::user();
        $data = $request->validated();
        $count = Order::where('id', $data['order_id'])->count();
        if (!$count) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "not found"
                    ]
                ]
            ]), 404);
        }
        $data['customer_id'] = $customer->id;
        $rating = new Rating($data);
        $rating->save();
        return $rating;
    }
}
