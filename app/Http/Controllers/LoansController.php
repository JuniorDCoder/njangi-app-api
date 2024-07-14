<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLoanRequest;
use Illuminate\Contracts\Validation\Validator;

class LoansController extends Controller
{
    public function store(StoreLoanRequest $request)
    {
        $loan = Loan::create([
            'user_id' => $request->user_id,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Loan created successfully',
            'data' => $loan
        ], 201);
    }

}
