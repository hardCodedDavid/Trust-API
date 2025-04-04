<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SavingsAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Savings Balance', 'slug' => 'savings-balance', 'desc' => 'A savings account to grow your balance', 'rate' => 0.05],
            ['name' => 'Traditional IRA', 'slug' => 'traditional-ira', 'desc' => 'Tax-deferred retirement savings', 'rate' => 0.07],
            ['name' => 'HSA', 'slug' => 'hsa', 'desc' => 'Health Savings Account', 'rate' => 0.03],
            ['name' => 'SEP IRA', 'slug' => 'sep-ira', 'desc' => 'Simplified Employee Pension Plan', 'rate' => 0.06],
            ['name' => 'Custodian Account', 'slug' => 'custodian-account', 'desc' => 'Account managed by a custodian for minors', 'rate' => 0.04],
            ['name' => 'ROTH IRA', 'slug' => 'roth-ira', 'desc' => 'Tax-free retirement savings', 'rate' => 0.08],
            ['name' => 'Cash Management Account', 'slug' => 'cash-management-account', 'desc' => 'Flexible account for managing cash', 'rate' => 0.02],
        ];
        
        // Fetch all country IDs
        $allCountryIds = Country::pluck('id')->toArray();
        
        foreach ($accounts as $account) {
            // "Savings Balance" should have all country IDs
            if ($account['slug'] === 'savings-balance' & $account['slug'] === 'sep-ira') {
                $countryIds = $allCountryIds;
            } else {
                // Other accounts should have random country IDs
                $countryIds = Country::inRandomOrder()->limit(rand(1, 3))->pluck('id')->toArray();
            }
        
            DB::table('savings_accounts')->insert([
                'id' => Str::uuid(),
                'name' => $account['name'],
                'slug' => $account['slug'],
                'title' => $account['desc'],
                'rate' => $account['rate'],
                'note' => 'Lorem ipsum dolor sit, amet consectetur adipisicing elit. Reprehenderit repellat vitae tenetur quasi quos neque non voluptate nam? Fugit reiciendis ipsa optio aliquid nisi accusamus officiis minus amet eaque aspernatur.',
                'status' => 'active',
                'country_id' => json_encode($countryIds), // Store as JSON
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
    }
}
