<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampingSpot;
use App\Models\Camping;
use Illuminate\Http\Request;

class CampingSpotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CampingSpot::with('camping');

        // Szűrés kemping alapján
        if ($request->has('camping_id')) {
            $query->where('camping_id', $request->camping_id);
        }

        // Szűrés típus alapján
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Csak szabad helyek
        if ($request->has('available') && $request->available) {
            $query->where('is_available', true);
        }

        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'camping_id' => 'required|exists:campings,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:tent,caravan,camper,bungalow',
            'capacity' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'row' => 'required|integer',
            'column' => 'required|integer',
            'tags' => 'nullable|array',
            'services' => 'nullable|array'
        ]);

        // Ellenőrizzük hogy a user tulajdonosa-e a kempingnek
        $camping = Camping::findOrFail($validated['camping_id']);
        if ($camping->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $spot = CampingSpot::create($validated);

        return response()->json($spot->load('camping'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $spot = CampingSpot::with(['camping', 'bookings'])->findOrFail($id);
        return response()->json($spot);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $spot = CampingSpot::findOrFail($id);

        // Ellenőrizzük hogy a user tulajdonosa-e a kempingnek
        if ($spot->camping->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:tent,caravan,camper,bungalow',
            'capacity' => 'sometimes|integer|min:1',
            'price_per_night' => 'sometimes|numeric|min:0',
            'is_available' => 'boolean',
            'row' => 'sometimes|integer',
            'column' => 'sometimes|integer',
            'tags' => 'nullable|array',
            'services' => 'nullable|array'
        ]);

        $spot->update($validated);

        return response()->json($spot->load('camping'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $spot = CampingSpot::findOrFail($id);

        // Ellenőrizzük hogy a user tulajdonosa-e a kempingnek
        if ($spot->camping->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $spot->delete();

        return response()->json(['message' => 'Camping spot deleted successfully']);
    }
}
