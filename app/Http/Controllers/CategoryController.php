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
            ->with('rules')
            ->ordered()
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function store(CategoryStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $category = Auth::user()->categories()->create([
            'name' => $validated['name'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'is_active' => true,
            'is_system' => false,
            'sort_order' => Category::where('user_id', Auth::id())->count(),
        ]);

        if (! empty($validated['keywords'])) {
            foreach ($validated['keywords'] as $keyword) {
                if (! empty(trim($keyword))) {
                    $category->rules()->create([
                        'keyword' => trim($keyword),
                        'priority' => 1,
                        'is_active' => true,
                        'case_sensitive' => false,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'บันทึกหมวดหมู่เรียบร้อย',
            'category' => $category->load('rules'),
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category): JsonResponse
    {
        if ($category->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'ไม่พบหมวดหมู่'], 404);
        }

        if ($category->is_system) {
            return response()->json(['success' => false, 'message' => 'ไม่สามารถแก้ไขหมวดหมู่ระบบได้'], 403);
        }

        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
        ]);

        $category->rules()->delete();

        if (! empty($validated['keywords'])) {
            foreach ($validated['keywords'] as $keyword) {
                if (! empty(trim($keyword))) {
                    $category->rules()->create([
                        'keyword' => trim($keyword),
                        'priority' => 1,
                        'is_active' => true,
                        'case_sensitive' => false,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'แก้ไขหมวดหมู่เรียบร้อย',
            'category' => $category->load('rules'),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'ไม่พบหมวดหมู่'], 404);
        }

        if ($category->is_system) {
            return response()->json(['success' => false, 'message' => 'ไม่สามารถลบหมวดหมู่ระบบได้'], 403);
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
