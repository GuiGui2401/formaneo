<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::paginate(20);
        return response()->json($users);
    }

    public function toggleActive($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        return response()->json(['user'=>$user]);
    }

    public function show($id)
    {
        return response()->json(User::findOrFail($id));
    }
}
