<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Jobs\TimeEntryReportJob;

class WeeklyReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmts:weekly-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a weekly report for management';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \Log::info("WeeklyReportCommand@handle: Initiated");

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        \Log::info("WeeklyReportCommand@handle: Dispatching Job with dates ", [$startDate, $endDate]);
        TimeEntryReportJob::dispatch($startDate, $endDate);


    }
}
