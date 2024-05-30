<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryCreateRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/category",
     *      tags={"Category"},
     *      summary="Create new category",
     *      description="Create a new category",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="category", type="string",example="Samsung"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *             @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="category", type="string",example="Samsung"),
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function create(CategoryCreateRequest $request)
    {
        $data = $request->validated();
        $this->responseCategoryAlready($data);
        $category = new Category($data);
        $category->save();

        return $this->success($category);
    }
    /**
     * @OA\Get(
     *      path="/api/category",
     *      tags={"Category"},
     *      summary="Show list category",
     *      description="Show list category",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="array",
     *              title="data",
     *              example={{
     *                  "id":"1",
     *                  "category":"Samsung" 
     *              },
     *              {
     *                  "id":"2",
     *                  "category":"oppo" 
     *              }
     * 
     *              }  ,
     *               @OA\Items(
     *              type="object",
     *              title="data[0]",
     *              @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="category", type="string",example="Samsung"),     
     * 
     *              )
     * 
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              title="data",
     *               @OA\Items(
     *              type="object",
     *              title="data[0]",
     *              @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="category", type="string",example="Samsung"),     
     * 
     *              )
     * 
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function show()
    {
        $category = Category::query()->get();
        $this->CategoryNotFound($category);
        return $category;
    }
    /**
     * @OA\Get(
     *      path="/api/category/{id}",
     *      tags={"Category"},
     *      summary="Get Single Category",
     *      description="Get Single Category",
     *     @OA\Parameter(
     *         name="Id",
     *         in="path",
     *         description="Id Category",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *             @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="category", type="string",example="Samsung"),
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function get(int $id)
    {
        $category = Category::where('id', $id)->first();
        $this->CategoryNotFound($category);
        return $category;
    }
    /**
     * @OA\Put(
     *      path="/api/category",
     *      tags={"Category"},
     *      summary="Update category",
     *      description="Update a new category",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="category", type="string",example="Samsung"),
     *           )
     *         
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *             @OA\Property(property="id", type="string", example="1"),
     *              @OA\Property(property="category", type="string",example="Samsung"),
     *           )
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     * )
     **/
    public function update(CategoryUpdateRequest $request)
    {

        $data = $request->validated();
        $category = Category::where('id', $data['id'])->first();
        $this->CategoryNotFound($category);
        $this->responseCategoryAlready($data);
        $category->fill($data);
        $category->save();

        return $this->success($category);
    }
    /** 
     * @param int $id
     * @OA\Delete(
     *     path="/api/category/{Id}",
     *     tags={"Category"},
     *     summary="Delete Category by ID",
     *     description="Delete a single Category",
     *     @OA\Parameter(
     *         name="Id",
     *         in="path",
     *         description="Id Category",
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
        $category = Category::where('id', $id)->first();
        $this->CategoryNotFound($category);

        $category->delete();
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

    private function responseCategoryAlready($data)
    {
        if (Category::where('category', $data['category'])->count() == 1) {
            throw new HttpResponseException(response([
                "errors" => [
                    "category" => [
                        "category already created"
                    ]
                ]
            ]), 400);
        }
    }
    private function CategoryNotFound($data)
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
