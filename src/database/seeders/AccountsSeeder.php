<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Account::create([
            'name' => 'Fulano da Silva',
            'cpfcnpj' => '012.345.678-90',
            'mail' => 'fulano@silva.com.br',
            'password' => '123456',
            'type' => 'user',
            'balance' => '100'
        ]);
    }
}
