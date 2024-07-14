<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{
    public function getAllUsers(){
        $users = User::all();

        return response()->json($users, 200);
    }
}
