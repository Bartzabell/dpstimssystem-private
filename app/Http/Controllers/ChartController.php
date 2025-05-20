<?php
namespace App\Http\Controllers;
use App\Models\TransactionSalesBill;
use App\Models\TransactionSalesItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChartController extends Controller
{
    public function getMonthlyIncome($year)
    {
        $monthlyData = array_fill(0, 12, 0);
        $user = Auth::user();
        $connection = DB::connection()->getDriverName();

        $query = TransactionSalesBill::query();

        // Filter by warehouse if user is not admin
        if ($user->role_id != 1) {
            $query->whereHas('items.stock', function($q) use ($user) {
                $q->where('warehouse_id', $user->warehouse_id);
            });
        }

        if ($connection === 'pgsql') {
            $results = $query->select(
                DB::raw('EXTRACT(MONTH FROM date_sold) as month'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereRaw('EXTRACT(YEAR FROM date_sold) = ?', [$year])
            ->groupBy(DB::raw('EXTRACT(MONTH FROM date_sold)'))
            ->get();
        } else {
            $results = $query->select(
                DB::raw('MONTH(date_sold) as month'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereYear('date_sold', $year)
            ->groupBy(DB::raw('MONTH(date_sold)'))
            ->get();
        }

        foreach ($results as $result) {
            $monthIndex = (int) $result->month - 1;
            $monthlyData[$monthIndex] = (float) $result->total;
        }

        return response()->json($monthlyData);
    }

    public function getAvailableYears()
    {
        $user = Auth::user();
        $connection = DB::connection()->getDriverName();

        $query = TransactionSalesBill::query();

        // Filter by warehouse if user is not admin
        if ($user->role_id != 1) {
            $query->whereHas('items.stock', function($q) use ($user) {
                $q->where('warehouse_id', $user->warehouse_id);
            });
        }

        if ($connection === 'pgsql') {
            $years = $query->select(
                DB::raw('DISTINCT EXTRACT(YEAR FROM date_sold) as year')
            )
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
        } else {
            $years = $query->select(
                DB::raw('DISTINCT YEAR(date_sold) as year')
            )
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
        }

        return response()->json($years);
    }

    public function getMonthlySalesQuantity($year)
    {
        $user = Auth::user();
        $connection = DB::connection()->getDriverName();

        $query = TransactionSalesItem::query()
            ->join('transaction_sales_bills', 'transaction_sales_items.tsb_id', '=', 'transaction_sales_bills.id')
            ->join('warehouse_stocks', 'transaction_sales_items.stock_id', '=', 'warehouse_stocks.id')
            ->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id');

        // Filter by warehouse if user is not admin
        if ($user->role_id != 1) {
            $query->where('warehouse_stocks.warehouse_id', $user->warehouse_id);
        }

        if ($connection === 'pgsql') {
            $results = $query->select(
                DB::raw('EXTRACT(MONTH FROM transaction_sales_bills.date_sold) as month'),
                'inventory_stocks.name as item_name',
                'warehouse_stocks.id as item_id',
                DB::raw('SUM(transaction_sales_items.item_qty) as total')
            )
            ->whereRaw('EXTRACT(YEAR FROM transaction_sales_bills.date_sold) = ?', [$year])
            ->groupBy(DB::raw('EXTRACT(MONTH FROM transaction_sales_bills.date_sold)'), 'warehouse_stocks.id')
            ->get();
        } else {
            $results = $query->select(
                DB::raw('MONTH(transaction_sales_bills.date_sold) as month'),
                'inventory_stocks.name as item_name',
                'warehouse_stocks.id as item_id',
                DB::raw('SUM(transaction_sales_items.item_qty) as total')
            )
            ->whereYear('transaction_sales_bills.date_sold', $year)
            ->groupBy(DB::raw('MONTH(transaction_sales_bills.date_sold)'), 'warehouse_stocks.id')
            ->get();
        }

        // Structure the data for the chart
        $monthlyData = [];
        $items = [];

        foreach ($results as $result) {
            $monthIndex = (int) $result->month - 1;
            $itemId = $result->item_id;

            if (!isset($monthlyData[$itemId])) {
                $monthlyData[$itemId] = array_fill(0, 12, 0);
            }

            $monthlyData[$itemId][$monthIndex] = (int) $result->total;

            // Track unique items
            if (!isset($items[$itemId])) {
                $items[$itemId] = [
                    'id' => $itemId,
                    'name' => $result->item_name
                ];
            }
        }

        return response()->json([
            'monthlyData' => $monthlyData,
            'items' => array_values($items) // Convert associative array to indexed array
        ]);
    }

    public function getTodaysSalesByItem()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $query = TransactionSalesItem::query()
            ->join('transaction_sales_bills', 'transaction_sales_items.tsb_id', '=', 'transaction_sales_bills.id')
            ->join('warehouse_stocks', 'transaction_sales_items.stock_id', '=', 'warehouse_stocks.id')
            ->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id');

        // Filter by warehouse if user is not admin
        if ($user->role_id != 1) {
            $query->where('warehouse_stocks.warehouse_id', $user->warehouse_id);
        }

        $results = $query->select(
                'inventory_stocks.item_code',
                DB::raw('SUM(transaction_sales_items.item_qty) as total_quantity'),
                DB::raw('SUM(transaction_sales_items.item_qty * transaction_sales_items.item_price) as total_sales')
            )
            ->whereDate('transaction_sales_bills.date_sold', $today)
            ->groupBy('inventory_stocks.item_code')
            ->orderBy('total_sales', 'desc')
            ->get();

        return response()->json([
            'items' => $results->pluck('item_code'),
            'quantities' => $results->pluck('total_quantity'),
            'sales' => $results->pluck('total_sales')
        ]);
    }

    public function getWeeklySalesByItem()
    {
        $user = Auth::user();
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $endOfWeek = now()->endOfWeek()->format('Y-m-d');

        $query = TransactionSalesItem::query()
            ->join('transaction_sales_bills', 'transaction_sales_items.tsb_id', '=', 'transaction_sales_bills.id')
            ->join('warehouse_stocks', 'transaction_sales_items.stock_id', '=', 'warehouse_stocks.id')
            ->join('inventory_stocks', 'warehouse_stocks.inventory_id', '=', 'inventory_stocks.id');

        // Filter by warehouse if user is not admin
        if ($user->role_id != 1) {
            $query->where('warehouse_stocks.warehouse_id', $user->warehouse_id);
        }

        $results = $query->select(
                'inventory_stocks.item_code',
                DB::raw('SUM(transaction_sales_items.item_qty) as total_quantity'),
                DB::raw('SUM(transaction_sales_items.item_qty * transaction_sales_items.item_price) as total_sales')
            )
            ->whereBetween('transaction_sales_bills.date_sold', [$startOfWeek, $endOfWeek])
            ->groupBy('inventory_stocks.item_code')
            ->orderBy('total_sales', 'desc')
            ->get();

        return response()->json([
            'items' => $results->pluck('item_code'),
            'quantities' => $results->pluck('total_quantity'),
            'sales' => $results->pluck('total_sales')
        ]);
    }
}
