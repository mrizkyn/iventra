<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Menampilkan laporan Hutang Usaha (Accounts Payable).
     */
  public function payable(Request $request)
{
    $query = Purchase::with('supplier')
        ->where('remaining_debt', '>', 0)
        ->where('approval_status', 'Approved')
        ->orderBy('purchase_date', 'asc');

    if ($request->has('search') && $request->search !== null) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('purchase_number', 'like', "%{$search}%")
              ->orWhereHas('supplier', function ($q2) use ($search) {
                  $q2->where('supplier_name', 'like', "%{$search}%");
              });
        });
    }

    return Inertia::render('Reports/Payable', [
        'payables' => $query->paginate(15)->withQueryString(),
        'filters' => $request->only('search'),
    ]);
}


    /**
     * Menampilkan laporan Piutang Usaha (Accounts Receivable).
     */
    public function receivable(Request $request)
{
    $query = Sale::with('customer')
        ->where('remaining_receivable', '>', 0)
        ->where('approval_status', 'Approved')
        ->orderBy('sale_date', 'asc');

    // Search jika ada input search
    if ($request->has('search') && $request->search !== null) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('sale_number', 'like', "%{$search}%")
              ->orWhereHas('customer', function ($q2) use ($search) {
                  $q2->where('customer_name', 'like', "%{$search}%");
              });
        });
    }

    return Inertia::render('Reports/Receivable', [
        'receivables' => $query->paginate(15)->withQueryString(),
        'filters' => $request->only('search'), // agar form search tetap terisi
    ]);
}

}
