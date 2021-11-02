<?php

namespace App\Services;

use App\Repositories\AccountRepository;

class AccountService
{
    private $repository;
    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function validatePayer(int $id, float $value, bool $refund): array
    {
        if(!$this->repository->hasAccount($id))
            return [
                "code" => "PayerNotFoundException",
                "message" => "payer not found"
            ];

        $account = $this->repository->getAccount($id);
        if($refund && $account->type != "user")
            return [
                "code" => "PayerIsNotUserException",
                "message" => "only users can be payer"
            ];

        if($account->balance < $value)
            return [
                "code" => "PayerInsufficientBalanceException",
                "message" => "payer does not have enough balance for the transaction"
            ];

        return [];
    }

    public function validatePayee(int $id): array
    {
        if(!$this->repository->hasAccount($id))
            return [
                "code" => "PayeeNotFoundException",
                "message" => "payee not found"
            ];

        return [];
    }

    public function increaseBalance(int $id, float $value): void
    {
        $this->repository->increaseBalance($id, $value);
    }

    public function decreaseBalance(int $id, float $value): void
    {
        $this->repository->decreaseBalance($id, $value);
    }
}
