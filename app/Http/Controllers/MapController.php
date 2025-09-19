<?php

namespace App\Http\Controllers;

use App\Models\Residency;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller; // Add this import

class MapController extends Controller
{
  public function index()
{
    $user = auth()->user();

    // Get residencies based on role
    $residencies = match ($user->role) {
        'super_admin'   => Residency::query(),
        'council_admin' => Residency::where('council_id', $user->council_id),
        'company_admin' => Residency::where('company_id', $user->company_id),
        'collector'     => Residency::whereIn('id', $user->assignedResidencies()->pluck('id')),
        default         => Residency::whereNull('id'), // return empty
    };

    // Eager load user + relations
  


      $residencies = $residencies
    ->with([
        'user:id,name',
        'user.payments' => fn($q) => $q->latest()->limit(1),
        'user.collections:id,resident_id',
        'user.collections.schedules:id,collection_id,status',
        'user.issues:id,user_id,status',
    ])
    ->get();



$mapData = $residencies->map(function ($res) {
    $user = $res->user;
    if (!$user || !$res->latitude || !$res->longitude) return null;

    $latestPayment = $user->payments->first();
    $paymentStatus = $latestPayment?->status ?? 'unpaid';

    $pickupStatus = $user->collections->where('status', 'pending')->isNotEmpty()
        ? 'pending'
        : 'completed';

    $issueStatus = $user->issues->where('status', 'open')->isNotEmpty()
        ? 'open'
        : 'none';

    return [
        'id'             => $res->id,
        'name'           => $user->name,
        'lat'            => (float) $res->latitude,
        'lng'            => (float) $res->longitude,
        'payment_status' => $paymentStatus,
        'pickup_status'  => $pickupStatus,
        'issue_status'   => $issueStatus,
    ];
})->filter()->values();

//dd($mapData->toArray());

    return view('map', ['mapData' => $mapData]);
}

}
