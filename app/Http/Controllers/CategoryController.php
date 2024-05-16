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
    public function create(CategoryCreateRequest $request)
    {
        $data = $request->validated();
        $this->responseCategoryAlready($data);
        $category = new Category($data);
        $category->save();

        return $this->success($category);
    }

    public function show()
    {
        $category = Category::query()->get();
        $this->CategoryNotFound($category);
        return $category;
    }
    public function get(int $id)
    {
        $category = Category::where('id', $id)->first();
        $this->CategoryNotFound($category);
        return $category;
    }
    public function update(int $id, CategoryUpdateRequest $request)
    {
        $category = Category::where('id', $id)->first();
        $this->CategoryNotFound($category);
        $data = $request->validated();
        $this->responseCategoryAlready($data);
        $category->fill($data);
        $category->save();

        return $this->success($category);
    }

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
