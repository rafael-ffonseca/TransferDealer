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

    public function validatePayer(int $payerId, float $value, bool $refund): array
    {
        if(!$this->repository->hasAccount($payerId))
            return [
                "code" => "PayerNotFoundException",
                "message" => "payer not found"
            ];

        $account = $this->repository->getAccount($payerId);
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

    public function validatePayee(int $payeeId): array
    {
        if(!$this->repository->hasAccount($payeeId))
            return [
                "code" => "PayeeNotFoundException",
                "message" => "payee not found"
            ];

        return [];
    }

    public function increaseBalance(int $accountId, float $value): void
    {
        $this->repository->increaseBalance($accountId, $value);
    }

    public function decreaseBalance(int $accountId, float $value): void
    {
        $this->repository->decreaseBalance($accountId, $value);
    }
}
