<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ListingController extends Controller
{
    /**
     * Display a listing of the resource with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Listing::with(['user', 'images']);

        // Filter by trade type (sale, exchange, both)
        if ($request->filled('trade_type')) {
            $query->where('trade_type', $request->trade_type);
        }

        // Filter by user district through the relationship
        if ($request->filled('district')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('district', 'like', '%' . $request->district . '%');
            });
        }

        // Search by title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by specific user listings
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status:
        // If a status is explicitly provided, filter by it (unless 'all' is passed)
        // By default, if not filtering by user_id, only show active listings
        if ($request->filled('status')) {
            if ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        } elseif (!$request->filled('user_id')) {
            $query->where('status', 'active');
        }

        $listings = $query->latest()->paginate(15);

        return response()->json($listings);
    }

    /**
     * Store a newly created listing in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'trade_type' => ['required', 'in:sale,exchange,both'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'qty' => ['required', 'string', 'max:50'],
            'wanted_description' => ['required', 'string'],
            'images' => ['required', 'min:1', 'array', 'max:5'], // Max 5 images
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // 2MB max per image
        ]);

        $listing = $request->user()->listings()->create([
            'trade_type' => $validated['trade_type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'qty' => $validated['qty'],
            'status' => 'active',
            'wanted_description' => $validated['wanted_description'] ?? null,
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('listings', 'public');
                $listing->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json([
            'message' => 'Listing created successfully',
            'listing' => $listing->load(['user', 'images']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Listing $listing): JsonResponse
    {
        return response()->json([
            'listing' => $listing->load(['user', 'images']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Listing $listing): JsonResponse
    {
        // Authorization check: only owner can update
        if ($request->user()->id !== $listing->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'trade_type' => ['sometimes', 'required', 'in:sale,exchange,both'],
            'status' => ['sometimes', 'required', 'in:active,inactive'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'qty' => ['sometimes', 'required', 'string', 'max:50'],
            'wanted_description' => ['nullable', 'string'],
        ]);

        $listing->update($validated);

        return response()->json([
            'message' => 'Listing updated successfully',
            'listing' => $listing->fresh(['user', 'images']),
        ]);
    }

    /**
     * Toggle the active/inactive status of a listing.
     */
    public function toggleStatus(Request $request, Listing $listing): JsonResponse
    {
        // Authorization check: only owner can toggle
        if ($request->user()->id !== $listing->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $newStatus = $listing->status === 'active' ? 'inactive' : 'active';
        $listing->update(['status' => $newStatus]);

        return response()->json([
            'message' => "Listing status changed to {$newStatus}",
            'listing' => $listing->fresh(['user', 'images']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Listing $listing): JsonResponse
    {
        // Authorization check: only owner (or admin) can delete
        if ($request->user()->id !== $listing->user_id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete associated image files from disk
        foreach ($listing->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        $listing->delete();

        return response()->json([
            'message' => 'Listing deleted successfully',
        ]);
    }
}
