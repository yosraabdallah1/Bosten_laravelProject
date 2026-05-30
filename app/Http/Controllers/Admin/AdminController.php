<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'totalProducts' => Product::count(),
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'totalRevenue' => Order::whereNotIn('status', ['cancelled'])->sum('total'),
            'totalClients' => User::where('is_admin', false)->count(),
            'recentOrders' => Order::with('user')->latest()->limit(5)->get(),
            'lowStockProducts' => Product::where('stock', '<', 5)
                ->where('is_active', true)->get(),
        ]);
    }
}
