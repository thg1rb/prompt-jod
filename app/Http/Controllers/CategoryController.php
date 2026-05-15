<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Models\Category;
use App\Models\FixedCategory;
use App\Models\Wallet;
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
        $categories = Auth::user()->customCategories()
            ->with('fixedCategory')
            ->ordered()
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function fixedCategories()
    {
        return response()->json([
            'fixed_categories' => FixedCategory::orderBy('sort_order')->get(),
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

        $category = Auth::user()->customCategories()->create([
            'fixed_category_id' => $validated['fixed_category_id'],
            'name' => $validated['name'],
            'icon' => $validated['icon'],
        ]);

        $category->load('fixedCategory');

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

        if (! $category->isOwnedBy(Auth::user())) {
            return response()->json(['success' => false, 'message' => 'ไม่พบหมวดหมู่'], 404);
        }

        $validated = $request->validated();

        $category->update([
            'fixed_category_id' => $validated['fixed_category_id'],
            'name' => $validated['name'],
            'icon' => $validated['icon'],
        ]);

        $category->load('fixedCategory');

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

        if (! $category->isOwnedBy(Auth::user())) {
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

    public function walletCategories(Wallet $wallet): JsonResponse
    {
        if (! $wallet->hasAccess(Auth::user())) {
            return response()->json(['success' => false, 'message' => 'ไม่พบกระเป๋าเงิน'], 404);
        }

        $categories = $wallet->customCategories()
            ->with('fixedCategory')
            ->ordered()
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    public function storeWalletCategory(CategoryStoreRequest $request, Wallet $wallet): JsonResponse
    {
        if (! $wallet->isOwner(Auth::user())) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์'], 403);
        }

        $validated = $request->validated();

        $category = $wallet->customCategories()->create([
            'fixed_category_id' => $validated['fixed_category_id'],
            'name' => $validated['name'],
            'icon' => $validated['icon'],
        ]);

        $category->load('fixedCategory');

        return response()->json([
            'success' => true,
            'message' => 'บันทึกหมวดหมู่เรียบร้อย',
            'category' => $category,
        ]);
    }

    public function updateWalletCategory(CategoryUpdateRequest $request, Wallet $wallet, Category $category): JsonResponse
    {
        if (! $wallet->isOwner(Auth::user())) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์'], 403);
        }

        if (! $category->belongsToWallet($wallet)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบหมวดหมู่'], 404);
        }

        $validated = $request->validated();

        $category->update([
            'fixed_category_id' => $validated['fixed_category_id'],
            'name' => $validated['name'],
            'icon' => $validated['icon'],
        ]);

        $category->load('fixedCategory');

        return response()->json([
            'success' => true,
            'message' => 'แก้ไขหมวดหมู่เรียบร้อย',
            'category' => $category,
        ]);
    }

    public function destroyWalletCategory(Wallet $wallet, Category $category): JsonResponse
    {
        if (! $wallet->isOwner(Auth::user())) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์'], 403);
        }

        if (! $category->belongsToWallet($wallet)) {
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
