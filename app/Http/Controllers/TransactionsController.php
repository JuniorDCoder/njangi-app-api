<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionType;

class TransactionsController extends Controller
{
    public function store(Request $request){
        $validated = $request->validate([
            'user_name' => ['required', 'exists:users,name', 'string'],
            'amount' => ['required', 'numeric'],
            'type' => ['required', 'exists:transaction_types,name'],
            'date' => ['required_if:type,!=,loan', 'nullable', 'date'],
            'start_date' => ['required_if:type,loan', 'nullable', 'date'],
            'due_date' => ['required_if:type,loan', 'nullable', 'date'],
        ]);

        try {
            $user = User::where('name', $validated['user_name'])->first();
            $transactionType = TransactionType::where('name', $validated['type'])->first();

            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->transaction_type_id = $transactionType->id;
            $transaction->amount = $validated['amount'];
            $transaction->date = $validated['date'] ?? null;
            $transaction->start_date = $validated['start_date'] ?? null;
            $transaction->due_date = $validated['due_date'] ?? null;
            $transaction->save();

            return response()->json([
                'message' => 'Transaction successfully created',
                'transaction' => [
                    'id' => $transaction->id,
                    'user_name' => $user->name,
                    'transaction_type' => $transactionType->name,
                    'amount' => $transaction->amount,
                    'date' => $transaction->date,
                    'start_date' => $transaction->start_date,
                    'due_date' => $transaction->due_date,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'There was a problem creating the transaction',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getContributionTransactions()
    {
        $transactions = Transaction::with(['transactionType', 'user'])
            ->whereHas('transactionType', function ($query) {
                $query->where('id', 3);
            })
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'date' => $transaction->date,
                    'start_date' => $transaction->start_date,
                    'due_date' => $transaction->due_date,
                    'transaction_type' => $transaction->transactionType->name,
                    'user_name' => $transaction->user->name,
                    'user_host_number' => $transaction->user->host_number,
                    'total_amount' => $transaction->amount
                ];
            });

        return response()->json([
            'contributions' => $transactions,
        ], 200);
    }
}
