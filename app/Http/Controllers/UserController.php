<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::select(
            'id',
            'name',
            'username',
            'email',
            'role',
            'created_at'
        )->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diambil',
            'data' => $users
        ], 200);
    }
}
