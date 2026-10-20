<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Offers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{
    /**
     * Display a listing of offers sent or received by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $type = $request->query('type', 'received'); // 'received' or 'sent'

        $query = Offers::with(['listing.user', 'listing.images', 'offeredBy', 'messages']);

        if ($type === 'sent') {
            $query->where('offered_by', $user->id);
        } else {
            // Received offers for listings owned by the user
            $query->whereHas('listing', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $offers = $query->latest()->paginate(15);

        return response()->json($offers);
    }

    /**
     * Store a newly created offer in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'exists:lisitings,id'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $listing = Listing::findOrFail($validated['listing_id']);

        // Prevent users from offering on their own listings
        if ($listing->user_id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot make an offer on your own listing.',
            ], 422);
        }

        // Check if listing is active
        if ($listing->status !== 'active') {
            return response()->json([
                'message' => 'This listing is no longer active.',
            ], 422);
        }

        $offer = DB::transaction(function () use ($validated, $request, $listing) {
            $offer = Offers::create([
                'listing_id' => $listing->id,
                'offered_by' => $request->user()->id,
                'status' => 'pending',
                'message' => $validated['message'] ?? null,
            ]);

            return $offer;
        });

        return response()->json([
            'message' => 'Offer submitted successfully',
            'offer' => $offer->load(['listing.user', 'offeredBy']),
        ], 201);
    }

    /**
     * Display the specified offer.
     */
    public function show(Request $request, Offers $offer): JsonResponse
    {
        $user = $request->user();
        $listingOwnerId = $offer->listing->user_id;

        // Authorization: Only offer creator or listing owner can view
        if ($offer->offered_by !== $user->id && $listingOwnerId !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'offer' => $offer->load(['listing.user', 'listing.images', 'offeredBy', 'messages.sender']),
        ]);
    }

    /**
     * Accept an offer.
     */
    public function accept(Request $request, Offers $offer): JsonResponse
    {
        $user = $request->user();
        
        // Authorization: Only the listing owner can accept an offer
        if ($offer->listing->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$offer->isPending()) {
            return response()->json([
                'message' => 'Only pending offers can be accepted.',
            ], 422);
        }

        DB::transaction(function () use ($offer) {
            // Update offer status
            $offer->update(['status' => 'accepted']);

            // Mark the target listing as reserved/traded so it cannot receive other accepted offers
            $offer->listing->update(['status' => 'reserved']);

            // Optional: Reject other pending offers for this listing
            Offers::where('listing_id', $offer->listing_id)
                ->where('id', '!=', $offer->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);
        });

        return response()->json([
            'message' => 'Offer accepted successfully',
            'offer' => $offer->fresh(['listing', 'offeredBy']),
        ]);
    }

    /**
     * Reject an offer.
     */
    public function reject(Request $request, Offers $offer): JsonResponse
    {
        $user = $request->user();

        // Authorization: Only the listing owner can reject an offer
        if ($offer->listing->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$offer->isPending()) {
            return response()->json([
                'message' => 'Only pending offers can be rejected.',
            ], 422);
        }

        $offer->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Offer rejected successfully',
            'offer' => $offer->fresh(['listing', 'offeredBy']),
        ]);
    }

    /**
     * Cancel an offer (by the user who made it).
     */
    public function cancel(Request $request, Offers $offer): JsonResponse
    {
        $user = $request->user();

        // Authorization: Only the user who made the offer can cancel it
        if ($offer->offered_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$offer->isPending()) {
            return response()->json([
                'message' => 'Only pending offers can be cancelled.',
            ], 422);
        }

        $offer->delete(); // Or update status to 'cancelled' if tracking history is required

        return response()->json([
            'message' => 'Offer cancelled successfully',
        ]);
    }
}