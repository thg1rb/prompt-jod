<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories');
    }

    public function data()
    {
        $categories = Auth::user()->categories()
            ->ordered()
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function store(CategoryStoreRequest $request): JsonResponse
    {
        if (Auth::user()->isFree()) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาสมัครสมาชิก Premium เพื่อสร้างหมวดหมู่',
                'requires_subscription' => true,
            ], 403);
        }

        $validated = $request->validated();

        $category = Auth::user()->categories()->create([
            'name' => $validated['name'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'is_active' => true,
            'sort_order' => Category::where('user_id', Auth::id())->count(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'บันทึกหมวดหมู่เรียบร้อย',
            'category' => $category,
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category): JsonResponse
    {
        if (Auth::user()->isFree()) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาสมัครสมาชิก Premium เพื่อแก้ไขหมวดหมู่',
                'requires_subscription' => true,
            ], 403);
        }

        if ($category->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'ไม่พบหมวดหมู่'], 404);
        }

        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'แก้ไขหมวดหมู่เรียบร้อย',
            'category' => $category,
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if (Auth::user()->isFree()) {
            return response()->json([
                'success' => false,
                'message' => 'กรุณาสมัครสมาชิก Premium เพื่อลบหมวดหมู่',
                'requires_subscription' => true,
            ], 403);
        }

        if ($category->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'ไม่พบหมวดหมู่'], 404);
        }

        if ($category->transactions()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่สามารถลบหมวดหมู่ที่มีธุรกรรมได้',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'ลบหมวดหมู่เรียบร้อย',
        ]);
    }
}
