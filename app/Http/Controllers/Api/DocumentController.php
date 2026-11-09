<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['uploader', 'deal', 'property']);
        if ($request->deal_id) $query->where('deal_id', $request->deal_id);
        if ($request->property_id) $query->where('property_id', $request->property_id);
        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'deal_id' => 'nullable|exists:deals,id',
            'property_id' => 'nullable|exists:properties,id',
            'name' => 'required|string|max:255',
            'file_path' => 'required|string',
            'type' => 'required|in:agreement,deed,id,other',
            'notes' => 'nullable|string',
        ]);
        $validated['uploaded_by'] = auth()->id();
        $document = Document::create($validated);
        return response()->json($document->load('uploader'), 201);
    }

    public function destroy(Document $document)
    {
        $document->delete();
        return response()->json(['message' => 'Document deleted.']);
    }
}
