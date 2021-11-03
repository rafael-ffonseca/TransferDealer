<?php

namespace App\Http\Controllers;

use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
     * @OA\Post(
     *     path="/transaction",
     *     operationId="/transaction",
     *     tags={"Transaction"},
     *     @OA\Parameter(
     *         name="payer",
     *         in="query",
     *         description="The payer account id who balance will be decreased",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="payee",
     *         in="query",
     *         description="The payee account id who balance will be increased",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="value",
     *         in="query",
     *         description="The amount that will be transferred",
     *         required=true,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns Success for the transaction",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="transactionId", type="integer"),
     *             @OA\Property(property="warning", type="string", example="Filled when any notice occurs in the transaction"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Transaction is not able to be completed. Check errors bellow",
     *         @OA\JSonContent(
     *             @OA\Property(property="code", type="string",
     *                 example={"PayerNotFoundException", "PayerIsNotUserException", "PayerInsufficientBalanceException",
     *                          "PayeeNotFoundException", "UnauthorizedTransactionException", "AuthorizationServiceUnavailableException"}),
     *             @OA\Property(property="message", type="string",
     *                 example={"payer not found", "only users can be payer", "payer does not have enough balance for the transaction",
     *                          "payee not found", "transaction unauthorized by authorization service", "transaction cannot be processed by authorization service"}),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *         @OA\JsonContent(
     *             @OA\Property(property="payer", type="array", @OA\Items(
     *                 type="string", example="payer validation errors",
     *             )),
     *             @OA\Property(property="payee", type="array", @OA\Items(
     *                 type="string", example="payee validation errors",
     *             )),
     *             @OA\Property(property="value", type="array", @OA\Items(
     *                 type="string", example="value validation errors",
     *             )),
     *         )
     *     ),
     * )
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

    /**
     * @OA\Post(
     *     path="/revertTransaction",
     *     operationId="/revertTransaction",
     *     tags={"Transaction"},
     *     @OA\Parameter(
     *         name="transactionId",
     *         in="query",
     *         description="The transaction id to be reverted",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns Success for the revert",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success"),
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Transaction revert is not able to be completed. Check errors bellow",
     *         @OA\JSonContent(
     *             @OA\Property(property="code", type="string",
     *                 example={"TransactionNotFoundException", "PayerInsufficientBalanceException"}),
     *             @OA\Property(property="message", type="string",
     *                 example={"transaction does not exists", "payer does not have enough balance for the transaction"}),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request. When required parameters were not supplied.",
     *         @OA\JsonContent(
     *             @OA\Property(property="transactionId", type="array", @OA\Items(
     *                 type="string", example="transaction id validation errors",
     *             )),
     *         )
     *     ),
     * )
     */
    public function revertTransaction(Request $request): JsonResponse
    {
        $this->validate($request, [
            'transactionId' => 'required|integer',
        ]);

        $transactionId = Arr::get($request->toArray(), 'transactionId');

        return $this->transactionService->revertTransaction($transactionId);
    }
}
