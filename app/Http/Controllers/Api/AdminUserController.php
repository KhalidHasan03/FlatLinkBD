<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Seller;
use App\Models\Buyer;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function sellers(Request $request)
    {
        $query = User::with('seller.properties')->where('role', 'seller');
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function buyers(Request $request)
    {
        $query = User::with('buyer')->where('role', 'buyer');
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $validated = $request->validate(['status' => 'required|in:active,inactive,banned']);
        $user->update($validated);
        return response()->json($user);
    }
}
