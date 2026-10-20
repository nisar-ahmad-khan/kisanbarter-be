<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of all reports (Admin only).
     */
    public function index(Request $request): JsonResponse
    {
        // Authorization: Only admins can view reports
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $reports = Report::with(['reporter', 'listing.user'])->latest()->paginate(15);

        return response()->json($reports);
    }

    /**
     * Store a newly created report in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_id' => ['required', 'exists:listings,id'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $listing = Listing::findOrFail($validated['listing_id']);

        // Prevent users from reporting their own listings
        if ($listing->user_id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot report your own listing.',
            ], 422);
        }

        // Prevent duplicate active reports by the same user on the same listing if desired
        $existingReport = Report::where('reported_by', $request->user()->id)
            ->where('listing_id', $listing->id)
            ->first();

        if ($existingReport) {
            return response()->json([
                'message' => 'You have already reported this listing.',
            ], 422);
        }

        $report = Report::create([
            'reported_by' => $request->user()->id,
            'listing_id' => $validated['listing_id'],
            'reason' => $validated['reason'],
        ]);

        return response()->json([
            'message' => 'Report submitted successfully. Our team will review it shortly.',
            'report' => $report->load(['reporter', 'listing']),
        ], 201);
    }

    /**
     * Remove or resolve the specified report (Admin only).
     */
    public function destroy(Request $request, Report $report): JsonResponse
    {
        // Authorization: Only admins can resolve/delete reports
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $report->delete();

        return response()->json([
            'message' => 'Report resolved and removed successfully.',
        ]);
    }
}