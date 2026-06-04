<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\LogisticsJob;
use Illuminate\Http\Request;

class BidController extends Controller
{
    // Submit a bid on a job (agents only)
    public function store(Request $request, $jobId)
    {
        $user = $request->user();

        if ($user->role !== 'agent') {
            return response()->json(['message' => 'Only agents can submit bids.'], 403);
        }

        $job = LogisticsJob::findOrFail($jobId);

        if ($job->status !== 'open') {
            return response()->json(['message' => 'This job is no longer accepting bids.'], 422);
        }

        // Enforce one bid per agent per job
        $existingBid = Bid::where('job_id', $jobId)
            ->where('agent_id', $user->id)
            ->first();

        if ($existingBid) {
            return response()->json(['message' => 'You have already submitted a bid for this job.'], 422);
        }

        $validated = $request->validate([
            'amount'                   => 'required|numeric|min:0',
            'message'                  => 'required|string',
            'estimated_completion_time' => 'required|string|max:255',
        ]);

        $bid = Bid::create([
            ...$validated,
            'job_id'   => $jobId,
            'agent_id' => $user->id,
            'status'   => 'pending',
        ]);

        return response()->json($bid->load('agent'), 201);
    }

    // Get all bids for a job (job owner only)
    public function index(Request $request, $jobId)
    {
        $job = LogisticsJob::findOrFail($jobId);

        if ($request->user()->id !== $job->importer_id) {
            return response()->json(['message' => 'You do not own this job.'], 403);
        }

        $bids = Bid::where('job_id', $jobId)
            ->with('agent')
            ->get();

        return response()->json($bids);
    }

    // Accept a bid (job owner only)
    public function accept(Request $request, $bidId)
    {
        $bid = Bid::with('job')->findOrFail($bidId);
        $job = $bid->job;

        if ($request->user()->id !== $job->importer_id) {
            return response()->json(['message' => 'You do not own this job.'], 403);
        }

        if ($job->status !== 'open') {
            return response()->json(['message' => 'This job has already been awarded.'], 422);
        }

        // Accept this bid
        $bid->update(['status' => 'accepted']);

        // Reject all other bids for this job
        Bid::where('job_id', $job->id)
            ->where('id', '!=', $bid->id)
            ->update(['status' => 'rejected']);

        // Update job status to awarded
        $job->update(['status' => 'awarded']);

        return response()->json([
            'message' => 'Bid accepted successfully.',
            'bid'     => $bid,
            'job'     => $job,
        ]);
    }
}
