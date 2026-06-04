<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogisticsJob;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'importer') {
            $jobs = LogisticsJob::where('importer_id', $user->id)
                ->withCount('bids')->latest()->get();
        } else {
            $jobs = LogisticsJob::where('status', 'open')
                ->withCount('bids')->latest()->get();
        }

        return response()->json($jobs);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'importer') {
            return response()->json(['message' => 'Only importers can create jobs.'], 403);
        }

        $validated = $request->validate([
            'title'                  => 'required|string|max:255',
            'description'            => 'required|string',
            'origin_port'            => 'required|string|max:255',
            'destination'            => 'required|string|max:255',
            'budget'                 => 'required|numeric|min:0',
            'expected_delivery_date' => 'required|date|after:today',
        ]);

        $job = LogisticsJob::create([
            ...$validated,
            'importer_id' => $request->user()->id,
            'status'      => 'open',
        ]);

        return response()->json($job, 201);
    }

    public function show(Request $request, $id)
    {
        $job = LogisticsJob::with(['importer', 'bids.agent'])->findOrFail($id);
        return response()->json($job);
    }

    public function update(Request $request, $id)
    {
        $job = LogisticsJob::findOrFail($id);

        if ($request->user()->id !== $job->importer_id) {
            return response()->json(['message' => 'You do not own this job.'], 403);
        }

        $validated = $request->validate([
            'title'                  => 'sometimes|string|max:255',
            'description'            => 'sometimes|string',
            'origin_port'            => 'sometimes|string|max:255',
            'destination'            => 'sometimes|string|max:255',
            'budget'                 => 'sometimes|numeric|min:0',
            'expected_delivery_date' => 'sometimes|date',
            'status'                 => 'sometimes|in:open,awarded,in_progress,completed',
        ]);

        $job->update($validated);
        return response()->json($job);
    }

    public function destroy(Request $request, $id)
    {
        $job = LogisticsJob::findOrFail($id);

        if ($request->user()->id !== $job->importer_id) {
            return response()->json(['message' => 'You do not own this job.'], 403);
        }

        $job->delete();
        return response()->json(['message' => 'Job deleted successfully.']);
    }
}
