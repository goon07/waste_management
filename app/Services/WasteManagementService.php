<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Council;
use App\Models\CollectorCompany;
use App\Models\User;
use App\Models\Collection;
use App\Models\CouncilRequest;
use App\Models\Issue;
use App\Models\Payment;
use App\Models\Residency;
use App\Models\WasteGuide;
use App\Models\WasteType;
use App\Notifications\WasteManagementNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Events\CouncilUpdated;
use App\Events\CompanyUpdated;
use App\Events\UserUpdated;
use App\Events\CollectionUpdated;
use App\Events\CouncilRequestUpdated;
use App\Events\IssueUpdated;
use App\Events\PaymentUpdated;
use App\Models\UserBill;
use App\Models\BillPayment; // if using BillPayment too



class WasteManagementService
{


     public function getAllReports()
    {
        return Collection::whereHas('schedules', function ($query) {
                $query->where('status', 'completed');
            })
            ->with(['resident', 'wasteType', 'schedules'])
            ->get()
            ->map(function ($pickup) {
                $schedule = $pickup->schedules->first();
                return [
                    'pickup_id' => $pickup->id,
                    'resident_name' => $pickup->resident->name ?? 'Unknown',
                    'waste_type' => $pickup->wasteType->name ?? 'Unknown',
                    'status' => $schedule ? $schedule->status : 'Unknown',
                    'rating' => $pickup->rating,
                    'feedback' => $pickup->feedback,
                    'confirmed_by_collector' => $pickup->confirmed_by_collector,
                    'confirmed_by_resident' => $pickup->confirmed_by_resident,
                    'scheduled_date' => $schedule ? $schedule->scheduled_date : null,
                ];
            });
    }

    public function getAssignedPickups($collectorId = null)
    {
        $query = Collection::with(['resident', 'wasteType', 'schedules']);
        if ($collectorId) {
            $query->whereHas('schedules', function ($q) use ($collectorId) {
                $q->where('assigned_collector_id', $collectorId);
            });
        }
        return $query->whereHas('schedules', function ($q) {
            $q->whereIn('status', ['pending', 'scheduled']);
        })->get();
    }

    public function confirmCollectorPickup($pickupId, $collectorId)
    {
        $pickup = Collection::with('schedules')->findOrFail($pickupId);
        $schedule = $pickup->schedules()->where('assigned_collector_id', $collectorId)->first();
        if (!$schedule) {
            throw new \Exception('Unauthorized');
        }
        $pickup->update(['confirmed_by_collector' => true]);
        broadcast(new CollectionUpdated());
    }

    public function getReports($collectorCompanyId = null)
    {
        $query = Collection::whereHas('schedules', function ($query) {
                $query->where('status', 'completed');
            })
            ->with(['resident', 'wasteType', 'schedules']);
        if ($collectorCompanyId) {
            $query->whereHas('resident', function ($q) use ($collectorCompanyId) {
                $q->where('collector_company_id', $collectorCompanyId);
            });
        }
        return $query->get()->map(function ($pickup) {
            $schedule = $pickup->schedules->first();
            return [
                'pickup_id' => $pickup->id,
                'resident_name' => $pickup->resident->name ?? 'Unknown',
                'waste_type' => $pickup->wasteType->name ?? 'Unknown',
                'status' => $schedule ? $schedule->status : 'Unknown',
                'rating' => $pickup->rating,
                'feedback' => $pickup->feedback,
                'confirmed_by_collector' => $pickup->confirmed_by_collector,
                'confirmed_by_resident' => $pickup->confirmed_by_resident,
            ];
        });
    }

