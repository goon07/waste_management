<?php

namespace App\Http\Controllers;

use App\Models\CollectorResidentAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CollectorAssignmentController extends Controller
{
    public function assign(Request $request)
    {
        try {
            $request->validate([
                'assign_to' => 'required|in:individual,area,all',
                'resident_id' => 'required_if:assign_to,individual|uuid|exists:users,id',
                'area_id' => 'required_if:assign_to,area|integer|exists:areas,id',
                'collector_id' => 'required|uuid|exists:users,id',
            ]);

            $companyId = Auth::user()->collector_company_id;

            $residents = [];
            if ($request->assign_to === 'individual') {
                $residents[] = User::where('id', $request->resident_id)
                    ->where('role', 'resident')
                    ->where('collector_company_id', $companyId)
                    ->firstOrFail();
            } elseif ($request->assign_to === 'area') {
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
                CollectorResidentAssignment::updateOrCreate(
                    [
                        'resident_id' => $resident->id,
                        'collector_company_id' => $companyId,
                    ],
                    [
                        'collector_id' => $request->collector_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            return redirect()->route('management.dashboard')->with('success', 'Collector(s) assigned successfully.');
        } catch (\Exception $e) {
            Log::error('Error assigning collector', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Failed to assign collector. Please try again.');
        }
    }
}