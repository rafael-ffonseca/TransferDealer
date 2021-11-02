<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    private $model;

    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    public function saveTransaction(int $debitAccountId, int $creditAccountId, float $value): int
    {
        $this->model->payer = $debitAccountId;
        $this->model->payee = $creditAccountId;
        $this->model->value = $value;
        $this->model->save();
        return $this->model->id;
    }
}
