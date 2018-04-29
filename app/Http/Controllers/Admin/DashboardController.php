<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $time = [];
        for($i=7; $i>=0; $i--) {
            $time[Carbon::now()->subDays($i)->format('m-d-Y')] = \App\Models\TimeEntry::whereBetween('created_at', [
                Carbon::now()->subDays($i)->startOfDay(),
                Carbon::now()->subDays($i)->endOfDay()
            ])->count();
        }

        $labels = array_keys($time);
        $data = array_values($time);

        $chartjs = app()->chartjs
            ->name('lineChartTest')
            ->type('line')
            ->size(['width' => 400, 'height' => 200])
            ->labels($labels)
            ->datasets([
                [
                    "label" => "Hours Posted This Week",
                    'backgroundColor' => "rgba(38, 185, 154, 0.31)",
                    'borderColor' => "rgba(38, 185, 154, 0.7)",
                    "pointBorderColor" => "rgba(38, 185, 154, 0.7)",
                    "pointBackgroundColor" => "rgba(38, 185, 154, 0.7)",
                    "pointHoverBackgroundColor" => "#fff",
                    "pointHoverBorderColor" => "rgba(220,220,220,1)",
                    'data' => $data,
                ]
            ])
            ->options([]);

        return view('dashboard', compact('chartjs'));
    }
}