    public function getPickups($councilId)
    {
        return Collection::whereHas('resident', function ($query) use ($councilId) {
            $query->where('council_id', $councilId);
        })
            ->with(['resident', 'wasteType', 'schedules'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getScheduledPickups($councilId)
    {
        return Collection::whereHas('resident', function ($query) use ($councilId) {
                $query->where('council_id', $councilId);
            })
            ->whereHas('schedules', function ($query) {
                $query->where('status', 'scheduled');
            })
            ->with(['resident', 'wasteType', 'schedules'])
            ->orderBy('collection_schedules.scheduled_date', 'asc')
            ->join('collection_schedules', 'collections.id', '=', 'collection_schedules.collection_id')
            ->get();
    }

    public function getCompletedPickups($councilId)
    {
        return Collection::whereHas('resident', function ($query) use ($councilId) {
                $query->where('council_id', $councilId);
            })
            ->whereHas('schedules', function ($query) {
                $query->where('status', 'completed');
            })
            ->with(['resident', 'wasteType', 'schedules'])
            ->orderByDesc('collection_schedules.end_date')
            ->join('collection_schedules', 'collections.id', '=', 'collection_schedules.collection_id')
            ->get();
    }



  


  

  



public function updateProfile($userId, array $data)
{
    $user = \App\Models\User::findOrFail($userId);
    $user->update($data);
    return $user;
}

public function getCouncils()
{
    return \App\Models\Council::all();
}

public function getUserRequests($userId)
{
    return \App\Models\CouncilRequest::where('council_id', $userId)
        ->orderBy('requested_at', 'desc')
        ->get();
}

public function requestCouncil($userId, $councilId, $notes)
{
    return \App\Models\CouncilRequest::create([
        'user_id' => $userId,
        'council_id' => $councilId,
        'status' => 'pending',
        'requested_at' => now(),
        'notes' => $notes,
    ]);
}

public function logAction($action, $description, $userId, $entityType, $entityId)
{
    \App\Models\AuditLog::create([
        'action' => $action,
        'description' => $description,
        'user_id' => $userId,
        'entity_type' => $entityType,
        'entity_id' => $entityId,
    ]);
}


   

    public function getCouncil($id)
    {
        return Council::findOrFail($id);
    }

    public function createCouncil(array $data)
    {
        $council = Council::create($data);
        broadcast(new CouncilUpdated());
        return $council;
    }

    public function updateCouncil($id, array $data)
    {
        $council = Council::findOrFail($id);
        $council->update($data);
        broadcast(new CouncilUpdated());
        return $council;
    }

    public function getCollectorCompanies()
    {
        return CollectorCompany::all();
    }

    public function getCollectorCompany($id)
    {
        return CollectorCompany::findOrFail($id);
    }

    public function createCollectionCompany(array $data)
    {
        $company = CollectorCompany::create($data);
        broadcast(new CompanyUpdated());
        return $company;
    }

    public function updateCollectionCompany($id, array $data)
    {
        $company = CollectorCompany::findOrFail($id);
        $company->update($data);
        broadcast(new CompanyUpdated());
        return $company;
    }

    public function getAllUsers()
    {
        return User::all();
    }

    public function getUser($id)
    {
        return User::findOrFail($id);
    }

    public function getResidents($collectorCompanyId = null)
    {
        $query = User::where('role', 'resident');
        if ($collectorCompanyId) {
            $query->where('collector_company_id', $collectorCompanyId);
        }
        return $query->get();
    }

    public function getCouncilAdmins()
    {
        return User::where('role', 'council_admin')->get();
    }

    public function getCompanyAdmins()
    {
        return User::where('role', 'company_admin')->get();
    }

    public function createUser(array $data)
    {
        $user = User::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'email' => $data['email'],
            'name' => $data['name'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'address' => $data['address'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'council_id' => $data['council_id'] ?? null,
            'collector_company_id' => $data['collector_company_id'] ?? null,
            'notifications_enabled' => $data['notifications_enabled'] ?? true,
        ]);
        broadcast(new UserUpdated());
        return $user;
    }

    public function updateUser($id, array $data)
    {
        $user = User::findOrFail($id);
        $updateData = [
            'email' => $data['email'],
            'name' => $data['name'],
            'role' => $data['role'],
            'address' => $data['address'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'council_id' => $data['council_id'] ?? null,
            'collector_company_id' => $data['collector_company_id'] ?? null,
            'notifications_enabled' => $data['notifications_enabled'] ?? true,
        ];
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }
        $user->update($updateData);
        broadcast(new UserUpdated());
        return $user;
    }

    public function getResidencies()
    {
        return Residency::with(['user', 'council', 'collectorCompany'])->get();
    }

    public function getResidency($id)
    {
        return Residency::findOrFail($id);
    }

    public function createResidency(array $data)
    {
        // Validate council-collector company relationship
        $relationshipExists = DB::table('council_collector_companies')
            ->where('council_id', $data['council_id'])
            ->where('collector_company_id', $data['collector_company_id'])
            ->exists();

        if (!$relationshipExists) {
            throw new \Exception('Invalid council-collector company combination.');
        }

        // Ensure user is a resident
        $user = User::findOrFail($data['user_id']);
        if ($user->role !== 'resident') {
            throw new \Exception('User must have the resident role.');
        }

        $residency = Residency::create($data);
        return $residency;
    }

    public function updateResidency($id, array $data)
    {
        // Validate council-collector company relationship
        $relationshipExists = DB::table('council_collector_companies')
            ->where('council_id', $data['council_id'])
            ->where('collector_company_id', $data['collector_company_id'])
            ->exists();

        if (!$relationshipExists) {
            throw new \Exception('Invalid council-collector company combination.');
        }

        // Ensure user is a resident
        $user = User::findOrFail($data['user_id']);
        if ($user->role !== 'resident') {
            throw new \Exception('User must have the resident role.');
        }

        $residency = Residency::findOrFail($id);
        $residency->update($data);
        return $residency;
    }

    public function assignCollectorCompany($councilId, $collectorCompanyId)
    {
        DB::table('council_collector_companies')->insert([
            'council_id' => $councilId,
            'collector_company_id' => $collectorCompanyId,
            'created_at' => now(),
        ]);
    }

    public function resetUserPassword($email)
    {
        // Implement password reset logic using Laravel's built-in password reset
        \Illuminate\Support\Facades\Password::sendResetLink(['email' => $email]);
    }

   

 

  

    public function reportIssue($userId, $councilId, $issueType, $description)
    {
        $issue = Issue::create([
            'user_id' => $userId,
            'council_id' => $councilId,
            'issue_type' => $issueType,
            'description' => $description,
            'status' => 'reported',
        ]);
        broadcast(new IssueUpdated());
        return $issue;
    }

    public function getWasteGuide()
    {
        return WasteGuide::all();
    }

    public function getCollectors($collectorCompanyId = null)
    {
        $query = User::where('role', 'collector');
        if ($collectorCompanyId) {
            $query->where('collector_company_id', $collectorCompanyId);
        }
        return $query->get();
    }

  

    public function sendNotification($userId, $title, $body)
    {
        $user = User::findOrFail($userId);
        $user->notify(new WasteManagementNotification($title, $body));
        return \App\Models\Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
        ]);
    }

   

    public function approveCouncilRequest($requestId)
    {
        $request = CouncilRequest::findOrFail($requestId);
        $request->update(['status' => 'approved']);
        User::where('id', $request->user_id)->update([
            'council_id' => $request->council_id,
            'collector_company_id' => $request->collector_company_id,
        ]);
        broadcast(new CouncilRequestUpdated());
    }

    public function rejectCouncilRequest($requestId)
    {
        $request = CouncilRequest::findOrFail($requestId);
        $request->update(['status' => 'rejected']);
        broadcast(new CouncilRequestUpdated());
    }


    public function getIssues($councilId)
    {
        return Issue::where('council_id', $councilId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCouncilBills($councilId)
{
    return UserBill::whereHas('user', function ($query) use ($councilId) {
        $query->where('council_id', $councilId);
    })
    ->with(['user', 'payments'])
    ->orderBy('year', 'desc')
    ->orderBy('month', 'desc')
    ->get();
}

    public function getPayments($councilId)
    {
        return Payment::whereHas('user', function ($query) use ($councilId) {
            $query->where('council_id', $councilId);
        })
            ->with('user')
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function updateIssueStatus($issueId, $status)
    {
        $issue = Issue::findOrFail($issueId);
        $issue->update(['status' => $status, 'updated_at' => now()]);
        broadcast(new IssueUpdated());
        return $issue;
    }

    public function updatePaymentStatus($paymentId, $status)
    {
        $payment = Payment::findOrFail($paymentId);
        $payment->update(['status' => $status, 'updated_at' => now()]);
        broadcast(new PaymentUpdated());
        return $payment;
    }
}