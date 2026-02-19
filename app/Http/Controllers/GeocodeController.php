<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodeController extends Controller
{
    /**
     * Forward Geocoding
     * /api/geocode?q=226010
     */
    public function search(Request $request)
    {
        $query = $request->query('q');

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Query is required.'
            ], 400);
        }

        try {

            $response = Http::timeout(10)
                ->withHeaders([
                    // REQUIRED by Nominatim usage policy
                    'User-Agent' => 'OohApp/1.0 (admin@oohapp.io)'
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format'         => 'json',
                    'limit'          => 1,
                    'addressdetails' => 1,
                    'countrycodes'   => 'in',
                    'q'              => $query
                ]);

            if (!$response->successful()) {
                Log::error('Geocode API error', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Location service unavailable.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data'    => $response->json()
            ]);

        } catch (\Exception $e) {

            Log::error('Geocode exception', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Geocoding failed.'
            ], 500);
        }
    }

    /**
     * Reverse Geocoding
     * /api/reverse-geocode?lat=26.8467&lng=81.0279
     */
    public function reverse(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if (!$lat || !$lng) {
            return response()->json([
                'success' => false,
                'message' => 'Latitude and Longitude are required.'
            ], 400);
        }

        try {

            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'OohApp/1.0 (admin@oohapp.io)'
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format'         => 'json',
                    'addressdetails' => 1,
                    'lat'            => $lat,
                    'lon'            => $lng
                ]);

            if (!$response->successful()) {

                Log::error('Reverse geocode API error', [
                    'status' => $response->status(),
                    'body'   => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Reverse geocoding failed.'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data'    => $response->json()
            ]);

        } catch (\Exception $e) {

            Log::error('Reverse geocode exception', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Reverse geocoding failed.'
            ], 500);
        }
    }
}
