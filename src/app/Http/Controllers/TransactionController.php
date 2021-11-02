<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Laravel\Lumen\Routing\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class TransactionController extends Controller
{
    private $transactionService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Method that returns the amount of accounts
     *
     * @return bool
     */
    public function transfer(Request $request): JsonResponse
    {
        $this->validate($request, [
            'payer' => 'required|integer',
            'payee' => 'required|integer|different:payer',
            'value' => 'required|numeric',
        ]);

        $payerId = Arr::get($request->toArray(), 'payer');
        $payeeId = Arr::get($request->toArray(), 'payee');
        $value = Arr::get($request->toArray(), 'value');

        return $this->transactionService->transfer($payerId, $payeeId, $value);
    }

    public function revertTransaction(Request $request): JsonResponse
    {
        $this->validate($request, [
            'transaction' => 'required|integer',
        ]);

        $transactionId = Arr::get($request->toArray(), 'transaction');

        return $this->transactionService->revertTransaction($transactionId);
    }
}
