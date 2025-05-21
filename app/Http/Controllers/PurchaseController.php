<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\WarehouseStock;
use App\Models\Supplier;
use App\Models\TransactionPurchaseBill;
use App\Models\TransactionPurchaseItem;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    //this index is for TABLE
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $warehouseId = $request->input('warehouse_id', 1);

        //FOR TABLE PAGINATION, SEARCH, AND SORTING
        $forms = TransactionPurchaseBill::query()
            ->with(['creator', 'supplier', 'items', 'warehouse']) // Eager load relationships
            ->when($search, function ($query, $search) {
                return $query->where('id', 'like', "%{$search}%")
                    ->orWhere('date_purchased', 'like', "%{$search}%")
                    ->orWhere('total_price', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($sortField, function ($query, $sortField) use ($sortDirection) {
                return $query->orderBy($sortField, $sortDirection);
            })
            ->when($warehouseId, function ($query, $warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            // ->where('warehouse_id', '=', Auth::user()->warehouse_id)
            ->paginate(5)
            ->appends($request->query());

        $suppliers = Supplier::select('id', 'name', 'phone_no', 'tin_no')
            ->get();
        $inventories = WarehouseStock::select('id', 'item_code', 'warehouse_id')->get();

        $warehouses = Warehouse::select('id', 'name')->get();

        return Inertia::render('Purchase/Index', [
            'forms' => $forms,
            'suppliers' => $suppliers,
            'inventories' => $inventories,
            'warehouses' => $warehouses,
            'filters' => $request->only('search', 'sort_field', 'sort_direction', 'warehouse_id')
        ]);
    }

    //this STORE IS FOR CREATE
    public function store(Request $request)
    {
        // Create
        $form = TransactionPurchaseBill::create([
            'supplier_id' => $request->supplier_id,
            'date_purchased' => $request->date_purchased,
            'warehouse_id' => $request->warehouse_id,
            'created_by' => Auth::id(),
        ]);

        // ADD ITEMs
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                TransactionPurchaseItem::create([
                    'tpb_id' => $form->id,
                    'stock_id' => $item['stock_id'],
                    'item_qty' => $item['item_qty'],
                    'item_price' => $item['item_price'],
                    'created_by' => Auth::id(),
                ]);

                $inventory = WarehouseStock::find($item['stock_id']);
                $inventory->update([
                    'item_qty' => $inventory->item_qty + $item['item_qty'],
                ]);
                $status = $this->getStatus($inventory->item_qty, $inventory->min_stock, $inventory->max_stock);
                $inventory->update([
                    'status' => $status,
                ]);

                $bill = TransactionPurchaseBill::find($form->id);
                $bill->update([
                    'total_price' => $bill->total_price + $item['item_price'],
                ]);
            }
        }

        return redirect()->route('purchase.index');
    }

    //this UPDATE IS FOR EDIT
    public function update(Request $request, TransactionPurchaseBill $form)
    {

        $form->update([
            'supplier_id' => $request->supplier_id,
            'date_purchased' => $request->date_purchased,
            'warehouse_id' => $request->warehouse_id,
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
                TransactionPurchaseItem::whereIn('id', $itemsToDelete)->delete();
            }

            foreach ($request->items as $item) {
                if (isset($item['id']) && $item['id']) {
                    TransactionPurchaseItem::where('id', $item['id'])->update([
                        'tpb_id' => $form->id,
                        'stock_id' => $item['stock_id'],
                        'item_qty' => $item['item_qty'],
                        'item_price' => $item['item_price'],
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    // Create new product
                    TransactionPurchaseItem::create([
                        'tpb_id' => $request->id,
                        'stock_id' => $item['stock_id'],
                        'item_qty' => $item['item_qty'],
                        'item_price' => $item['item_price'],
                        'created_by' => Auth::id(),
                    ]);

                    $inventory = WarehouseStock::find($item['stock_id']);
                    $inventory->update([
                        'item_qty' => $inventory->item_qty + $item['item_qty'],
                    ]);
                    $status = $this->getStatus($inventory->item_qty, $inventory->min_stock, $inventory->max_stock);
                    $inventory->update([
                        'status' => $status,
                    ]);

                    $bill = TransactionPurchaseBill::find($form->id);
                    $bill->update([
                        'total_price' => $bill->total_price + $item['item_price'],
                    ]);
                }
            }
        } else {
            // If no items submitted, delete all existing items
            $form->items()->delete();
        }

        return redirect()->route('purchase.index');
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
    public function destroy(TransactionPurchaseBill $form)
    {
        $form->items()->delete();
        $form->delete();
        return redirect()->route('purchase.index');
    }
}
