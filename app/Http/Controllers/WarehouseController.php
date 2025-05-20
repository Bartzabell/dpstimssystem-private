<?php

namespace App\Http\Controllers;

use App\Models\InventoryStock;
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
        $sortField = $request->input('sort_field', 'id'); // Default sort field
        $sortDirection = $request->input('sort_direction', 'asc'); // Default sort direction

        //FOR TABLE PAGINATION AND SEARCH
        $warehouseStocks = WarehouseStock::query()
            ->with(['creator', 'inventory'])
            ->when($search, function ($query, $search) {
                return $query->where('inventory.name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('inventory.item_code', 'like', "%{$search}%")
                    ->orWhere('item_qty', 'like', "%{$search}%")
                    ->orWhere('inventory.category', 'like', "%{$search}%")
                    ->orWhere('inventory.size', 'like', "%{$search}%")
                    ->orWhere('inventory.type', 'like', "%{$search}%")
                    ->orWhere('inventory.material', 'like', "%{$search}%")
                    ->orWhere('inventory.color', 'like', "%{$search}%")
                    ->orWhere('inventory.uom', 'like', "%{$search}%")
                    ->orWhere('inventory.price', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->when($sortField, function ($query, $sortField) use ($sortDirection) {
                return $query->orderBy($sortField, $sortDirection);
            })
            ->where('warehouse_id', '=', Auth::user()->warehouse_id)
            ->paginate(5)
            ->appends($request->query());

        $inventories = InventoryStock::select('item_code', 'name', 'id')->get();

        return Inertia::render('Warehouse/Index', [
            'warehouseStocks' => $warehouseStocks,
            'inventories' => $inventories,
            'filters' => $request->only('search', 'sort_field', 'sort_direction')
        ]);
    }

    public function store(Request $request)
    {
        $status = $this->getStatus($request->item_qty, $request->min_stock, $request->max_stock);

        WarehouseStock::create([
            'inventory_id' => $request->inventory_id,
            'warehouse_id' => Auth::user()->warehouse_id,
            'item_qty' => $request->item_qty,
            'price' => $request->price,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'status' => $status,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('warehouse.index');
    }

    public function update(Request $request, WarehouseStock $warehouseStock)
    {
        $status = $this->getStatus($request->item_qty, $request->min_stock, $request->max_stock);

        $warehouseStock->update([
            'inventory_id' => $request->inventory_id,
            'item_qty' => $request->item_qty,
            'price' => $request->price,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'status' => $status,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('warehouse.index');
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
        return redirect()->route('warehouse.index');
    }
}
