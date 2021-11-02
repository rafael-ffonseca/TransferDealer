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

    public function getAccount(int $id): Account
    {
        return $this->model->find($id);
    }

    public function hasAccount(int $id): bool
    {
        return $this->model->find($id) == true;
    }

    public function increaseBalance(int $id, float $value): void
    {
        $account = $this->getAccount($id);
        $account->balance += $value;
        $account->save();
    }

    public function decreaseBalance(int $id, float $value): void
    {
        $account = $this->getAccount($id);
        $account->balance -= $value;
        $account->save();
    }
}
