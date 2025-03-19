<?php
namespace App\Http\Controllers;
use App\Models\TransactionSalesBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function getMonthlyIncome($year)
    {
        $monthlyData = array_fill(0, 12, 0);

        // Get the database connection type
        $connection = DB::connection()->getDriverName();

        // Different SQL based on the database type
        if ($connection === 'pgsql') {
            $results = TransactionSalesBill::select(
                DB::raw('EXTRACT(MONTH FROM date_sold) as month'),
                DB::raw('SUM(total_price) as total')
            )
            ->whereRaw('EXTRACT(YEAR FROM date_sold) = ?', [$year])
            ->groupBy(DB::raw('EXTRACT(MONTH FROM date_sold)'))
            ->get();
        } else {
            // MySQL and other databases
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
        // Get the database connection type
        $connection = DB::connection()->getDriverName();

        // Different SQL based on the database type
        if ($connection === 'pgsql') {
            $years = TransactionSalesBill::select(
                DB::raw('DISTINCT EXTRACT(YEAR FROM date_sold) as year')
            )
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
        } else {
            // MySQL and other databases
            $years = TransactionSalesBill::select(
                DB::raw('DISTINCT YEAR(date_sold) as year')
            )
            ->orderBy('year')
            ->pluck('year')
            ->toArray();
        }

        return response()->json($years);
    }
}
