<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getActiveProducts(?string $categorySlug = null, ?string $search = null)
    {
        $query = Product::with('category')->where('is_active', true);

        if ($categorySlug) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%");
        }

        return $query->latest()->paginate(12);
    }

    public function getInStockProducts()
    {
        return Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->get();
    }

    public function getBestSellers(int $limit = 5)
    {
        return Product::withSum('orderItems', 'quantity')
            ->orderByDesc('order_items_sum_quantity')
            ->where('is_active', true)
            ->limit($limit)
            ->get();
    }

    public function getLowStock(int $threshold = 5)
    {
        return Product::where('stock', '<', $threshold)
            ->where('is_active', true)
            ->get();
    }
}
