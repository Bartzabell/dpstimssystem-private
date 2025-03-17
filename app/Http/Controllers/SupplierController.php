<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    //this index is for TABLE
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        //FOR TABLE PAGINATION AND SEARCH
        $suppliers = Supplier::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('phone_no', 'like', "%{$search}%")
                    ->orWhere('street', 'like', "%{$search}%")
                    ->orWhere('municipality', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('tin_no', 'like', "%{$search}%");
            })
            ->paginate(5)
            ->appends($request->query());

        return Inertia::render('Supplier/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only('search')
        ]);
    }

    //this STORE IS FOR CREATE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_no' => 'required|string|min:10',
        ]);

        Supplier::create([
            'name' => $request->name,
            'phone_no' => $request->phone_no,
            'street' => $request->street,
            'municipality' => $request->municipality,
            'city' => $request->city,
            'email' => $request->email,
            'tin_no' =>$request->tin_no,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('supplier.index');
    }

    //this UPDATE IS FOR EDIT
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_no' => 'required|string|min:10',
        ]);

        $supplier->update([
            'name' => $request->name,
            'phone_no' => $request->phone_no,
            'street' => $request->street,
            'municipality' => $request->municipality,
            'city' => $request->city,
            'email' => $request->email,
            'tin_no' => $request->tin_no,
            'updated_by' => Auth::id(),
        ]);
        return redirect()->route('supplier.index');
    }

    // FOR DELETE
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('supplier.index');
    }
}
