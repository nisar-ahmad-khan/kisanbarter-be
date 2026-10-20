<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\ListingImages;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ListingImageController extends Controller
{
    /**
     * Add more images to an existing listing.
     */
    public function store(Request $request, Listing $listing): JsonResponse
    {
        // Authorization: only the owner can add images
        if ($request->user()->id !== $listing->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'images' => ['required', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // Max 2MB per image
        ]);

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('listings', 'public');
                $uploadedImages[] = $listing->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'images' => $uploadedImages,
        ], 201);
    }

    /**
     * Remove a specific listing image.
     */
    public function destroy(Request $request, ListingImages $image): JsonResponse
    {
        $listing = $image->listing;

        // Authorization: only the listing owner (or admin) can delete the image
        if ($request->user()->id !== $listing->user_id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete file from disk
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        $image->delete();

        return response()->json([
            'message' => 'Image deleted successfully',
        ]);
    }
}