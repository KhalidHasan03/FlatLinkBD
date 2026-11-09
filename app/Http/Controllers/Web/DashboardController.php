<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return match (true) {
            $user->isSuperAdmin(), $user->isStaff() => redirect()->route('admin.dashboard'),
            $user->isSeller() => redirect()->route('seller.dashboard'),
            default => redirect()->route('buyer.dashboard'),
        };
    }
}
