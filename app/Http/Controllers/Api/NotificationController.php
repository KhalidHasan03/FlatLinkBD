<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return response()->json(
            Notification::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(20)
        );
    }

    public function markRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->update(['read_at' => now()]);
        return response()->json($notification);
    }

    public function markAllRead()
    {
        Notification::where('user_id', auth()->id())->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function unreadCount()
    {
        $count = Notification::where('user_id', auth()->id())->whereNull('read_at')->count();
        return response()->json(['count' => $count]);
    }
}
