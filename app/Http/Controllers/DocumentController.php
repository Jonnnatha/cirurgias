<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SurgeryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(Request $request, SurgeryRequest $surgeryRequest)
    {
        $this->authorize('update', $surgeryRequest);

        $data = $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $data['file'];
        $path = $file->store('documents');

        $surgeryRequest->documents()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('ok', 'Documento enviado!');
    }

    public function destroy(SurgeryRequest $surgeryRequest, Document $document)
    {
        $this->authorize('update', $surgeryRequest);
        abort_unless($document->surgery_request_id === $surgeryRequest->id, 404);

        Storage::delete($document->path);
        $document->delete();

        return back()->with('ok', 'Documento removido.');
    }
}
