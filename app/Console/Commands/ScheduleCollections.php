<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CollectorResidentAssignment;
use App\Models\Collection;
use Carbon\Carbon;

class ScheduleCollections extends Command
{
    protected $signature = 'collections:schedule';

    protected $description = 'Automatically schedule collections based on assignments';

public function handle()
{
    $today = Carbon::today();

    $assignments = CollectorResidentAssignment::with('collectorCompany')->get();

    foreach ($assignments as $assignment) {
        $company = $assignment->collectorCompany;

        // Default to weekly if not set
        $frequency = $company->collection_frequency ?? 'weekly';

        // Calculate next scheduled date based on frequency
        $nextScheduledDate = $this->calculateNextScheduledDate($today, $frequency);

        // Check if collection already scheduled
        $exists = Collection::where('user_id', $assignment->resident_id)
            ->where('collector_id', $assignment->collector_id)
            ->whereDate('scheduled_date', $nextScheduledDate)
            ->exists();

        if (!$exists) {
            Collection::create([
                'user_id' => $assignment->resident_id,
                'collector_id' => $assignment->collector_id,
                'collector_company_id' => $assignment->collector_company_id,
                'status' => 'pending',
                'scheduled_date' => $nextScheduledDate,
                // Add other required fields as needed
            ]);

            $this->info("Scheduled collection for resident {$assignment->resident_id} on {$nextScheduledDate->toDateString()}");
        }
    }

    $this->info('Collection scheduling completed.');
}

/**
 * Calculate next scheduled date based on frequency.
 */
protected function calculateNextScheduledDate(Carbon $fromDate, string $frequency): Carbon
{
    switch ($frequency) {
        case 'biweekly':
            // Next Monday every 2 weeks
            $nextDate = $fromDate->copy()->next(Carbon::MONDAY);
            // Check if last scheduled was less than 14 days ago, if so add 1 week more
            // (You can enhance this logic based on your needs)
            return $nextDate;

        case 'monthly':
            // Next first day of the month
            return $fromDate->copy()->addMonthNoOverflow()->startOfMonth();

        case 'weekly':
        default:
            // Next Monday (weekly)
            return $fromDate->copy()->next(Carbon::MONDAY);
    }
}

}
