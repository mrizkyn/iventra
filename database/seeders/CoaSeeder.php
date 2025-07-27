<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class CoaSeeder extends Seeder
{
    public function run(): void
    {
        Account::firstOrCreate(
            ['account_code' => '1101'],
            ['account_name' => 'Kas & Bank', 'category' => 'Asset']
        );
        Account::firstOrCreate(
            ['account_code' => '1102'],
            ['account_name' => 'Piutang Usaha', 'category' => 'Asset']
        );
        Account::firstOrCreate(
            ['account_code' => '2101'],
            ['account_name' => 'Hutang Usaha', 'category' => 'Liability']
        );
         Account::firstOrCreate(
            ['account_code' => '4101'],
            ['account_name' => 'Pendapatan Penjualan', 'category' => 'Revenue']
        );
        Account::firstOrCreate(
            ['account_code' => '5101'],
            ['account_name' => 'Beban Pokok Penjualan (HPP)', 'category' => 'Expense']
        );
    }
}
