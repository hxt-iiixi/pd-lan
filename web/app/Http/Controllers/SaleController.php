<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use App\Models\SalesItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class SaleController extends Controller
{
 public function store(Request $request)
    { 
       $productIds = $request->input('product_ids');
        $quantities = $request->input('quantity');

        $items = [];
        foreach ($productIds as $index => $productId) {
            $items[] = [
                'product_id' => $productId,
                'quantity' => $quantities[$index],
            ];
}

        // Inject the 'items' array into the request for validation
        $request->merge(['items' => $items]);

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_type' => 'nullable|in:NONE,SENIOR,PWD'
        ]);
        
       
        DB::beginTransaction();
        try {
            $totalBeforeDiscount = 0;

            // Create Sale
            $sale = Sale::create([
                'discount_type' => $request->discount_type ?? 'NONE'
            ]);

            $itemsForResponse = []; // Collect info for all items

                foreach ($request->items as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $qty = $item['quantity'];
                    $subtotal = $product->selling_price * $qty;
                    $totalBeforeDiscount += $subtotal;

                    // ✅ Calculate profit here
                    $profit = ($product->selling_price - $product->supplier_price) * $qty;

                    // Create the sale item
                    SalesItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'total_price' => $subtotal,
                        // Optional: You can add 'profit' => $profit here if your sales_items table has a profit column
                    ]);

                    $product->decrement('stock', $qty);

                    // Add to response
                    $itemsForResponse[] = [
                        'product' => $product->name,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'subtotal' => number_format($subtotal, 2),
                        'profit' => number_format($profit, 2) // Optional: include this in response
                    ];
                }

           if (in_array($request->discount_type, ['SENIOR', 'PWD'])) {
                $discount = $totalBeforeDiscount * 0.20;
            } else {
                $discount = 0;
            }

            $finalTotal = $totalBeforeDiscount - $discount;

            $sale->update([
                'total_price' => round($finalTotal, 2),
                'discount_type' => $request->discount_type,
                'discount_amount' => round($discount, 2),
            ]);
            DB::commit();
               if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Sale logged successfully',
                        'sale_id' => $sale->id,
                        'sale_date' => $sale->created_at->format('M d, Y h:i A'),
                        'discount_type' => $sale->discount_type ?? 'NONE',
                        'total_price' => number_format($sale->total_price, 2),
                        'items' => $itemsForResponse,
                        'updatedTotalProfit' => Sale::sum('total_price'),
                        'updatedTotalSold' => SalesItem::sum('quantity'),
                    ]);
                }


            return redirect()->back()->with('success', 'Sale logged successfully.');

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Sale store failed: " . $e->getMessage());

                if ($request->ajax()) {
                    return response()->json(['error' => $e->getMessage()], 500); // <- SHOW actual error
                }

                return redirect()->back()->with('error', 'Failed to log sale.');
            }
    }





    public function update(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'quantity' => 'required|integer|min:1',
            'original_quantity' => 'required|integer|min:1'
        ]);

        $sale = Sale::findOrFail($request->sale_id);
        $product = Product::findOrFail($sale->product_id);

        $product->increment('stock', $request->original_quantity);

        if ($product->stock < $request->quantity) {
            return response()->json(['error' => 'Not enough stock to update.'], 400);
        }

        $product->decrement('stock', $request->quantity);

        $sale->update([
    
            'quantity' => $request->quantity,
            'total_price' => $product->selling_price * $request->quantity,
        ]);

        return response()->json(['success' => 'Sale updated.']);
    }

    public function destroy(Request $request)
    {
        $sale = Sale::findOrFail($request->sale_id);
        $product = Product::findOrFail($sale->product_id);

        // Increment stock
        $product->increment('stock', $sale->quantity);

        // Store sale in session before deletion
        session(['last_deleted_sale' => $sale->toArray()]);

        $sale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted successfully.',
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'updatedStock' => $product->stock
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

        $sale = new Sale();
        
        $sale->product_id = $lastDeleted['product_id'];
        $sale->quantity = $lastDeleted['quantity'];
        $sale->discount_type = $lastDeleted['discount_type'];
        $sale->total_price = $lastDeleted['total_price'];
        $sale->created_at = now();
        $sale->updated_at = now();
        $sale->save();


        // Restore stock
        $product = Product::find($sale->product_id);
        $product->stock -= $sale->quantity;
        $product->save();

        session()->forget('last_deleted_sale');

        return response()->json([
            'success' => true,
            'message' => 'Sale restored successfully.'
        ]);
    }


}
