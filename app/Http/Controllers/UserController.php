<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        //FOR TABLE PAGINATION AND SEARCH
        $users = User::query()
            ->with(['role', 'warehouse'])
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('created_at', 'like', "%{$search}%")
                    ->orWhereHas('role', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('warehouse', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->paginate(5)
            ->appends($request->query());

        $roles = Role::select('id', 'name')
            ->get();
        $warehouses = Warehouse::select('id', 'name')->get();

        return Inertia::render('User/Index', [
            'users' => $users,
            'roles' => $roles,
            'warehouses' => $warehouses,
            'filters' => $request->only('search')
        ]);
    }

    //this STORE IS FOR CREATE
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'password' => 'required|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'warehouse_id' => $request->warehouse_id,
            'password' => $request->password,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('user.index');
    }

    //this UPDATE IS FOR EDIT
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|min:8',
        ]);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'warehouse_id' => $request->warehouse_id,
            'password' => $request->password,
            'updated_by' => Auth::id(),
        ]);
        return redirect()->route('user.index');
    }

    // FOR DELETE
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('user.index');
    }
}
