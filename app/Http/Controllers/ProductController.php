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
    public function create(ProductCreateRequest $request)
    {
        $data = $request->validated();

        $id = Category::where('category', $data['category_id'])->first();
        $data['category_id'] = $id->id;
        $product = new Product($data);
        $product->save();

        return $product;
    }

    public function show(Request $request)
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

    public function get(int $id)
    {
        $product = $product = DB::table('product')
            ->leftJoin('category', 'category.id', '=', 'product.category_id')
            ->where('product.id', $id)->first();

        $this->ProductNotFound($product);
        return $product;
    }

    public function update(int $id, ProductUpdateRequest $request)
    {
        $data = $request->validated();
        $product = Product::where('id', $id)->first();
        $id_category = Category::where('category', $data['category_id'])->first();
        $data['category_id'] = $id_category->id;
        $this->ProductNotFound($product);

        $product->fill($data);
        $product->save();

        return $product;
    }
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
