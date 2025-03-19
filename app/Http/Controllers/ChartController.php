<?php

namespace App\Http\Controllers;

use App\Models\TransactionSalesBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    /**
     * Get monthly income data for a specific year
     *
     * @param int $year
     * @return \Illuminate\Http\Response
     */
    public function getMonthlyIncome($year)
    {
        // Initialize an array with zeros for all 12 months
        $monthlyData = array_fill(0, 12, 0);

        // Query to get monthly totals for the selected year
        $results = TransactionSalesBill::select(
            DB::raw('MONTH(date_sold) as month'),
            DB::raw('SUM(total_price) as total')
        )
        ->whereYear('date_sold', $year)
        ->groupBy(DB::raw('MONTH(date_sold)'))
        ->get();

        // Fill the data array with the actual values
        foreach ($results as $result) {
            // Months are 1-indexed in SQL, but we need 0-indexed for the array
            $monthIndex = $result->month - 1;
            $monthlyData[$monthIndex] = (float) $result->total;
        }

        return response()->json($monthlyData);
    }

    /**
     * Get available years from the transaction data
     *
     * @return \Illuminate\Http\Response
     */
    public function getAvailableYears()
    {
        $years = TransactionSalesBill::select(
            DB::raw('DISTINCT YEAR(date_sold) as year')
        )
        ->orderBy('year')
        ->pluck('year')
        ->toArray();

        return response()->json($years);
    }
}
