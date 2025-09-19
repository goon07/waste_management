<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\CollectionSchedule;
use App\Models\User;
use App\Notifications\CollectionScheduled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CollectionController extends Controller
{
    public function schedule(Request $request)
    {
        try {
            $request->validate([
                'schedule_for' => 'required|in:individual,area,all',
                'resident_id' => 'required_if:schedule_for,individual|uuid|exists:users,id',
                'area_id' => 'required_if:schedule_for,area|integer|exists:areas,id',
                'collector_id' => 'required|uuid|exists:users,id',
                'waste_type' => 'required|integer|exists:waste_types,id',
                'schedule_type' => 'required|in:specific_date,weekly,biweekly,monthly',
                'specific_date' => 'required_if:schedule_type,specific_date|date|after_or_equal:today',
                'weekly_day' => 'required_if:schedule_type,weekly|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'biweekly_day' => 'required_if:schedule_type,biweekly|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'monthly_day' => 'required_if:schedule_type,monthly|integer|min:1|max:31',
            ]);

            $companyId = Auth::user()->collector_company_id;
            $scheduledDate = $this->calculateScheduleDate($request);

            $residents = [];
            if ($request->schedule_for === 'individual') {
                $residents[] = User::where('id', $request->resident_id)
                    ->where('role', 'resident')
                    ->where('collector_company_id', $companyId)
                    ->firstOrFail();
            } elseif ($request->schedule_for === 'area') {
                $residents = User::where('role', 'resident')
                    ->whereHas('residency', fn($q) => $q->where('area_id', $request->area_id))
                    ->where('collector_company_id', $companyId)
                    ->get();
            } else {
                $residents = User::where('role', 'resident')
                    ->where('collector_company_id', $companyId)
                    ->get();
            }

            foreach ($residents as $resident) {
                $collection = Collection::create([
                    'user_id' => $resident->id,
                    'waste_type' => $request->waste_type,
                    'status' => 'scheduled',
                    'collector_company_id' => $companyId,
                    'council_id' => $resident->residency->council_id,
                    'scheduled_date' => $scheduledDate,
                ]);

                $schedule = CollectionSchedule::create([
                    'collection_id' => $collection->id,
                    'scheduled_date' => $scheduledDate,
                    'assigned_collector_id' => $request->collector_id,
                    'assigned_by_admin_id' => Auth::id(),
                    'status' => 'scheduled',
                    'schedule_type' => $request->schedule_type,
                    'weekly_day' => $request->weekly_day,
                    'biweekly_day' => $request->biweekly_day,
                    'monthly_day' => $request->monthly_day,
                ]);

                // Notify resident and collector
                if ($resident->notifications_enabled) {
                    $resident->notify(new CollectionScheduled($schedule));
                }
                $collector = User::find($request->collector_id);
                if ($collector && $collector->notifications_enabled) {
                    $collector->notify(new CollectionScheduled($schedule));
                }
            }

            return redirect()->route('management.dashboard')->with('success', 'Collection(s) scheduled successfully.');
        } catch (\Exception $e) {
            Log::error('Error scheduling collection', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to schedule collection. Please try again.');
        }
    }

    private function calculateScheduleDate(Request $request)
    {
        if ($request->schedule_type === 'specific_date') {
            return \Carbon\Carbon::parse($request->specific_date);
        } elseif ($request->schedule_type === 'weekly') {
            return \Carbon\Carbon::today()->next($request->weekly_day);
        } elseif ($request->schedule_type === 'biweekly') {
            return \Carbon\Carbon::today()->next($request->biweekly_day);
        } elseif ($request->schedule_type === 'monthly') {
            $day = min($request->monthly_day, \Carbon\Carbon::today()->endOfMonth()->day);
            return \Carbon\Carbon::today()->startOfMonth()->addDays($day - 1);
        }
    }

    public function create(Request $request)
    {
        $residentId = $request->query('resident_id');
        $resident = $residentId ? User::where('id', $residentId)
            ->where('role', 'resident')
            ->where('collector_company_id', Auth::user()->collector_company_id)
            ->select('id', 'name')
            ->firstOrFail() : null;

        $collectors = User::where('role', 'collector')
            ->where('collector_company_id', Auth::user()->collector_company_id)
            ->select('id', 'name')
            ->get();

        $wasteTypes = Cache::remember('waste_types', now()->addHours(24), function () {
            return WasteType::select('id', 'name')->get();
        });

        return view('management.collections.create', compact('resident', 'collectors', 'wasteTypes'));
    }
}