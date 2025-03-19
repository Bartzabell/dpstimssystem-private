<?php
namespace App\Http\Controllers;
use App\Models\TransactionSalesBill;
use App\Models\InventoryStock;
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

        // Sales data
        $todayTotalSales = TransactionSalesBill::whereDate('date_sold', $today)
            ->sum('total_price');
        $januaryTotalSales = TransactionSalesBill::whereBetween('date_sold', [$firstDayOfMonth, $lastDayOfMonth])
            ->sum('total_price');
        $sales = TransactionSalesBill::query()
            ->with(['creator', 'customer', 'items', 'discount'])
            ->paginate(5)
            ->appends($request->query());

        // Inventory data
        $availableProducts = InventoryStock::where('item_qty', '>', 0)->count();
        $lowStatusProducts = InventoryStock::whereRaw('item_qty < min_stock')->count();
        $exceedingProducts = InventoryStock::whereRaw('item_qty > max_stock')->count();

        return Inertia::render('Dashboard', [
            'sales' => $sales,
            'todayTotalSales' => $todayTotalSales ?? 0,
            'januaryTotalSales' => $januaryTotalSales ?? 0,
            'availableProducts' => $availableProducts,
            'lowStatusProducts' => $lowStatusProducts,
            'exceedingProducts' => $exceedingProducts,
        ]);
    }
}
