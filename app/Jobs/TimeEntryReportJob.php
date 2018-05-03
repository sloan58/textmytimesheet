<?php

namespace App\Jobs;

use App\User;
use Carbon\Carbon;
use App\Models\TimeEntry;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TimeEntryReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * @var
     */
    private $startDate;
    /**
     * @var
     */
    private $endDate;

    /**
     * Create a new job instance.
     * @param Carbon $startDate
     * @param Carbon $endDate
     */
    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info("TimeEntryReportJob@handle: Initiated");

        \Log::info("TimeEntryReportJob@handle: Spreadsheet Created");
        $spreadsheet = new Spreadsheet();

        \Log::info("TimeEntryReportJob@handle: Set Top level values for hours overview");
        $spreadsheet->createSheet()->setTitle('Total Hours');
        $sheet = $spreadsheet->getSheetByName('Total Hours');
        $sheet->fromArray(['Name', 'Hours']);

        \Log::info("TimeEntryReportJob@handle: Set start and end dates", [
            $this->startDate->toDateString(), $this->endDate->toDateString()
        ]);

        $users = User::whereHas('timeEntries', function ($query) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate),
                Carbon::parse($this->endDate)
            ]);
        })->get();
        \Log::info("TimeEntryReportJob@handle: Collected users with TimeEntries", [$users->count()]);

        $outPut = [];
        foreach ($users as $user) {
            $outPut[] = [$user->name, $user->timeEntries()->whereBetween('created_at', [
                Carbon::parse($this->startDate),
                Carbon::parse($this->endDate)
            ])->get()->sum('hours')];
        }

        \Log::info("TimeEntryReportJob@handle: Inserting Data");
        for ($i=0; $i<count($outPut); $i++) {
            if ($outPut[$i][1] != NULL) {
                $sheet->fromArray([$outPut[$i][0], $outPut[$i][1]], NULL, "A" . ($i + 2));
            }
        }

        \Log::info("TimeEntryReportJob@handle: Set Top level values for time entry details");
        $spreadsheet->createSheet()->setTitle('Time Entry Details');
        $sheet = $spreadsheet->getSheetByName('Time Entry Details');
        $sheet->fromArray(['User', 'Hours', 'Project', 'Date']);

        $timeEntries = TimeEntry::whereBetween('created_at', [
            Carbon::parse($this->startDate),
            Carbon::parse($this->endDate)
        ])->orderBy('created_at', 'desc')->get();
        \Log::info("TimeEntryReportJob@handle: Collected all TimeEntries between for this date range", [
            $timeEntries->count()
        ]);

        \Log::info("TimeEntryReportJob@handle: Writing time entry details");
        foreach ($timeEntries as $index => $timeEntry) {
            $sheet->fromArray([
                $timeEntry->user->name,
                $timeEntry->hours,
                $timeEntry->project,
                $timeEntry->created_at->toDateString()
            ], NULL, "A" . ($index + 2));
        }

        \Log::info("TimeEntryReportJob@handle: Removed first tab");
        $spreadsheet->removeSheetByIndex(0);

        \Log::info("TimeEntryReportJob@handle: Saving Spreadsheet");
        $writer = new Xlsx($spreadsheet);
        $fileName = storage_path('app/reports/') . Carbon::now()->toDateString() . '.xlsx';
        $writer->save($fileName);

        \Log::info("TimeEntryReportJob@handle: Sending Mail");
        Mail::raw("TextMyTimeSheet Report Attached",
            function($message) use ($fileName){
                $message->to('martin.sloan@karma-tek.com');
                $message->bcc('martin.sloan@karma-tek.com');
                $message->subject(
                    sprintf("TextMyTimeSheet Report (%s to %s)",
                        $this->startDate->toDateString(),
                        $this->endDate->toDateString()
                    )
                );
                $message->attach($fileName);
            });
    }
}
