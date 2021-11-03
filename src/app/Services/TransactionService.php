<?php

namespace App\Services;

use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionService
{
    private $repository;
    private $accountService;
    public function __construct(TransactionRepository $repository, AccountService $accountService)
    {
        $this->repository = $repository;
        $this->accountService = $accountService;
    }

    public function transfer(int $payerId, int $payeeId, float $value): JsonResponse
    {
        $payerVerified = $this->accountService->validatePayer($payerId, $value, false);
        if(!empty($payerVerified))
            return new JsonResponse($payerVerified, 400);

        $payeeVerified = $this->accountService->validatePayee($payeeId);
        if(!empty($payeeVerified))
            return new JsonResponse($payeeVerified, 400);

        $authorization = $this->authorizeTransaction($payerId, $payeeId, $value);
        if(!empty($authorization))
            return new JsonResponse($authorization, 400);

        DB::beginTransaction();
        try {
            $this->accountService->decreaseBalance($payerId, $value);
            $this->accountService->increaseBalance($payeeId, $value);
            $transactionId = $this->repository->saveTransaction($payerId, $payeeId, $value);
            DB::commit();
        }
        catch(\Exception $e)
        {
            DB::rollBack();
            throw $e;
        }

        $messages["message"] = "Success";
        $messages["transactionId"] = $transactionId;

        $notifyTransaction = $this->notifyTransaction($payerId, $payeeId, $value);
        if(!empty($notifyTransaction))
            $messages["warning"] = $notifyTransaction;

        return new JsonResponse($messages, 200);
    }

    public function revertTransaction(int $transactionId): JsonResponse
    {
        if(!$this->repository->hasTransaction($transactionId))
            return new JsonResponse([
                "code" => "TransactionNotFoundException",
                "message" => "transaction does not exists"
            ], 400);

        $transaction = $this->repository->getTransaction($transactionId);

        $payerVerified = $this->accountService->validatePayer($transaction->payer, $transaction->value, true);
        if(!empty($payerVerified))
            return new JsonResponse($payerVerified, 400);

        $payeeVerified = $this->accountService->validatePayee($transaction->payee);
        if(!empty($payeeVerified))
            return new JsonResponse($payeeVerified, 400);

        DB::beginTransaction();
        try {
            $this->accountService->decreaseBalance($transaction->payee, $transaction->value);
            $this->accountService->increaseBalance($transaction->payer, $transaction->value);
            $this->repository->deleteTransaction($transactionId);
            DB::commit();
        }
        catch(\Exception $e)
        {
            DB::rollBack();
            throw $e;
        }

        return new JsonResponse(["message" => "Success"],200);
    }

    private function authorizeTransaction(int $payerId, int $payeeId, float $value): array
    {
        try {
            $authorization = Http::get('https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6', [
                "payer" => $payerId,
                "payee" => $payeeId,
                "value" => $value
            ]);
            if(!$authorization->successful() || $authorization['message'] != 'Autorizado')
                return [
                    "code" => "UnauthorizedTransactionException",
                    "message" => "transaction unauthorized by authorization service"
                ];
            }
            catch(\Exception $e)
        {
            return [
                "code" => "AuthorizationServiceUnavailableException",
                "message" => "transaction cannot be processed by authorization service"
            ];
        }
        return [];
    }

    private function notifyTransaction(int $payerId, int $payeeId, float $value): string
    {
        try {
            $authorization = Http::timeout(15)->retry(3, 100)->get('http://o4d9z.mocklab.io/notify');
            if(!$authorization->successful() || $authorization['message'] != 'Success')
                return "TransactionNotNotifiedException: transaction cannot be notified at this time";
        }
        catch(\Exception $e)
        {
            return "NotifyServiceUnavailableException: transaction cannot be processed by notify service";
        }
        return "";
    }
}
