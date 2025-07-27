<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\ReturnModel;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReturnModelController extends Controller
{
    public function index(Request $request)
    {
        $returns = ReturnModel::with(['purchase.supplier', 'sale.customer'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('return_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Returns/Index', [
            'returns' => $returns,
            'filters' => $request->only(['search']),
        ]);
    }

  public function create()
{
    return Inertia::render('Returns/Create', [
        'purchases' => Purchase::where('approval_status', 'Approved')
                               ->where('return_status', '!=', 'Fully Returned')
                               ->get(['id', 'purchase_number']),
        'sales' => Sale::where('approval_status', 'Approved')
                       ->where('return_status', '!=', 'Fully Returned')
                       ->get(['id', 'sale_number']),
    ]);
}

  public function store(Request $request)
{
    $request->validate([
        'return_date' => 'required|date',
        'return_type' => 'required|string|in:Purchase,Sale',
        'transaction_id' => 'required|integer',
        'reason' => 'nullable|string',
        'details' => 'required|array|min:1',
        'details.*.product_id' => 'required|exists:products,id',
        'details.*.quantity' => 'required|integer|min:1',
    ]);

    try {
        DB::transaction(function () use ($request) {
            $return = ReturnModel::create([
                'return_number' => 'RT/' . date('Ymd') . '/' . (ReturnModel::count() + 1),
                'return_date' => $request->return_date,
                'return_type' => $request->return_type,
                'related_transaction_id' => $request->transaction_id,
                'reason' => $request->reason,
            ]);

            if ($request->return_type === 'Sale') {
                $sale = Sale::with('details')->findOrFail($request->transaction_id);
                $totalReturnValue = 0;

                foreach ($request->details as $detail) {
                    // PERBAIKAN DI SINI
                    $return->details()->create([
                        'product_id' => $detail['product_id'],
                        'quantity' => $detail['quantity'],
                    ]);

                    Product::find($detail['product_id'])->increment('current_stock', $detail['quantity']);
                    $originalDetail = $sale->details->firstWhere('product_id', $detail['product_id']);
                    if ($originalDetail) {
                        $totalReturnValue += $detail['quantity'] * $originalDetail->sell_price_per_unit;
                    }
                }

                $sale->decrement('remaining_receivable', $totalReturnValue);
                $totalOriginalQty = $sale->details->sum('quantity');
                $totalExistingReturns = ReturnModel::where('return_type', 'Sale')->where('related_transaction_id', $sale->id)->with('details')->get()->sum(fn($r) => $r->details->sum('quantity'));
                $sale->return_status = $totalExistingReturns >= $totalOriginalQty ? 'Fully Returned' : 'Partially Returned';

                if($sale->remaining_receivable <= 0) {
                    $sale->payment_status = 'Paid';
                }
                $sale->save();

            } else { // Purchase
                $purchase = Purchase::with('details')->findOrFail($request->transaction_id);
                $totalReturnValue = 0;

                foreach ($request->details as $detail) {
                    // PERBAIKAN DI SINI JUGA
                    $return->details()->create([
                        'product_id' => $detail['product_id'],
                        'quantity' => $detail['quantity'],
                    ]);

                    Product::find($detail['product_id'])->decrement('current_stock', $detail['quantity']);
                    $originalDetail = $purchase->details->firstWhere('product_id', $detail['product_id']);
                    if ($originalDetail) {
                        $totalReturnValue += $detail['quantity'] * $originalDetail->buy_price_per_unit;
                    }
                }

                $purchase->decrement('remaining_debt', $totalReturnValue);
                $totalOriginalQty = $purchase->details->sum('quantity');
                $totalExistingReturns = ReturnModel::where('return_type', 'Purchase')->where('related_transaction_id', $purchase->id)->with('details')->get()->sum(fn($r) => $r->details->sum('quantity'));
                $purchase->return_status = $totalExistingReturns >= $totalOriginalQty ? 'Fully Returned' : 'Partially Returned';

                if($purchase->remaining_debt <= 0) {
                    $purchase->payment_status = 'Paid';
                }
                $purchase->save();
            }
        });
    } catch (\Exception $e) {
        return redirect()->back()->withErrors(['error' => 'Failed to record return! ' . $e->getMessage()]);
    }

    return redirect()->route('returns.index')->with('message', 'Return has been recorded successfully.');
}

    public function show(ReturnModel $return)
    {
        $return->load(['details.product']);
        if ($return->return_type === 'Purchase') {
            $return->load('purchase.supplier');
        } else {
            $return->load('sale.customer');
        }
        return Inertia::render('Returns/Show', ['returnData' => $return]);
    }

    public function getTransactionDetails($type, $id)
    {
        if ($type === 'Sale') {
            $transaction = Sale::with('details.product')->find($id);
        } elseif ($type === 'Purchase') {
            $transaction = Purchase::with('details.product')->find($id);
        } else {
            return response()->json(['error' => 'Invalid transaction type'], 400);
        }

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        $details = $transaction->details->map(function ($detail) {
            return [
                'product_id' => $detail->product->id,
                'product_name' => $detail->product->product_name,
                'max_quantity' => $detail->quantity,
            ];
        });

        return response()->json($details);
    }
}
