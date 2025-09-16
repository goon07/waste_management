<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller; // Add this import

class AreaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:council_admin']);
    }

    public function index()
    {
        $councilId = Auth::user()->council_id;
        $areas = Area::where('council_id', $councilId)->get();
        return view('council.areas.index', compact('areas'));
    }

    public function create()
    {
        return view('council.areas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            Area::create([
                'council_id' => Auth::user()->council_id,
                'name' => $request->name,
                'description' => $request->description,
            ]);
            return redirect()->route('council.areas.index')->with('success', 'Area created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating area: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create area.');
        }
    }

    public function edit(Area $area)
    {
        $this->authorizeCouncilArea($area);
        return view('council.areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area)
    {
        $this->authorizeCouncilArea($area);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $area->update($request->only('name', 'description'));
            return redirect()->route('council.areas.index')->with('success', 'Area updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating area: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update area.');
        }
    }

    public function destroy(Area $area)
    {
        $this->authorizeCouncilArea($area);

        try {
            $area->delete();
            return redirect()->route('council.areas.index')->with('success', 'Area deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting area: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete area.');
        }
    }

    private function authorizeCouncilArea(Area $area)
    {
        if ($area->council_id !== Auth::user()->council_id) {
            abort(403, 'Unauthorized');
        }
    }
}