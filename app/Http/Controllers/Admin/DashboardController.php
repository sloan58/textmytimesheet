<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use App\Jobs\TimeEntryReportJob;
use Colors\RandomColor;

class DashboardController extends Controller
{
    /**
     *  Generate TMTS Dashboard
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        // Top Contributors Chart
        list($labels, $data) = $this->getTopContributors();
        $topContributors = app()->chartjs
            ->name('topContributorsChart')
            ->type('pie')
            ->size(['width' => 400, 'height' => 200])
            ->labels($labels)
            ->datasets([
                [
                    'backgroundColor' => RandomColor::many(count($labels)),
                    'hoverBackgroundColor' => ['#FF6384', '#36A2EB'],
                    'data' => $data
                ]
            ])
            ->options([]);

        // Weekly Hours Chart
        list($labels, $data) = $this->getHoursThisWeek();
        $hoursThisWeek = app()->chartjs
            ->name('hoursThisWeekChart')
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

        // Bud Light Chart
        $budLightsThisWeek = app()->chartjs
            ->name('budLightChart')
            ->type('line')
            ->size(['width' => 400, 'height' => 200])
            ->labels(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday',' Saturday'])
            ->datasets([
                [
                    "label" => "Bud Light's Slammed This Week",
                    'backgroundColor' => "rgba(54, 162, 235, 0.2)",
                    'borderColor' => "rgba(255,99,132,1)",
                    "pointBorderColor" => "rgba(255,99,132,1)",
                    "pointBackgroundColor" => "rgba(255,99,132,1)",
                    "pointHoverBackgroundColor" => "#fff",
                    "pointHoverBorderColor" => "rgba(220,220,220,1)",
                    'data' => [12, 6, 4, 6, 10, 12, 20],
                ]
            ])
            ->optionsRaw("{
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true
                            }
                        }]
                    }
                }");

        return view('dashboard', compact('hoursThisWeek', 'topContributors', 'budLightsThisWeek'));
    }

    /**
     *  Generate custom Time Entries report
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function report()
    {
        \Log::info("DashboardController@report: Received request for a report");

        $startDate = Carbon::parse(request()->input('start_date'));
        $endDate = Carbon::parse(request()->input('end_date'));
        \Log::info("DashboardController@report: Set start and end dates for report", [$startDate, $endDate]);

        \Log::info("DashboardController@report: Dispatching TimeEntryReportJob");
        TimeEntryReportJob::dispatch($startDate, $endDate, auth()->user());

        \Alert::success('Report Submitted!  Check your email.')->flash();

        return redirect()->back();
    }

    /**
     *  Calculate Time Entries submitted this week
     *
     * @return array
     */
    private function getHoursThisWeek()
    {
        $time = [];
        for ($i = 7; $i >= 0; $i--) {
            $time[Carbon::now()->subDays($i)->format('m-d-Y')] = \App\Models\TimeEntry::whereBetween('created_at', [
                Carbon::now()->subDays($i)->startOfDay(),
                Carbon::now()->subDays($i)->endOfDay()
            ])->count();
        }

        $labels = array_keys($time);
        $data = array_values($time);
        return array($labels, $data);
    }

    /**
     *  Get the top Users by hours submitted
     *
     * @return array
     */
    private function getTopContributors()
    {
        $output = [];
        $users = User::whereHas('timeEntries', function ($query) {
            $query->whereBetween('created_at', [
                Carbon::now()->subDays(6)->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        })->take(10)->get();

        foreach ($users as $user) {
            $output[$user->name] = $user->timeEntries()->whereBetween('created_at', [
                Carbon::now()->subDays(6)->startOfDay(),
                Carbon::now()->endOfDay()
            ])->get()->sum('hours');
        }

        $labels = array_keys($output);
        $data = array_values($output);
        return array($labels, $data);
    }
}
