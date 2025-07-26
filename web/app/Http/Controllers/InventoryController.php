<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;

class InventoryController extends Controller
{
    public function salesHistory()
    {
        $sales = \App\Models\Sale::with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'));

        $dailySummary = [];

        foreach ($sales as $date => $daySales) {
            $totalSold = 0;
            $totalProfit = 0;

            foreach ($daySales as $sale) {
                $saleTotal = $sale->items->sum('total_price');
                $discount = $sale->discount_amount ?? 0;

                foreach ($sale->items as $item) {
                    $totalSold += $item->quantity;

                    $product = $item->product;
                    $cost = $product->supplier_price ?? 0;

                    $itemPortion = $saleTotal > 0 ? ($item->total_price / $saleTotal) : 0;
                    $discountShare = $itemPortion * $discount;

                    $revenue = $item->total_price - $discountShare;
                    $netProfit = $revenue - ($cost * $item->quantity);

                    $totalProfit += $netProfit;
                }
            }

           $dailySummary[] = [
            'date' => $date,
            'totalSold' => $totalSold,
            'totalProfit' => $totalProfit,
            'grossProfit' => $daySales->sum(function ($sale) {
                return $sale->items->sum('total_price');
            }),
            'sales' => $daySales,
        ];
}


        return view('inventory.history', compact('dailySummary'));
    }


}
