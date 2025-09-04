<?php

namespace App\Services;

use App\Models\User;
use App\Models\Issue;
use App\Models\Payment;
use App\Models\Collection;

class ReportService
{
    public function getReports($scope, $id = null)
    {
        switch ($scope) {
            case 'system':
                return $this->systemReports();
            case 'council':
                return $this->councilReports($id);
            case 'company':
                return $this->companyReports($id);
            default:
                return [];
        }
    }

    private function systemReports()
    {
        return [
            'residentStats' => [
                'total' => User::where('role', 'resident')->count(),
                'active' => User::where('role', 'resident')
                    ->whereHas('collections', fn($q) => $q->whereMonth('created_at', now()->month))
                    ->count(),
            ],
            'issueStats' => [
                'total' => Issue::count(),
                'resolved' => Issue::where('status', 'resolved')->count(),
            ],
            'paymentStats' => [
                'collected' => Payment::whereHas('user', fn($q) => $q->where('role', 'resident'))
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount'),
                'outstanding' => Payment::whereHas('user', fn($q) => $q->where('role', 'resident'))
                    ->where('status', 'pending')
                    ->count(),
            ],
            'pickupStats' => [
                'total' => Collection::count(),
                'completed' => Collection::where('status', 'completed')->count(),
            ],
        ];
    }

    private function councilReports($councilId)
    {
        return [
            'residentStats' => [
                'total' => User::where('council_id', $councilId)->count(),
                'active' => User::where('council_id', $councilId)
                    ->whereHas('collections', fn($q) => $q->whereMonth('created_at', now()->month))
                    ->count(),
            ],
            'issueStats' => [
                'total' => Issue::where('council_id', $councilId)->count(),
                'resolved' => Issue::where('council_id', $councilId)->where('status', 'resolved')->count(),
            ],
            'paymentStats' => [
                'collected' => Payment::whereHas('user', fn($q) => $q->where('council_id', $councilId))
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount'),
                'outstanding' => Payment::whereHas('user', fn($q) => $q->where('council_id', $councilId))
                    ->where('status', 'pending')
                    ->count(),
            ],
            'pickupStats' => [
                'total' => Collection::where('council_id', $councilId)->count(),
                'completed' => Collection::where('council_id', $councilId)->where('status', 'completed')->count(),
            ],
        ];
    }

    private function companyReports($companyId)
    {
        return [
            'residentStats' => [
                'total' => User::where('collector_company_id', $companyId)->count(),
                'active' => User::where('collector_company_id', $companyId)
                    ->whereHas('collections', fn($q) => $q->whereMonth('created_at', now()->month))
                    ->count(),
            ],
            'issueStats' => [
                'total' => Issue::where('collector_company_id', $companyId)->count(),
                'resolved' => Issue::where('collector_company_id', $companyId)->where('status', 'resolved')->count(),
            ],
            'paymentStats' => [
                'collected' => Payment::whereHas('user', fn($q) => $q->where('collector_company_id', $companyId))
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount'),
                'outstanding' => Payment::whereHas('user', fn($q) => $q->where('collector_company_id', $companyId))
                    ->where('status', 'pending')
                    ->count(),
            ],
            'pickupStats' => [
                'total' => Collection::where('collector_company_id', $companyId)->count(),
                'completed' => Collection::where('collector_company_id', $companyId)->where('status', 'completed')->count(),
            ],
        ];
    }
}
