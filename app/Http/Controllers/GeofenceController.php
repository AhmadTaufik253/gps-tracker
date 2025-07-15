<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class GeofenceController extends Controller
{
    public function geofenceExit(Request $request)
    {
        // $lat = $request->input('latitude');
        // $lng = $request->input('longitude');
        // $timestamp = $request->input('timestamp');

        // // Kirim email
        // Mail::raw("ALERT: User keluar dari geofence pada $timestamp. Lokasi: $lat, $lng", function ($message) {
        //     $message->to('ahmadtaufik2503@gmail.com')
        //             ->subject('Geofence Exit Notification');
        // });

        // Kirim WhatsApp via API pihak ketiga
        // Http::withHeaders([
        //     'Authorization' => 'Bearer YOUR_TOKEN',
        // ])->post('https://api.whatsapp-gateway.id/send-message', [
        //     'phone' => '6281234567890',
        //     'message' => "🚨 User keluar dari geofence!\nLokasi: $lat, $lng\nWaktu: $timestamp"
        // ]);

        return response()->json(['status' => 'sent']);
    }
}
