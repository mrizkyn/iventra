<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Tampilkan dashboard untuk Owner
        if ($user->role?->role_name === 'Owner') {
            // Data untuk Grafik Sales & Purchases per bulan (6 bulan terakhir)
            $monthlySales = Sale::select(
                DB::raw('SUM(total_price) as total'),
                DB::raw("DATE_FORMAT(sale_date, '%Y-%m') as month")
            )
            ->where('approval_status', 'Approved') // Hanya hitung yang sudah disetujui
            ->where('sale_date', '>=', now()->subMonths(5))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

            $monthlyPurchases = Purchase::select(
                DB::raw('SUM(total_price) as total'),
                DB::raw("DATE_FORMAT(purchase_date, '%Y-%m') as month")
            )
            ->where('approval_status', 'Approved') // Hanya hitung yang sudah disetujui
            ->where('purchase_date', '>=', now()->subMonths(5))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

            // Data untuk 5 produk terlaris
            $topSellingProducts = Product::whereHas('saleDetails.sale', function ($query) {
                    $query->where('approval_status', 'Approved');
                })
                ->withCount(['saleDetails as quantity_sold' => function ($query) {
                    $query->select(DB::raw('sum(quantity)'));
                }])
                ->orderBy('quantity_sold', 'desc')
                ->take(5)
                ->get();

            return Inertia::render('Dashboard/OwnerDashboard', [
                'stats' => [
                    'total_revenue' => Sale::where('approval_status', 'Approved')->sum('total_price'),
                    'total_expense' => Purchase::where('approval_status', 'Approved')->sum('total_price'),
                    'pending_purchases' => Purchase::where('approval_status', 'Pending')->count(),
                    'pending_sales' => Sale::where('approval_status', 'Pending')->count(),
                    'total_customers' => Customer::count(),
                    'total_suppliers' => Supplier::count(),
                    'total_sale_returns' => ReturnModel::where('return_type', 'Sale')->count(),
                    'total_purchase_returns' => ReturnModel::where('return_type', 'Purchase')->count(),
                ],
                'chartData' => [
                    'monthly_sales' => $monthlySales,
                    'monthly_purchases' => $monthlyPurchases,
                ],
                'topSellingProducts' => $topSellingProducts,
                'recentActivities' => Sale::with('customer')->where('approval_status', 'Approved')->latest()->take(5)->get(),
            ]);
        }

        // Tampilkan dashboard untuk Admin
        if ($user->role?->role_name === 'Admin') {
            return Inertia::render('Dashboard/AdminDashboard', [
                'stats' => [
                    'pending_purchases' => Purchase::where('approval_status', 'Pending')->count(),
                    'pending_sales' => Sale::where('approval_status', 'Pending')->count(),
                ],
                'recent_sales' => Sale::with('customer')->latest()->take(5)->get(),
                'low_stock_products' => Product::whereColumn('current_stock', '<=', 'minimum_stock')->where('minimum_stock', '>', 0)->take(5)->get(),
            ]);
        }

        // Dashboard default jika user tidak punya role spesifik
        return Inertia::render('Dashboard');
    }
}
