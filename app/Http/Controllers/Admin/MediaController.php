<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    // Supported morph types — prevents arbitrary class injection
    private const ALLOWED_MODELS = [
        'project' => \App\Models\Project::class,
        'unit'    => \App\Models\Unit::class,
        'client'  => \App\Models\Client::class,
    ];

    public function upload(Request $request, string $modelType, int $modelId)
    {
        abort_unless(array_key_exists($modelType, self::ALLOWED_MODELS), 404);

        $modelClass = self::ALLOWED_MODELS[$modelType];
        $model      = $modelClass::findOrFail($modelId);
        $collection = $request->input('collection', 'images');

        $request->validate([
            'file'       => ['required', 'file', 'max:20480'], // 20 MB
            'collection' => ['sometimes', 'string'],
            // Document metadata (stored in custom_properties)
            'title'      => ['sometimes', 'string', 'max:255'],
            'category'   => ['sometimes', 'string'],
            'status'     => ['sometimes', 'string'],
            'expires_at' => ['sometimes', 'nullable', 'date'],
            'requires_signature' => ['sometimes', 'boolean'],
        ]);

        $media = $model
            ->addMediaFromRequest('file')
            ->withCustomProperties([
                'title'              => $request->input('title'),
                'category'           => $request->input('category'),
                'status'             => $request->input('status', 'received'),
                'expires_at'         => $request->input('expires_at'),
                'requires_signature' => $request->boolean('requires_signature'),
                'tenant_id'          => $request->input('tenant_id', 1),
            ])
            ->toMediaCollection($collection);

        return response()->json([
            'id'       => $media->id,
            'name'     => $media->file_name,
            'url'      => $media->getUrl(),
            'thumb'    => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : null,
            'size'     => $media->size,
            'mime'     => $media->mime_type,
        ]);
    }

    public function destroy(Media $media)
    {
        $media->delete();

        return response()->json(['deleted' => true]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        Media::setNewOrder($request->input('ids'));

        return response()->json(['reordered' => true]);
    }
}
