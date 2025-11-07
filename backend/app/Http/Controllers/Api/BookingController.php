<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CampingSpot;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'camping', 'campingSpot']);

        // User saját foglalásai
        if ($request->has('my_bookings')) {
            $query->where('user_id', $request->user()->id);
        }

        // Kemping foglalásai (owner számára)
        if ($request->has('camping_id')) {
            $camping = \App\Models\Camping::findOrFail($request->camping_id);
            if ($camping->user_id === $request->user()->id) {
                $query->where('camping_id', $request->camping_id);
            } else {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
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
            'camping_spot_id' => 'required|exists:camping_spots,id',
            'arrival_date' => 'required|date|after_or_equal:today',
            'departure_date' => 'required|date|after:arrival_date'
        ]);

        // Ellenőrizzük hogy a hely szabad-e
        $spot = CampingSpot::findOrFail($validated['camping_spot_id']);
        
        if (!$spot->is_available) {
            return response()->json(['message' => 'Camping spot is not available'], 400);
        }

        // Ellenőrizzük hogy nincs-e már foglalás ebben az időpontban
        $overlapping = Booking::where('camping_spot_id', $validated['camping_spot_id'])
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($validated) {
                $query->whereBetween('arrival_date', [$validated['arrival_date'], $validated['departure_date']])
                    ->orWhereBetween('departure_date', [$validated['arrival_date'], $validated['departure_date']])
                    ->orWhere(function($q) use ($validated) {
                        $q->where('arrival_date', '<=', $validated['arrival_date'])
                          ->where('departure_date', '>=', $validated['departure_date']);
                    });
            })
            ->exists();

        if ($overlapping) {
            return response()->json(['message' => 'Camping spot is already booked for these dates'], 400);
        }

        $booking = $request->user()->bookings()->create([
            'camping_id' => $validated['camping_id'],
            'camping_spot_id' => $validated['camping_spot_id'],
            'arrival_date' => $validated['arrival_date'],
            'departure_date' => $validated['departure_date'],
            'status' => 'pending'
        ]);

        return response()->json($booking->load(['camping', 'campingSpot']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $booking = Booking::with(['user', 'camping', 'campingSpot'])->findOrFail($id);

        // Csak a saját foglalását vagy a kemping tulajdonosa láthatja
        if ($booking->user_id !== $request->user()->id && 
            $booking->camping->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($booking);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,checked_in,checked_out,cancelled'
        ]);

        // User csak a saját foglalását törölheti (cancelled)
        if ($request->user()->id === $booking->user_id && isset($validated['status']) && $validated['status'] === 'cancelled') {
            $booking->update(['status' => 'cancelled']);
            return response()->json($booking);
        }

        // Owner változtathatja a státuszt
        if ($booking->camping->user_id === $request->user()->id) {
            $booking->update($validated);
            return response()->json($booking);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $booking = Booking::findOrFail($id);

        // Csak a kemping tulajdonosa törölheti véglegesen
        if ($booking->camping->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking->delete();

        return response()->json(['message' => 'Booking deleted successfully']);
    }
}
