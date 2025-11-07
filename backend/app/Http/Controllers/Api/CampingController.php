<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Camping;
use Illuminate\Http\Request;

class CampingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Camping::with(['location', 'owner', 'spots']);

        // Keresés név alapján
        if ($request->has('search')) {
            $query->where('camping_name', 'like', '%' . $request->search . '%');
        }

        // Szűrés város alapján
        if ($request->has('city')) {
            $query->whereHas('location', function($q) use ($request) {
                $q->where('city', 'like', '%' . $request->city . '%');
            });
        }

        // Szűrés helyszín alapján (város vagy megye)
        if ($request->has('location')) {
            $location = $request->location;
            $query->whereHas('location', function($q) use ($location) {
                $q->where('city', 'like', '%' . $location . '%')
                  ->orWhere('county', 'like', '%' . $location . '%');
            });
        }

        return response()->json($query->paginate(15));
    }

    /**
     * Get location suggestions for autocomplete
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Camping::with('location')
            ->whereHas('location', function($q) use ($query) {
                $q->where('city', 'like', '%' . $query . '%')
                  ->orWhere('county', 'like', '%' . $query . '%');
            })
            ->orWhere('camping_name', 'like', '%' . $query . '%')
            ->limit(10)
            ->get()
            ->map(function($camping) {
                return [
                    'id' => $camping->id,
                    'name' => $camping->camping_name,
                    'city' => $camping->location->city,
                    'county' => $camping->location->county,
                    'label' => $camping->camping_name . ' - ' . $camping->location->city . ', ' . $camping->location->county
                ];
            })
            ->unique('label')
            ->values();

        return response()->json($suggestions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'camping_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string',
            'location' => 'required|array',
            'location.postcode' => 'required|string|max:10',
            'location.county' => 'required|string|max:100',
            'location.city' => 'required|string|max:100',
            'location.street' => 'required|string|max:255',
            'location.street_number' => 'required|string|max:20',
            'spots' => 'nullable|array',
            'spots.*.name' => 'required|string|max:255',
            'spots.*.row' => 'required|integer|min:1',
            'spots.*.column' => 'required|integer|min:1',
            'spots.*.type' => 'required|string',
            'spots.*.capacity' => 'required|integer|min:1',
            'spots.*.price_per_night' => 'required|integer|min:0',
            'spots.*.is_available' => 'boolean',
            'spots.*.description' => 'nullable|string',
            'spots.*.tags' => 'nullable|string',
            'spots.*.services' => 'nullable|string',
        ]);

        // Location létrehozása
        $location = \App\Models\Location::create($validated['location']);

        // Camping létrehozása
        $camping = $request->user()->campings()->create([
            'camping_name' => $validated['camping_name'],
            'location_id' => $location->id,
            'description' => $validated['description'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'tax_id' => $validated['tax_id'] ?? null,
            'billing_address' => $validated['billing_address'] ?? null
        ]);

        // Spots létrehozása ha van
        if (isset($validated['spots'])) {
            foreach ($validated['spots'] as $spotData) {
                $camping->spots()->create($spotData);
            }
        }

        return response()->json($camping->load(['location', 'owner', 'spots']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $camping = Camping::with(['location', 'owner', 'spots', 'comments.user'])->findOrFail($id);
        return response()->json($camping);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $camping = Camping::findOrFail($id);

        // Csak a tulajdonos módosíthatja
        if ($camping->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'camping_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'billing_address' => 'nullable|string'
        ]);

        $camping->update($validated);

        return response()->json($camping->load(['location', 'owner']));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $camping = Camping::findOrFail($id);

        // Csak a tulajdonos törölheti
        if ($camping->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $camping->delete();

        return response()->json(['message' => 'Camping deleted successfully']);
    }
}
