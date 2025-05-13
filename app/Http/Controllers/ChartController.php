<?php
namespace App\Http\Controllers;
use App\Models\TransactionSalesBill;
use App\Models\TransactionSalesItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function getMonthlyIncome($year)
    {
        $monthlyData = array_fill(0, 12, 0);

        $connection = DB::connection()->getDriverName();

        if ($connection === 'pgsql') {
            $results = TransactionSalesBill::select(
                DB::raw('EXTRACT(MONTH FROM date_sold) as month'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereRaw('EXTRACT(YEAR FROM date_sold) = ?', [$year])
            ->groupBy(DB::raw('EXTRACT(MONTH FROM date_sold)'))
            ->get();
        } else {
            $results = TransactionSalesBill::select(
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
        $connection = DB::connection()->getDriverName();
        if ($connection === 'pgsql') {
            $years = TransactionSalesBill::select(
                DB::raw('DISTINCT EXTRACT(YEAR FROM date_sold) as year')
            )
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
        } else {
            $years = TransactionSalesBill::select(
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
        $connection = DB::connection()->getDriverName();

        if ($connection === 'pgsql') {
            $results = TransactionSalesItem::select(
                DB::raw('EXTRACT(MONTH FROM transaction_sales_bills.date_sold) as month'),
                'inventory_stocks.name as item_name',
                'inventory_stocks.id as item_id',
                DB::raw('SUM(transaction_sales_items.item_qty) as total')
            )
            ->join('transaction_sales_bills', 'transaction_sales_items.tsb_id', '=', 'transaction_sales_bills.id')
            ->join('inventory_stocks', 'transaction_sales_items.stock_id', '=', 'inventory_stocks.id')
            ->whereRaw('EXTRACT(YEAR FROM transaction_sales_bills.date_sold) = ?', [$year])
            ->groupBy(DB::raw('EXTRACT(MONTH FROM transaction_sales_bills.date_sold)'), 'inventory_stocks.id')
            ->get();
        } else {
            $results = TransactionSalesItem::select(
                DB::raw('MONTH(transaction_sales_bills.date_sold) as month'),
                'inventory_stocks.name as item_name',
                'inventory_stocks.id as item_id',
                DB::raw('SUM(transaction_sales_items.item_qty) as total')
            )
            ->join('transaction_sales_bills', 'transaction_sales_items.tsb_id', '=', 'transaction_sales_bills.id')
            ->join('inventory_stocks', 'transaction_sales_items.stock_id', '=', 'inventory_stocks.id')
            ->whereYear('transaction_sales_bills.date_sold', $year)
            ->groupBy(DB::raw('MONTH(transaction_sales_bills.date_sold)'), 'inventory_stocks.id')
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
        $today = now()->format('Y-m-d');

        $results = TransactionSalesItem::select(
                'inventory_stocks.item_code',
                DB::raw('SUM(transaction_sales_items.item_qty) as total_quantity'),
                DB::raw('SUM(transaction_sales_items.item_qty * transaction_sales_items.item_price) as total_sales')
            )
            ->join('transaction_sales_bills', 'transaction_sales_items.tsb_id', '=', 'transaction_sales_bills.id')
            ->join('inventory_stocks', 'transaction_sales_items.stock_id', '=', 'inventory_stocks.id')
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
}
