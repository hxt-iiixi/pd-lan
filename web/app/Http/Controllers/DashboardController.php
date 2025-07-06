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

        // ✅ Use sale.created_at instead of sales_items.created_at
        $todaySales = SalesItem::with('product', 'sale')
            ->whereHas('sale', function ($query) use ($today) {
                $query->whereDate('created_at', $today);
            })
            ->orderByDesc('created_at')
            ->get();

        // 🔢 Total products
        $totalProducts = Product::count();

        // 💰 Profit and quantity
        $totalProfit = $todaySales->sum('total_price');
        $totalSoldQty = $todaySales->sum('quantity');

        // 🔝 Most sold products today
        $soldDetails = $todaySales
            ->groupBy('product_id')
            ->map(function ($items) {
                return (object)[
                    'product' => $items->first()->product,
                    'total_quantity' => $items->sum('quantity')
                ];
            })
            ->sortByDesc('total_quantity');

        // 📉 Low stock
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
