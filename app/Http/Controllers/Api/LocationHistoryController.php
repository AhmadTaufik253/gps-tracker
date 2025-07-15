<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationHistory;
use Illuminate\Http\Request;

class LocationHistoryController extends Controller
{
    // Simpan lokasi
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        LocationHistory::create($validated);

        return response()->json(['message' => 'Location saved'], 201);
    }

    // Ambil riwayat berdasarkan device_id
    public function index($deviceId)
    {
        $locations = LocationHistory::where('device_id', $deviceId)
            ->orderBy('created_at')
            ->get(['latitude', 'longitude', 'created_at']);

        return response()->json($locations);
    }
}
