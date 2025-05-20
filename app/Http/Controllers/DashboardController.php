<?php
namespace App\Http\Controllers;
use App\Models\TransactionSalesBill;
use App\Models\InventoryStock;
use App\Models\WarehouseStock;
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
        $currentMonth = Carbon::now()->format('F');
        $startOfWeek = Carbon::now()->startOfWeek(Carbon::SUNDAY)->startOfDay();
        $endOfWeek = Carbon::now()->endOfWeek(Carbon::SATURDAY)->endOfDay();

        // Sales data
        $todayTotalSales = TransactionSalesBill::whereDate('date_sold', $today)
            ->sum('total_price');
        $weeklyTotalSales = TransactionSalesBill::whereBetween('date_sold', [$startOfWeek, $endOfWeek])
            ->sum('total_price');
        $monthlySales = TransactionSalesBill::whereBetween('date_sold', [$firstDayOfMonth, $lastDayOfMonth])
            ->sum('total_price');
        $sales = TransactionSalesBill::query()
            ->with(['creator', 'customer', 'items', 'discount'])
            ->paginate(5)
            ->appends($request->query());

        // Inventory data
        $availableProducts = WarehouseStock::where('warehouse_id', auth()->user()->warehouse_id)->where('item_qty', '>', 0)->count();
        $lowStatusProducts = WarehouseStock::where('warehouse_id', auth()->user()->warehouse_id)->whereRaw('item_qty < min_stock')->count();
        $exceedingProducts = WarehouseStock::where('warehouse_id', auth()->user()->warehouse_id)->whereRaw('item_qty > max_stock')->count();

        return Inertia::render('Dashboard', [
            'sales' => $sales,
            'todayTotalSales' => $todayTotalSales ?? 0,
            'weeklyTotalSales' => $weeklyTotalSales ?? 0,
            'monthlySales' => $monthlySales ?? 0,
            'availableProducts' => $availableProducts,
            'lowStatusProducts' => $lowStatusProducts,
            'exceedingProducts' => $exceedingProducts,
            'currentMonth' => $currentMonth,
        ]);
    }
}
