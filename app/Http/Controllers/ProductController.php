<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCreateRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    /**
     * @OA\Post(
     *      path="/api/product",
     *      tags={"Product"},
     *      summary="Create new product",
     *      description="Create a new product",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="name", type="string",example="Samsung S24"),
     *              @OA\Property(property="description", type="string",example="samsung memiliki ....."),
     *              @OA\Property(property="price", type="int", example="34000000"),
     *              @OA\Property(property="stock", type="int",example="8"),
     *              @OA\Property(property="category_id", type="int",example="1"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="name", type="string",example="Samsung S24"),
     *              @OA\Property(property="description", type="string",example="samsung memiliki ....."),
     *              @OA\Property(property="price", type="int", example="34000000"),
     *              @OA\Property(property="stock", type="int",example="8"),
     *              @OA\Property(property="rate", type="int",example="null"),
     *              @OA\Property(property="category_id", type="int",example="1"),
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function create(ProductCreateRequest $request)
    {
        $data = $request->validated();

        // $id = Category::where('category', $data['category_id'])->first();
        // $data['category_id'] = $id->id;
        $product = new Product($data);
        $product->save();

        return $product;
    }
    /** 
     * @param int $id
     * @OA\Get(
     *     path="/api/product",
     *     tags={"Product"},
     *     summary="Show All product",
     *     description="Returns a all Product",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         
     *         @OA\JsonContent(
     *              type="array",
     *              title="data",
     *                  example={{
     *                  "id":"1",
     *                  "name": "Samsung S24",
     *                  "description": "Samsung S24 memiliki...",
     *                  "price": "3000000",
     *                  "stock": "10",
     *                  "rate": "4",
     *                  "category_id": "1",
     *                  "category": "Samsung",
     *                }, {
     *                  "id":"2",
     *                  "name": "Samsung S23",
     *                  "description": "Samsung S23 memiliki...",
     *                  "price": "2800000",
     *                  "stock": "10",
     *                  "rate": "4",
     *                  "category_id": "1",
     *                  "category": "Samsung",
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
     *           )
     *          )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="error", type="string", example="Product not Found"),
     *          )
     *     ),
     * )
     * */

    public function show()
    {
        $page = 1;
        $count = Product::query()->count();

        $this->ProductNotFound($count);
        if (!$count || $count > 10) {
            $page = $count / 10;
        }
        $product = DB::table('product')
            ->leftJoin('category', 'category.id', '=', 'product.category_id')
            ->select('product.*', 'category.category')
            ->paginate(perPage: 10, page: $page);

        return $product;
    }



    /** 
     * @param int $id
     * @OA\Get(
     *     path="/api/product/{Id}",
     *     tags={"Product"},
     *     summary="Find Product by ID",
     *     description="Returns a single product",
     *     @OA\Parameter(
     *         name="Id",
     *         in="path",
     *         description="Id Product",
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
     *              @OA\Property(property="category", type="string",example="Samsung"),
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
        $product = $product = DB::table('product')
            ->leftJoin('category', 'category.id', '=', 'product.category_id')
            ->where('product.id', $id)->first();

        $this->ProductNotFound($product);
        return $product;
    }

    /**
     * @OA\Put(
     *      path="/api/product/{id}",
     *      tags={"Product"},
     *      summary="Update a product",
     *      description="Returns updated product data",
     *      @OA\Parameter(
     *          name="id",
     *          description="id Product",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="name", type="string",example="Samsung S24"),
     *              @OA\Property(property="description", type="string",example="samsung memiliki ....."),
     *              @OA\Property(property="price", type="int", example="34000000"),
     *              @OA\Property(property="stock", type="int",example="8"),
     *              @OA\Property(property="rate", type="int",example="5"),
     *              @OA\Property(property="category_id", type="int",example="1"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="name", type="string",example="Samsung S24"),
     *              @OA\Property(property="description", type="string",example="samsung memiliki ....."),
     *              @OA\Property(property="price", type="int", example="34000000"),
     *              @OA\Property(property="stock", type="int",example="8"),
     *              @OA\Property(property="rate", type="int",example="5"),
     *              @OA\Property(property="category_id", type="int",example="1"),
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
     * )
     **/
    public function update(int $id, ProductUpdateRequest $request)
    {
        $data = $request->validated();
        $product = Product::where('id', $id)->first();
        $this->ProductNotFound($product);
        $product->fill($data);
        $product->save();

        return $product;
    }


    /** 
     * @param int $id
     * @OA\Delete(
     *     path="/api/product/{Id}",
     *     tags={"Product"},
     *     summary="Delete Product by ID",
     *     description="Delete a single product",
     *     @OA\Parameter(
     *         name="Id",
     *         in="path",
     *         description="Id Product",
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
        $product = Product::where('id', $id)->first();
        $this->ProductNotFound($product);
        //cek product_id apakah ada di table Order
        $count = Order::where('product_id', $id)->count();
        if (!$count) {
            $product->delete();
        }

        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    /** 
     * 
     * @OA\Get(
     *     path="/api/product/category/{category}",
     *     tags={"Product"},
     *     summary="Show Product by Category",
     *     description="Show Product",
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="Category",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     * 
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              title="data",
     *                   example={{
     *                  "id":"1",
     *                  "name": "Samsung S24",
     *                  "description": "Samsung S24 memiliki...",
     *                  "price": "3000000",
     *                  "stock": "10",
     *                  "rate": "4",
     *                  "category_id": "1",
     *                  "category": "Samsung",
     *                }, {
     *                  "id":"2",
     *                  "name": "Samsung S23",
     *                  "description": "Samsung S23 memiliki...",
     *                  "price": "2800000",
     *                  "stock": "10",
     *                  "rate": "4",
     *                  "category_id": "1",
     *                  "category": "Samsung",
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
     *           )
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

    public function byCategory(String $category)
    {
        $page = 1;
        $count = DB::table('product')
            ->leftJoin('category', 'category.id', '=', 'product.category_id')
            ->select('product.*', 'category.category')
            ->where('category', $category)->count();

        $this->ProductNotFound($count);
        if (!$count || $count > 10) {
            $page = $count / 10;
        }
        $product = DB::table('product')
            ->leftJoin('category', 'category.id', '=', 'product.category_id')
            ->where('category', $category)
            ->select('product.*', 'category.category')
            ->paginate(perPage: 10, page: $page);

        return $product;
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
