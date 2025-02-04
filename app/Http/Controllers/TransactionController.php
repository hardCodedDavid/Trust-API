<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use App\Services\User\TransactionService;
use Symfony\Component\HttpFoundation\Response;
use App\Spatie\QueryBuilder\IncludeSelectFields;
use App\Http\Requests\User\StoreTransactionRequest;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use App\DataTransferObjects\Models\TransactionModelData;
use App\Http\Controllers\NotificationController as Notifications;

class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param \App\Models\Transaction $transaction
     */
    public function __construct(public Transaction $transaction)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $transactions = QueryBuilder::for(
            $this->transaction->query()->where('user_id', $request->user()->id)
        )
            ->allowedFields($this->transaction->getQuerySelectables()) // Get the selectable fields dynamically
            ->allowedFilters([
                'status',
                'type', // Filter by transaction type (credit, debit, transfer)
                AllowedFilter::scope('creation_date'), // Custom filter scope for creation date
                AllowedFilter::exact('amount'), // Exact match filter for amount
                AllowedFilter::exact('transactable_id'), // Exact match filter for transactable ID
                AllowedFilter::exact('transactable_type'), // Filter by the type of transactable model (e.g., Wallet, Savings, Trade)
                AllowedFilter::scope('comment'), // Scope for filtering by comment (if applicable)
            ])
            ->allowedIncludes([
                AllowedInclude::custom('user', new IncludeSelectFields([
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                ])),
                AllowedInclude::custom('transactable', new IncludeSelectFields([
                    'id',
                    'type', // You can select more details depending on the model (e.g., Wallet, Trade, Savings)
                    'amount', // Amount associated with transactable
                    'status', // You can display status of the related model
                ])),
            ])
            ->defaultSort('-created_at') // Default sort by created_at descending
            ->allowedSorts([
                'status', // Sort by transaction status
                'type', // Sort by transaction type (credit, debit, transfer)
                'amount', // Sort by transaction amount
                'created_at', // Sort by creation date
                'updated_at', // Sort by updated date
            ])
            ->paginate((int) $request->per_page) // Paginate the results
            ->withQueryString(); // Retain query string for pagination links

            //::;::::: ADMIN
            // $user = $request->user();
            // $user->wallet->credit(1000, 'wallet', 'Bonus reward');
            // $user->wallet->debit(50, 'wallet', 'Bonus reward');
            // $balance = $request->user()->wallet->getBalance('wallet');
            //::;::::: ADMIN

        return ResponseBuilder::asSuccess()
            ->withMessage('Transactions fetched successfully')
            ->withData([
                'transactions' => $transactions,
            ])
            ->build();
    }

    /**
     * Create a new transaction.
     *
     * @param \App\Http\Requests\User\StoreTransactionRequest $request
     * @param \App\Services\TransactionService $transactionService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(
        StoreTransactionRequest $request,
        TransactionService $transactionService
    ): Response { 
        $transaction = $transactionService->create(
            (new TransactionModelData())
                ->setUserId($request->user()->id)
                ->setAmount((float) $request->amount)
                ->setTransactableId($request->user()->wallet->id)
                ->setTransactableType($request->transactable_type)
                ->setType($request->type)
                ->setStatus($request->status)
                ->setComment($request->comment),
            $request->user()
        );

        // $request->user()->wallet->credit($request->amount, 'wallet', 'Test Transaction');

        // Notifications::sendTestEmailNotification($request->user());

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('Transaction created successfully')
            ->withData([
                'transaction' => $transaction,
            ])
            ->build();
    }
}
