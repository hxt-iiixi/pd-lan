<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use App\Models\SalesItem;
use Illuminate\Support\Facades\DB;
class SaleController extends Controller
{
   public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|array',
            'quantity' => 'required|array',
            'discount_type' => 'required|array', // each discount type per item
        ]);

        DB::beginTransaction();

        try {
            $grandTotal = 0;

            // Create the parent sale record
            $sale = Sale::create([
                'discount_type' => 'NONE', // optional; handled per item
                'created_at' => now(),
            ]);

            foreach ($request->product_id as $index => $productId) {
                $product = Product::find($productId);

                if (!$product) continue;

                $quantity = (int) $request->quantity[$index];
                if ($quantity <= 0) continue;

                $discountType = $request->discount_type[$index] ?? 'NONE';
                $pricePerUnit = $product->price;

                // Apply item-level discount (if needed)
                $discountMultiplier = in_array($discountType, ['SC', 'PWD']) ? 0.8 : 1.0;
                $finalUnitPrice = $pricePerUnit * $discountMultiplier;
                $totalPrice = $finalUnitPrice * $quantity;

                // Save sale item
                SalesItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price_per_unit' => $finalUnitPrice,
                    'total_price' => $totalPrice,
                    'discount_type' => $discountType,
                ]);

                // Decrease stock
                $product->stock -= $quantity;
                $product->save();

                // Add to grand total
                $grandTotal += $totalPrice;
            }

            // Update parent sale totals
            $sale->update([
                'total_price' => $grandTotal,
                'discount_amount' => 0, // already factored in per item
                'net_total' => $grandTotal,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sale recorded successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:sales_items,id',
            'quantity' => 'required|integer|min:1',
            'original_quantity' => 'required|integer|min:1'
        ]);

        $item = \App\Models\SalesItem::with('product')->findOrFail($request->item_id);
        $product = $item->product;

        // Restore original stock first
        $product->increment('stock', $request->original_quantity);

        // Check for sufficient stock for new quantity
        if ($product->stock < $request->quantity) {
            return response()->json(['error' => 'Not enough stock to update.'], 400);
        }

        // Apply new quantity
        $product->decrement('stock', $request->quantity);

        $item->update([
            'quantity' => $request->quantity,
        ]);

        // Recalculate total for parent sale
        $sale = $item->sale;
        $totalPrice = 0;
        $totalQuantity = 0;

        foreach ($sale->items as $i) {
            $unitPrice = $i->product->selling_price;
            $discountMultiplier = $i->discount_type !== 'NONE' ? 0.8 : 1.0;
            $totalPrice += $unitPrice * $i->quantity * $discountMultiplier;
            $totalQuantity += $i->quantity;
        }

        $sale->update([
            'total_price' => $totalPrice,
            'quantity' => $totalQuantity,
        ]);

        return response()->json(['success' => 'Sale item updated.']);
    }

    public function destroy(Request $request)
    {
        $sale = Sale::with('items')->findOrFail($request->sale_id);

        // Increment stock for each product in the sale
        foreach ($sale->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        session(['last_deleted_sale' => $sale->toArray() + ['items' => $sale->items->toArray()]]);
        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted successfully.',
            'sale_id' => $sale->id
        ]);
    }

    public function history(Request $request)
{
    $query = Sale::with('product');

    $date = $request->input('date');

    // Fix manually typed d/m/Y format if needed
    if ($date && str_contains($date, '/')) {
        try {
            $date = \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            $date = null;
        }
    }

    if ($date) {
        $query->whereDate('created_at', $date);
    }

    $sales = $query->orderBy('created_at', 'desc')->get()
        ->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'));

    $dailySummary = [];

    foreach ($sales as $date => $daySales) {
        $totalSold = $daySales->sum('quantity');
        $totalProfit = $daySales->sum(fn($s) => $s->total_price);
        $dailySummary[] = [
            'date' => $date,
            'totalSold' => $totalSold,
            'totalProfit' => $totalProfit,
            'sales' => $daySales,
        ];
    }

    return view('inventory.history', compact('dailySummary'));

}


    public function reset()
    {
        \App\Models\Sale::truncate(); // Permanently deletes all sales
        return response()->json(['success' => true, 'message' => 'All sales have been reset.']);
    }

    public function undo(Request $request)
    {
        $lastDeleted = session('last_deleted_sale');

        if (!$lastDeleted || $lastDeleted['id'] != $request->sale_id) {
            return response()->json(['success' => false, 'message' => 'No sale to restore.']);
        }

        DB::beginTransaction();

        try {
            // Recreate the parent sale
            $sale = Sale::create([
                'discount_type' => $lastDeleted['discount_type'] ?? 'NONE',
                'total_price' => $lastDeleted['total_price'],
                'discount_amount' => $lastDeleted['discount_amount'],
                'net_total' => $lastDeleted['net_total'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Recreate each sales item
            foreach ($lastDeleted['items'] as $item) {
                SalesItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price_per_unit' => $item['price_per_unit'],
                    'total_price' => $item['total_price'],
                    'discount_type' => $item['discount_type'],
                ]);

                // Decrease stock accordingly
                Product::find($item['product_id'])?->decrement('stock', $item['quantity']);
            }

            DB::commit();
            session()->forget('last_deleted_sale');

            return response()->json(['success' => true, 'message' => 'Sale restored successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to restore sale.', 'error' => $e->getMessage()]);
        }
    }


}
