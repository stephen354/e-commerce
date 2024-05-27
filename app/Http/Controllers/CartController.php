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
    /**
     * @OA\Post(
     *      path="/cart",
     *      tags={"Cart"},
     *      summary="Create new Product in cart",
     *      description="Create new Product in cart",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="quantity", type="int",example="1"),
     *              @OA\Property(property="product_id", type="int",example="2"),
     *              @OA\Property(property="customer_id", type="int",example="1"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *             @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="quantity", type="int",example="1"),
     *              @OA\Property(property="product_id", type="int",example="2"),
     *              @OA\Property(property="customer_id", type="int",example="1"),
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function create(CartCreateRequest $request)
    {

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


    /** 
     * @param int $id
     * @OA\Get(
     *     path="/cart/product/{Id}",
     *     tags={"Cart"},
     *     summary="Find Product in cart by ID",
     *     description="Returns a single product",
     *     @OA\Parameter(
     *         name="Id",
     *         in="path",
     *         description="Id Cart",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *              @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="name", type="string",example="Samsung S24"),
     *              @OA\Property(property="description", type="string",example="samsung memiliki ....."),
     *              @OA\Property(property="price", type="int", example="34000000"),
     *              @OA\Property(property="stock", type="int",example="8"),
     *              @OA\Property(property="rate", type="int",example="5"),
     *              @OA\Property(property="category_id", type="int",example="1"),
     *              @OA\Property(property="category", type="int",example="Samsung"),
     *              @OA\Property(property="quantity", type="int",example="1"),
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
    public function get(int $id)
    {

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

    /** 
     * @param int $id
     * @OA\Delete(
     *     path="/cart/{Id}",
     *     tags={"Cart"},
     *     summary="Delete Product in Cart by ID",
     *     description="Delete a single product",
     *     @OA\Parameter(
     *         name="Id",
     *         in="path",
     *         description="Id Cart",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="data", type="boolean", example=true),
     *          )
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
    public function delete(int $id)
    {
        $cart = Cart::where('id', $id)->first();
        $this->ProductNotFound($cart);
        $cart->delete();
        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    /** 
     * @param int $id
     * @OA\Get(
     *     path="/cart/{Id}",
     *     tags={"Cart"},
     *     summary="Show Product in Cart by Customer",
     *     description="Show Product in Cart by Customer",
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
     *         @OA\JsonContent(
     *              type="array",
     *              title="data",
     *               example={{
     *                  "id":"1",
     *                  "name": "Samsung S24",
     *                  "description": "Samsung S24 memiliki...",
     *                  "price": "3000000",
     *                  "stock": "10",
     *                  "rate": "4",
     *                  "category_id": "1",
     *                  "category": "Samsung",
     *                  "quantity": "2",
     *                }, {
     *                  "id":"2",
     *                  "name": "Samsung S23",
     *                  "description": "Samsung S23 memiliki...",
     *                  "price": "2800000",
     *                  "stock": "10",
     *                  "rate": "4",
     *                  "category_id": "1",
     *                  "category": "Samsung",
     *                  "quantity": "2",
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
     *              @OA\Property(property="category", type="string",example="Samsung"),
     *              @OA\Property(property="quantity", type="int",example="1"),
     *              
     *           
     *           ),
     *      
     *          
     *          
     *          )
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
    public function cart(int $id)
    {
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
