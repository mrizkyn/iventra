<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Tampilkan dashboard berdasarkan role
        if ($user->role?->role_name === 'Owner') {
            return Inertia::render('Dashboard/OwnerDashboard', [
                'stats' => [
                    'total_users' => User::count(),
                    'total_products' => Product::count(),
                    'total_sales' => Sale::sum('total_price'),
                    'total_purchases' => Purchase::sum('total_price'),
                ]
            ]);
        }

        if ($user->role?->role_name === 'Admin') {
            return Inertia::render('Dashboard/AdminDashboard', [
                'recent_sales' => Sale::with('customer')->latest()->take(5)->get(),
                'low_stock_products' => Product::whereColumn('current_stock', '<=', 'minimum_stock')->take(5)->get(),
            ]);
        }

        // Dashboard default jika user tidak punya role spesifik
        return Inertia::render('Dashboard');
    }
}
