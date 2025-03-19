<?php

namespace App\Http\Controllers;

use App\Models\Category; //
use App\Models\Color;//
use App\Models\Discount;//
use App\Models\Material;//
use App\Models\Uom;//
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;


class SettingsController extends Controller
{
    //this index is for TABLE
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        //FOR TABLE PAGINATION AND SEARCH
        $colors = Color::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('hex', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query());

        $categories = Category::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query());

        $materials = Material::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query());

        $uoms = Uom::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query());

        $discounts = Discount::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query());

        return Inertia::render('Settings/Index', [
            'categories' => $categories,
            'materials' => $materials,
            'colors' => $colors,
            'uoms' => $uoms,
            'discounts' => $discounts,
            'filters' => $request->only('search')
        ]);
    }

    //store
    public function categoryStore(Request $request)
    {
        Category::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    public function colorStore(Request $request)
    {
        Color::create([
            'name' => $request->name,
            'hex' => $request->hex,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    public function discountStore(Request $request)
    {
        Discount::create([
            'name' => $request->name,
            'type' => $request->type,
            'amount' => $request->amount,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    public function materialStore(Request $request)
    {
        Material::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    public function uomStore(Request $request)
    {
        Uom::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    //update
    public function categoryUpdate(Request $request, Category $category)
    {
        $category->update([
            'name' => $request->name,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    public function colorUpdate(Request $request, Color $color)
    {
        $color->update([
            'name' => $request->name,
            'hex' => $request->hex,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    public function discountUpdate(Request $request, Discount $discount)
    {
        $discount->update([
            'name' => $request->name,
            'type' => $request->type,
            'amount' => $request->amount,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    public function materialUpdate(Request $request, Material $material)
    {
        $material->update([
            'name' => $request->name,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    public function uomUpdate(Request $request, Uom $uom)
    {
        $uom->update([
            'name' => $request->name,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('settings.index');
    }

    //destroy
    public function categoryDestroy(Category $category)
    {
        $category->delete();
        return redirect()->route('settings.index');
    }

    public function colorDestroy(Color $color)
    {
        $color->delete();
        return redirect()->route('settings.index');
    }

    public function discountDestroy(Discount $discount)
    {
        $discount->delete();
        return redirect()->route('settings.index');
    }

    public function materialDestroy(Material $material)
    {
        $material->delete();
        return redirect()->route('settings.index');
    }

    public function uomDestroy(Uom $uom)
    {
        $uom->delete();
        return redirect()->route('settings.index');
    }
}
