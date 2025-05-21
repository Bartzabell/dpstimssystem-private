<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    //this index is for TABLE
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');
        $warehouseId = $request->input('warehouse_id', 1);

        //FOR TABLE PAGINATION AND SEARCH
        $warehouseStocks = WarehouseStock::query()
            ->with(['creator', 'inventory', 'warehouse'])
            ->when($search, function ($query, $search) {
                return $query->where('id', 'like', "%{$search}%")
                    ->orWhereHas('inventory', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('item_code', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%")
                            ->orWhere('size', 'like', "%{$search}%")
                            ->orWhere('type', 'like', "%{$search}%")
                            ->orWhere('material', 'like', "%{$search}%")
                            ->orWhere('color', 'like', "%{$search}%")
                            ->orWhere('uom', 'like', "%{$search}%")
                            ->orWhere('price', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('item_qty', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->when($warehouseId, function ($query, $warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->when($sortField, function ($query, $sortField) use ($sortDirection) {
                // Handle sorting by relationship fields
                if ($sortField === 'inventory.name') {
                    return $query->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id')
                                ->orderBy('inventory_stocks.name', $sortDirection)
                                ->select('warehouse_stocks.*');
                } elseif ($sortField === 'item_code') {
                    return $query->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id')
                                ->orderBy('inventory_stocks.item_code', $sortDirection)
                                ->select('warehouse_stocks.*');
                } elseif ($sortField === 'category') {
                    return $query->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id')
                                ->orderBy('inventory_stocks.category', $sortDirection)
                                ->select('warehouse_stocks.*');
                } elseif ($sortField === 'size') {
                    return $query->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id')
                                ->orderBy('inventory_stocks.size', $sortDirection)
                                ->select('warehouse_stocks.*');
                } elseif ($sortField === 'type') {
                    return $query->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id')
                                ->orderBy('inventory_stocks.type', $sortDirection)
                                ->select('warehouse_stocks.*');
                } elseif ($sortField === 'material') {
                    return $query->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id')
                                ->orderBy('inventory_stocks.material', $sortDirection)
                                ->select('warehouse_stocks.*');
                } elseif ($sortField === 'color') {
                    return $query->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id')
                                ->orderBy('inventory_stocks.color', $sortDirection)
                                ->select('warehouse_stocks.*');
                } elseif ($sortField === 'uom') {
                    return $query->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id')
                                ->orderBy('inventory_stocks.uom', $sortDirection)
                                ->select('warehouse_stocks.*');
                } elseif ($sortField === 'warehouse.name') {
                    return $query->join('warehouses', 'warehouse_stocks.warehouse_id', '=', 'warehouses.id')
                                ->orderBy('warehouses.name', $sortDirection)
                                ->select('warehouse_stocks.*');
                } else {
                    return $query->orderBy($sortField, $sortDirection);
                }
            })
            ->paginate(5)
            ->appends($request->query());

        $inventories = InventoryStock::select('item_code', 'name', 'id')->get();
        $warehouses = Warehouse::select('id', 'name')->get();

        return Inertia::render('Warehouse/Index', [
            'warehouseStocks' => $warehouseStocks,
            'warehouses' => $warehouses,
            'inventories' => $inventories,
            'filters' => $request->only('search', 'sort_field', 'sort_direction', 'warehouse_id')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'inventory_id' => 'required|exists:inventory_stocks,id',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);
        // Check if a warehouse stock with the same warehouse_id and inventory_id already exists
        $existingStock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
                                      ->where('inventory_id', $request->inventory_id)
                                      ->first();

        if ($existingStock) {
            return back()->withErrors(['error' => 'A stock with this product already exists in your warehouse.']);
        }

        $status = $this->getStatus($request->item_qty, $request->min_stock, $request->max_stock);

        WarehouseStock::create([
            'inventory_id' => $request->inventory_id,
            'warehouse_id' => $request->warehouse_id,
            'item_qty' => $request->item_qty,
            'price' => $request->price,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'status' => $status,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('warehouse.index')->with('success', 'Product added successfully.');
    }

    public function update(Request $request, WarehouseStock $warehouseStock)
    {
        // If inventory_id is being changed, check for duplicates
        if ($warehouseStock->inventory_id != $request->inventory_id) {
            $existingStock = WarehouseStock::where('warehouse_id', $request->warehouse_id)
                                         ->where('inventory_id', $request->inventory_id)
                                         ->first();

            if ($existingStock) {
                // Return with error message if duplicate found
                return redirect()->back()->with('error', 'A stock with this product already exists in your warehouse.');
            }
        }

        $status = $this->getStatus($request->item_qty, $request->min_stock, $request->max_stock);

        $warehouseStock->update([
            'inventory_id' => $request->inventory_id,
            'warehouse_id' => $request->warehouse_id,
            'item_qty' => $request->item_qty,
            'price' => $request->price,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'status' => $status,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('warehouse.index')->with('success', 'Product updated successfully.');
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
    public function destroy(WarehouseStock $warehouseStock)
    {
        $warehouseStock->delete();
        return redirect()->route('warehouse.index')->with('success', 'Product deleted successfully.');
    }
}
