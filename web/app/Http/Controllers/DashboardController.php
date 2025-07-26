<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use App\Models\SalesItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
  public function index()
    {
        $today = Carbon::today();

  
        $todaySales = SalesItem::with(['product', 'sale'])
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();

    
        $totalProducts = Product::count();

      
        $totalProfit = Sale::whereDate('created_at', $today)
        ->get()
        ->sum(fn($sale) => $sale->total_price ?? 0);
        $totalSoldQty = SalesItem::whereDate('created_at', $today)->sum('quantity');

    $totalCombinedProfit = 0;

    foreach ($todaySales->groupBy('sale_id') as $saleItems) {
    $sale = $saleItems->first()->sale;
    $saleProfit = 0;

    foreach ($saleItems as $item) {
        $product = $item->product;
        if (!$product || !$sale) continue;

        $selling = $product->selling_price;
        $cost = $product->supplier_price;
        $qty = $item->quantity;

        $saleProfit += ($selling - $cost) * $qty;
    }

    // Subtract discount ONCE per sale
    $saleProfit -= $sale->discount_amount ?? 0;

    $totalCombinedProfit += $saleProfit;
}

    // ✅ Final clean assignment
    $combinedProfit = $totalCombinedProfit;



        $soldDetails = $todaySales
            ->groupBy('product_id')
            ->map(function ($group) {
                return (object)[
                    'product' => $group->first()->product,
                    'total_quantity' => $group->sum('quantity')
                ];
            })
            ->sortByDesc('total_quantity');

       
        $lowStock = Product::where('stock', '<', 50)
            ->orderBy('stock', 'asc')
            ->get();

        $products = Product::orderBy('name')->get();

        return view('inventory.dashboard', compact(
            'todaySales',
            'totalProducts',
            'totalProfit',
            'totalSoldQty',
            'soldDetails',
            'lowStock',
            'combinedProfit',
            'products'
        ));
    }

   public function chartData($type)
    {
        // ✅ TEMP: Test block for "profit"
        if ($type === 'profit') {
    return response()->json([
        'labels' => ['Jan', 'Feb', 'Mar'],
        'values' => [100, 200, 150]
    ]);
    } elseif ($type === 'sold') {
        return response()->json([
            'labels' => ['Jan', 'Feb', 'Mar'],
            'values' => [90, 20, 15]
        ]);
    } 


    // fallback (shouldn't be triggered)
    return response()->json(['labels' => [], 'values' => []]);
}

}
