<?php

namespace App\Repositories;

use App\Models\Account;

class AccountRepository
{
    private $model;

    public function __construct(Account $model)
    {
        $this->model = $model;
    }

    public function getAccount(int $accountId): Account
    {
        return $this->model->find($accountId);
    }

    public function hasAccount(int $accountId): bool
    {
        return $this->model->find($accountId) == true;
    }

    public function increaseBalance(int $accountId, float $value): void
    {
        $account = $this->getAccount($accountId);
        $account->balance += $value;
        $account->save();
    }

    public function decreaseBalance(int $accountId, float $value): void
    {
        $account = $this->getAccount($accountId);
        $account->balance -= $value;
        $account->save();
    }
}
