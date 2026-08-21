<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\FinancialTransaction;
use App\Models\House;
use App\Models\Announcement;
use App\Models\MaintenanceBill;
use App\Models\MaintenanceCycle;
use App\Models\MaintenancePayment;
use App\Models\ParkingSlot;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SocietyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $society = Society::create([
            'name' => 'Shree Krishna Residency',
            'address' => 'Ahmedabad, Gujarat',
            'city' => 'Ahmedabad',
            'opening_balance' => 50000,
        ]);

        $blockA = Block::create(['society_id' => $society->id, 'name' => 'Block A', 'code' => 'A']);
        $blockB = Block::create(['society_id' => $society->id, 'name' => 'Block B', 'code' => 'B']);

        $admin = User::create([
            'name' => 'Society Admin',
            'email' => 'admin@society.local',
            'mobile' => '9876543210',
            'password' => Hash::make('password'),
            'society_id' => $society->id,
            'locale' => 'en',
            'status' => 'active',
        ]);
        $admin->assignRole('society_admin');

        $treasurer = User::create([
            'name' => 'Treasurer',
            'email' => 'treasurer@society.local',
            'mobile' => '9876543211',
            'password' => Hash::make('password'),
            'society_id' => $society->id,
            'locale' => 'gu',
            'status' => 'active',
        ]);
        $treasurer->assignRole('treasurer');

        for ($i = 1; $i <= 10; $i++) {
            $house = House::create([
                'society_id' => $society->id,
                'block_id' => $i <= 5 ? $blockA->id : $blockB->id,
                'house_number' => ($i <= 5 ? 'A' : 'B').'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'status' => $i <= 8 ? 'occupied' : 'vacant',
            ]);

            if ($i <= 8) {
                $member = User::create([
                    'name' => "Member {$i}",
                    'email' => "member{$i}@society.local",
                    'mobile' => '98000000'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'society_id' => $society->id,
                    'house_id' => $house->id,
                    'status' => 'active',
                ]);
                $member->assignRole('member');
                $house->update(['owner_user_id' => $member->id]);
            }
        }

        FinancialTransaction::create([
            'society_id' => $society->id,
            'type' => 'income',
            'category' => 'maintenance',
            'amount' => 25000,
            'transaction_date' => now(),
            'created_by' => $admin->id,
        ]);

        FinancialTransaction::create([
            'society_id' => $society->id,
            'type' => 'expense',
            'category' => 'security_salary',
            'amount' => 8000,
            'transaction_date' => now(),
            'created_by' => $treasurer->id,
        ]);

        $monthYear = now()->format('Y-m');
        $amount = 1500;

        MaintenanceCycle::create([
            'society_id' => $society->id,
            'month_year' => $monthYear,
            'amount' => $amount,
            'late_fee' => 100,
            'due_date' => now()->endOfMonth(),
            'bills_generated' => true,
            'created_by' => $admin->id,
        ]);

        $houses = House::where('society_id', $society->id)->get();
        foreach ($houses as $index => $house) {
            $bill = MaintenanceBill::create([
                'society_id' => $society->id,
                'house_id' => $house->id,
                'bill_number' => 'MB-'.str_replace('-', '', $monthYear).'-'.$house->house_number,
                'month_year' => $monthYear,
                'maintenance_amount' => $amount,
                'late_fee' => 0,
                'due_date' => now()->endOfMonth(),
                'status' => $index < 3 ? 'paid' : 'pending',
                'paid_amount' => $index < 3 ? $amount : 0,
            ]);

            $house->update([
                'outstanding_amount' => $bill->balanceDue(),
                'paid_amount' => $bill->paid_amount,
                'last_payment_date' => $index < 3 ? now() : null,
            ]);

            if ($index < 3) {
                MaintenancePayment::create([
                    'maintenance_bill_id' => $bill->id,
                    'house_id' => $house->id,
                    'amount' => $amount,
                    'payment_method' => $index === 0 ? 'cash' : 'online',
                    'receipt_number' => 'RCP-DEMO-'.$house->id,
                    'payment_date' => now(),
                    'received_by' => $treasurer->id,
                ]);
            }
        }

        foreach (['P-01', 'P-02', 'P-03', 'P-04'] as $num) {
            ParkingSlot::create([
                'society_id' => $society->id,
                'slot_number' => $num,
                'status' => 'available',
            ]);
        }

        Announcement::create([
            'society_id' => $society->id,
            'created_by' => $admin->id,
            'title' => 'Welcome to Society Manager Pro',
            'description' => 'Use this app for maintenance, complaints, visitors, and announcements.',
            'type' => 'text',
            'sent_at' => now(),
        ]);
    }
}
