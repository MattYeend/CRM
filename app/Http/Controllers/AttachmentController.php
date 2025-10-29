<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        return response()->json(
            Attachment::with('uploader')->paginate($perPage)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Attachment $attachment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Attachment $attachment)
    {
        return response()->json($attachment->load('uploader'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|max:10000',
            'attachable_type' => 'nullable|string',
            'attachable_id' => 'nullable|integer',
            'uploaded_by' => 'nullable|integer|exists:users,id',
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments', 'public');

        $attachment = Attachment::create([
            'filename' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getClientMimeType(),
            'uploaded_by' => $data['uploaded_by'] ?? null,
        ]);

        if (isset($data['attachable_type']) && isset($data['attachable_id'])) {
            try {
                $model = app($data['attachable_type'])->find($data['attachable_id']);
                if ($model) {
                    $model->attachments()->save($attachment);
                }
            } catch (\Throwable $e) {
                report($e); 
            }
        }

        return response()->json($attachment->load('uploader'), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \App\Models\Attachment $attachment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Attachment $attachment)
    {
        // No update functionality for attachments
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Attachment $attachment
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Attachment $attachment)
    {
        if ($attachment->disk && $attachment->path) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }
        $attachment->delete();
        return response()->json(null, 204);
    }
}
