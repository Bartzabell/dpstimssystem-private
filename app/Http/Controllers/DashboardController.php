<?php

namespace App\Http\Controllers;

use App\Models\TransactionSalesBill;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $today = Carbon::today()->toDateString();

        $firstDayOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $lastDayOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $todayTotalSales = TransactionSalesBill::whereDate('date_sold', $today)
            ->sum('total_price');

        $januaryTotalSales = TransactionSalesBill::whereBetween('date_sold', [$firstDayOfMonth, $lastDayOfMonth])
            ->sum('total_price');

        $sales = TransactionSalesBill::query()
            ->with(['creator', 'customer', 'items', 'discount'])
            ->paginate(5)
            ->appends($request->query());

        return Inertia::render('Dashboard', [
            'sales' => $sales,
            'todayTotalSales' => $todayTotalSales ?? 0,
            'januaryTotalSales' => $januaryTotalSales ?? 0,
        ]);
    }
}
