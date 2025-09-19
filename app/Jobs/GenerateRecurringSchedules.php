<?php

namespace App\Jobs;

use App\Models\CollectionSchedule;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateRecurringSchedules implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        DB::beginTransaction();
        try {
            $schedules = CollectionSchedule::whereIn('schedule_type', ['weekly', 'biweekly', 'monthly'])
                ->where('status', 'scheduled')
                ->where('start_date', '<=', now()->addDays(28))
                ->where(function ($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->with('collection')
                ->get();

            foreach ($schedules as $schedule) {
                $start = Carbon::parse($schedule->start_date);
                $end = $schedule->end_date ? Carbon::parse($schedule->end_date) : now()->addDays(28);
                $period = CarbonPeriod::create(now(), '1 day', $end);

                foreach ($period as $date) {
                    $createSchedule = false;
                    if ($schedule->schedule_type === 'weekly' && $date->englishDayOfWeek === $schedule->recurring_day) {
                        $createSchedule = true;
                    } elseif ($schedule->schedule_type === 'biweekly' && $date->englishDayOfWeek === $schedule->recurring_day && $start->diffInWeeks($date) % 2 === 0) {
                        $createSchedule = true;
                    } elseif ($schedule->schedule_type === 'monthly' && $date->day === $schedule->monthly_day) {
                        $createSchedule = true;
                    }

                    if ($createSchedule && !CollectionSchedule::where('collection_id', $schedule->collection_id)
                        ->where('scheduled_date', $date->toDateString())
                        ->exists()) {
                        CollectionSchedule::create([
                            'collection_id' => $schedule->collection_id,
                            'scheduled_date' => $date->toDateString(),
                            'collector_id' => $schedule->collector_id,
                            'assigned_by_admin_id' => $schedule->assigned_by_admin_id,
                            'status' => 'scheduled',
                            'schedule_type' => 'one_time',
                            'notes' => $schedule->notes,
                        ]);
                    }
                }
            }

            DB::commit();
            Log::info('Recurring schedules generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating recurring schedules', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}