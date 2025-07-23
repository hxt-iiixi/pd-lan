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

                    // Avoid division by zero
                    $itemPortion = $saleTotal > 0 ? ($item->total_price / $saleTotal) : 0;
                    $discountShare = $itemPortion * $discount;

                    $profitAfterDiscount = $item->total_price - $discountShare;
                    $totalProfit += $profitAfterDiscount;
                }
            }

            $dailySummary[] = [
                'date' => $date,
                'totalSold' => $totalSold,
                'totalProfit' => $totalProfit,
                'sales' => $daySales,
            ];
        }

        return view('inventory.history', compact('dailySummary'));
    }


}
