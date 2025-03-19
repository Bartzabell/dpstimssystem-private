<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Color;
use App\Models\InventoryStock;
use App\Models\Material;
use App\Models\Uom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    //this index is for TABLE
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        //FOR TABLE PAGINATION AND SEARCH
        $inventories = InventoryStock::query()
            ->with(['creator'])
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('item_code', 'like', "%{$search}%")
                    ->orWhere('item_qty', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('size', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('uom', 'like', "%{$search}%")
                    ->orWhere('price', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query());

        $categories = Category::select('id', 'name')
            ->get();
        $materials = Material::select('id', 'name')
            ->get();
        $colors = Color::select('id', 'name', 'hex')
            ->get();
        $uoms = Uom::select('id', 'name')
            ->get();

        return Inertia::render('Inventory/Index', [
            'inventories' => $inventories,
            'categories' => $categories,
            'materials' => $materials,
            'colors' => $colors,
            'uoms' => $uoms,
            'filters' => $request->only('search')
        ]);
    }

    public function store(Request $request)
    {
        $status = $this->getStatus($request->item_qty, $request->min_stock, $request->max_stock);

        InventoryStock::create([
            'name' => $request->name,
            'item_code' => $request->item_code,
            'item_qty' => $request->item_qty,
            'category' => $request->category,
            'material' => $request->material,
            'color' => $request->color,
            'uom' => $request->uom,
            'type' => $request->type,
            'size' => $request->size,
            'price' => $request->price,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'status' => $status,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('inventory.index');
    }

    // This UPDATE IS FOR EDIT
    public function update(Request $request, InventoryStock $inventory)
    {
        $status = $this->getStatus($request->item_qty, $request->min_stock, $request->max_stock);

        $inventory->update([
            'name' => $request->name,
            'item_code' => $request->item_code,
            'item_qty' => $request->item_qty,
            'category' => $request->category,
            'material' => $request->material,
            'color' => $request->color,
            'uom' => $request->uom,
            'type' => $request->type,
            'size' => $request->size,
            'price' => $request->price,
            'min_stock' => $request->min_stock,
            'max_stock' => $request->max_stock,
            'status' => $status,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('inventory.index');
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
    public function destroy(InventoryStock $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index');
    }
}
