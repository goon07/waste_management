<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CouncilDummyDataSeeder extends Seeder
{
    public function run()
    {
        $councilId = 1;
        $collectorCompanyId = 1; // assuming exists
        $wasteTypeId = 1; // assuming exists

        // Chongwe, Lusaka approx lat/lng bounds
        $latMin = -15.5;
        $latMax = -15.3;
        $lngMin = 28.4;
        $lngMax = 28.6;

        // Create 20 dummy residents
        for ($i = 1; $i <= 20; $i++) {
            $userId = (string) Str::uuid();
            $paid = $i % 3 !== 0; // 2 out of 3 paid, 1 unpaid

            DB::table('users')->insert([
                'id' => $userId,
                'name' => "Resident $i",
                'email' => "resident{$i}@example.com",
                'password' => Hash::make('password123'),
                'role' => 'resident',
                'council_id' => $councilId,
                'payment_status' => $paid ? 'paid' : 'pending',
                'user_status' => 'active',
                'notifications_enabled' => true,
                'created_at' => now(),
                'updated_at' => now(),
                'phone_number' => '096' . rand(1000000, 9999999),
                'address' => "Address $i",
            ]);

            // Residency with random lat/lng
            DB::table('residency')->insert([
                'user_id' => $userId,
                'council_id' => $councilId,
                'collector_company_id' => $collectorCompanyId,
                'household_size' => rand(1, 6),
                'waste_collection_frequency' => ['weekly', 'biweekly', 'monthly'][array_rand(['weekly', 'biweekly', 'monthly'])],
                'billing_address' => "Billing Address $i",
                'longitude' => mt_rand($lngMin * 1000000, $lngMax * 1000000) / 1000000,
                'latitude' => mt_rand($latMin * 1000000, $latMax * 1000000) / 1000000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Add a payment record for paid users
            if ($paid) {
                DB::table('payments')->insert([
                    'amount' => 100.00,
                    'payment_date' => Carbon::now()->subDays(rand(1, 30)),
                    'status' => 'completed',
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                // Add a pending payment record
                DB::table('payments')->insert([
                    'amount' => 100.00,
                    'payment_date' => null,
                    'status' => 'pending',
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Add pickups for each resident
            $pickupStatus = ['pending', 'scheduled', 'completed'][array_rand(['pending', 'scheduled', 'completed'])];
            $scheduledDate = $pickupStatus === 'scheduled' ? Carbon::now()->addDays(rand(1, 10)) : null;
            $completedDate = $pickupStatus === 'completed' ? Carbon::now()->subDays(rand(1, 10)) : null;

            DB::table('collections')->insert([
                'user_id' => $userId,
                'waste_type' => $wasteTypeId,
                'status' => $pickupStatus,
                'collector_company_id' => $collectorCompanyId,
                'collector_id' => null, // can assign later
                'scheduled_date' => $scheduledDate,
                'confirmed_by_collector' => false,
                'confirmed_by_resident' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'council_id' => $councilId,
                'completed_date' => $completedDate,
            ]);
        }

        // Add some issues for council 1
        for ($j = 1; $j <= 10; $j++) {
            $userId = DB::table('users')->where('council_id', $councilId)->where('role', 'resident')->inRandomOrder()->value('id');
            DB::table('issues')->insert([
                'council_id' => $councilId,
                'issue_type' => 'Garbage Collection',
                'description' => "Issue description $j",
                'status' => ['reported', 'in_progress', 'resolved'][array_rand(['reported', 'in_progress', 'resolved'])],
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
                'collector_company_id' => $collectorCompanyId,
                'user_id' => $userId,
            ]);
        }
    }
}