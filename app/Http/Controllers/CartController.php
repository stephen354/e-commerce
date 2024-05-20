<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartCreateRequest;
use App\Models\Cart;
use App\Http\Requests\StoreCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\Product;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function create(CartCreateRequest $request)
    {
        Auth::user();
        $data = $request->validated();
        $product = Product::where('id', $data['product_id'])->first();
        $this->ProductNotFound($product);
        $cart = Cart::where('product_id', $data['product_id'])->first();
        if ($cart) {
            $data['quantity'] += $cart->quantity;
            $cart->fill($data);
            $cart->save();
        } else {
            $cart = new Cart($data);
            $cart->save();
        }
        return $cart;
    }

    public function get(int $id)
    {
        Auth::user();
        $page = 1;
        $count = Cart::where('id', $id)->count();

        $this->ProductNotFound($count);

        if (!$count || $count > 10) {
            $page = $count / 10;
        }
        $product = DB::table('product')
            ->leftJoin('cart', 'cart.product_id', '=', 'product.id')
            ->leftJoin('category', 'category.id', '=', 'product.category_id')
            ->select('product.*', 'category.category', 'cart.quantity')->where('cart.id', $id)
            ->paginate(perPage: 10, page: $page);

        return $product;
    }

    public function delete(int $id)
    {
        Auth::user();
        $cart = Cart::where('id', $id)->first();
        $this->ProductNotFound($cart);
        $cart->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function cart(int $id)
    {
        $customer = Auth::user();
        $page = 1;
        $count = Cart::where('customer_id', $id)->count();

        $this->ProductNotFound($count);
        if (!$count || $count > 5) {
            $page = $count / 5;
        }
        $cart = DB::table('product')
            ->leftJoin('cart', 'cart.product_id', '=', 'product.id')
            ->leftJoin('category', 'category.id', '=', 'product.category_id')
            ->select('product.*', 'category.category', 'cart.quantity')->where('cart.customer_id', $id)
            ->paginate(perPage: 5, page: $page);

        return $cart;
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
}
