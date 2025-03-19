<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Customer;
use App\Models\Discount;
use App\Models\TransactionSalesBill;
use App\Models\TransactionSalesItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SalesController extends Controller
{
    //this index is for TABLE
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id'); // Default sort field
        $sortDirection = $request->input('sort_direction', 'asc'); // Default sort direction

        //FOR TABLE PAGINATION AND SEARCH
        $forms = TransactionSalesBill::query()
            ->with(['creator', 'customer', 'items', 'discount']) // Eager load relationships
            ->when($search, function ($query, $search) {
                return $query->where('id', 'like', "%{$search}%")
                    ->orWhere('date_sold', 'like', "%{$search}%")
                    ->orWhere('total_price', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('discount', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($sortField, function ($query, $sortField) use ($sortDirection) {
                return $query->orderBy($sortField, $sortDirection);
            })
            ->paginate(5)
            ->appends($request->query());

        $customers = Customer::select('*')
            ->get();
        $inventories = InventoryStock::select('id', 'item_code', 'price')
            ->get();
        $discounts = Discount::select('*')
            ->get();

        return Inertia::render('Sales/Index', [
            'forms' => $forms,
            'customers' => $customers,
            'inventories' => $inventories,
            'discounts' => $discounts,
            'filters' => $request->only('search', 'sort_field', 'sort_direction')
        ]);
    }

    //this STORE IS FOR CREATE
    public function store(Request $request)
    {
        // Create
        $form = TransactionSalesBill::create([
            'customer_id' => $request->customer_id,
            'date_sold' => $request->date_sold,
            'discount_id' => $request->discount_id,
            'created_by' => Auth::id(),
        ]);

        // ADD ITEMs
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                TransactionSalesItem::create([
                    'tsb_id' => $form->id,
                    'stock_id' => $item['stock_id'],
                    'item_qty' => $item['item_qty'],
                    'item_price' => $item['item_price'],
                    'created_by' => Auth::id(),
                ]);

                $inventory = InventoryStock::find($item['stock_id']);
                $inventory->update([
                    'item_qty' => $inventory->item_qty - $item['item_qty'],
                ]);
                $status = $this->getStatus($inventory->item_qty, $inventory->min_stock, $inventory->max_stock);
                $inventory->update([
                    'status' => $status,
                ]);

                $bill = TransactionSalesBill::find($form->id);
                $bill->update([
                    'total_price' => $bill->total_price + $item['item_price'],
                ]);
            }
        }

        return redirect()->route('sales.index');
    }

    //this UPDATE IS FOR EDIT
    public function update(Request $request, TransactionSalesBill $form)
    {

        $form->update([
            'customer_id' => $request->customer_id,
            'date_sold' => $request->date_sold,
            'discount_id' => $request->discount_id,
            'updated_by' => Auth::id(),
        ]);

        // FOR ITEMS
        if ($request->has('items') && is_array($request->items)) {
            $existingItemIds = $form->items()->pluck('id')->toArray();
            $submittedItemIds = collect($request->items)
                                    ->pluck('id')
                                    ->filter()
                                    ->toArray();

            $itemsToDelete = array_diff($existingItemIds, $submittedItemIds);
            if (!empty($itemsToDelete)) {
                TransactionSalesItem::whereIn('id', $itemsToDelete)->delete();
            }

            foreach ($request->items as $item) {
                if (isset($item['id']) && $item['id']) {
                    TransactionSalesItem::where('id', $item['id'])->update([
                        'tsb_id' => $form->id,
                        'stock_id' => $item['stock_id'],
                        'item_qty' => $item['item_qty'],
                        'item_price' => $item['item_price'],
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    // Create new product
                    TransactionSalesItem::create([
                        'tsb_id' => $request->id,
                        'stock_id' => $item['stock_id'],
                        'item_qty' => $item['item_qty'],
                        'item_price' => $item['item_price'],
                        'created_by' => Auth::id(),
                    ]);

                    $inventory = InventoryStock::find($item['stock_id']);
                    $inventory->update([
                        'item_qty' => $inventory->item_qty - $item['item_qty'],
                    ]);
                    $status = $this->getStatus($inventory->item_qty, $inventory->min_stock, $inventory->max_stock);
                    $inventory->update([
                        'status' => $status,
                    ]);

                    $bill = TransactionSalesBill::find($form->id);
                    $bill->update([
                        'total_price' => $bill->total_price - $item['item_price'],
                    ]);
                }
            }
        } else {
            // If no items submitted, delete all existing items
            $form->items()->delete();
        }

        return redirect()->route('sales.index');
    }

    private function getStatus($itemQty, $minStock, $maxStock)
    {
        if ($itemQty < $minStock) {
            return 'low';
        } elseif ($itemQty > $maxStock) {
            return 'high';
        }

        return 'normal';
    }

    // FOR DELETE
    public function destroy(TransactionSalesBill $form)
    {
        $form->items()->delete();
        $form->delete();
        return redirect()->route('sales.index');
    }
}
