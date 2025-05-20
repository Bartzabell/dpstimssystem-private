<?php
namespace App\Http\Controllers;
use App\Models\TransactionSalesBill;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
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

        // Get selected warehouse from request or default to 'general'
        $selectedWarehouse = $request->input('warehouse', 'general');

        // Get all warehouses for dropdown
        $warehouses = Warehouse::select('id', 'name')->get();
        $warehouses->prepend(['id' => 'general', 'name' => 'All Warehouses']);

        // Sales data queries
        $salesQuery = TransactionSalesBill::query();

        // Apply warehouse filter if not 'general'
        if ($selectedWarehouse !== 'general') {
            $salesQuery->whereHas('items.stock', function($q) use ($selectedWarehouse) {
                $q->where('warehouse_id', $selectedWarehouse);
            });
        }

        $todayTotalSales = $salesQuery->clone()->whereDate('date_sold', $today)->sum('total_price');
        $weeklyTotalSales = $salesQuery->clone()->whereBetween('date_sold', [$startOfWeek, $endOfWeek])->sum('total_price');
        $monthlySales = $salesQuery->clone()->whereBetween('date_sold', [$firstDayOfMonth, $lastDayOfMonth])->sum('total_price');

        $sales = $salesQuery->clone()
            ->with(['creator', 'customer', 'items', 'discount'])
            ->paginate(5)
            ->appends($request->query());

        // Inventory data queries
        $inventoryQuery = WarehouseStock::query();

        if ($selectedWarehouse !== 'general') {
            $inventoryQuery->where('warehouse_id', $selectedWarehouse);
        }

        $availableProducts = $inventoryQuery->clone()->where('item_qty', '>', 0)->count();
        $lowStatusProducts = $inventoryQuery->clone()->whereRaw('item_qty < min_stock')->count();
        $exceedingProducts = $inventoryQuery->clone()->whereRaw('item_qty > max_stock')->count();

        return Inertia::render('Dashboard', [
            'sales' => $sales,
            'todayTotalSales' => $todayTotalSales ?? 0,
            'weeklyTotalSales' => $weeklyTotalSales ?? 0,
            'monthlySales' => $monthlySales ?? 0,
            'availableProducts' => $availableProducts,
            'lowStatusProducts' => $lowStatusProducts,
            'exceedingProducts' => $exceedingProducts,
            'currentMonth' => $currentMonth,
            'warehouses' => $warehouses,
            'selectedWarehouse' => $selectedWarehouse,
        ]);
    }
}
