<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::orderBy('created_at', 'desc');
        if ($request->has('unread')) $query->where('is_read', false);
        return response()->json($query->paginate(20));
    }

    public function show(Contact $contact)
    {
        $contact->update(['is_read' => true]);
        return response()->json($contact);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->json(['message' => 'Message deleted.']);
    }
}
