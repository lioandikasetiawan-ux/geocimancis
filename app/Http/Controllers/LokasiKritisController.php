<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LokasiKritisController extends Controller
{
    /**
     * Get all lokasi kritis data as GeoJSON from external API
     * Endpoint: GET /api/lokasi-kritis-mysql
     */
    public function getGeoJson()
    {
        try {
            // Panggil API yang sudah dibuat di server sisdacimancis.id
            $response = Http::timeout(30)->get('http://sisdacimancis.id/api_lokasi_kritis.php');
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Response dari api_lokasi_kritis.php: {"success":true,"total":389,"data":[...]}
                if (isset($data['success']) && $data['success'] === true && isset($data['data'])) {
                    $records = $data['data'];
                    
                    // Konversi ke format GeoJSON
                    $features = [];
                    foreach ($records as $record) {
                        $x = floatval($record['koordinat_x']);
                        $y = floatval($record['koordinat_y']);
                        
                        // Validasi koordinat
                        if ($x != 0 && $y != 0 && !is_null($x) && !is_null($y)) {
                            $features[] = [
                                'type' => 'Feature',
                                'geometry' => [
                                    'type' => 'Point',
                                    'coordinates' => [$y, $x]
                                ],
                                'properties' => $record
                            ];
                        }
                    }
                    
                    return response()->json([
                        'type' => 'FeatureCollection',
                        'features' => $features,
                        'total' => count($features),
                        'source' => 'MySQL - sisdacimancis.id'
                    ]);
                }
                
                // Jika response sudah dalam format GeoJSON
                if (isset($data['type']) && $data['type'] === 'FeatureCollection') {
                    return response()->json($data);
                }
                
                return response()->json([
                    'type' => 'FeatureCollection',
                    'features' => [],
                    'total' => 0,
                    'error' => 'Format data tidak dikenal'
                ]);
            }
            
            throw new \Exception('API request failed: ' . $response->status());
            
        } catch (\Exception $e) {
            Log::error('Error fetching lokasi kritis: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Gagal mengambil data lokasi kritis: ' . $e->getMessage(),
                'type' => 'FeatureCollection',
                'features' => [],
                'total' => 0
            ], 500);
        }
    }
    
    /**
     * Get lokasi kritis by ID
     * Endpoint: GET /api/lokasi-kritis-mysql/{id}
     */
    public function getById($id)
    {
        try {
            $response = Http::timeout(30)->get('http://sisdacimancis.id/api_lokasi_kritis.php');
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data'])) {
                    $record = collect($data['data'])->firstWhere('id_lokasi_kritis', $id);
                    
                    if ($record) {
                        return response()->json($record);
                    }
                }
            }
            
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Get lokasi kritis with filters
     * Endpoint: GET /api/lokasi-kritis-mysql/filter?kab_kota=&kecamatan=
     */
    public function getFiltered(Request $request)
    {
        try {
            $response = Http::timeout(30)->get('http://sisdacimancis.id/api_lokasi_kritis.php');
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data'])) {
                    $records = collect($data['data']);
                    
                    // Filter berdasarkan kabupaten/kota
                    if ($request->has('kab_kota') && $request->kab_kota) {
                        $records = $records->filter(function($item) use ($request) {
                            return stripos($item['kab_kota'], $request->kab_kota) !== false;
                        });
                    }
                    
                    // Filter berdasarkan kecamatan
                    if ($request->has('kecamatan') && $request->kecamatan) {
                        $records = $records->filter(function($item) use ($request) {
                            return stripos($item['kecamatan'], $request->kecamatan) !== false;
                        });
                    }
                    
                    // Filter berdasarkan tingkat kerusakan
                    if ($request->has('tingkat_kerusakan') && $request->tingkat_kerusakan) {
                        $records = $records->filter(function($item) use ($request) {
                            return $item['tingkat_kerusakan'] === $request->tingkat_kerusakan;
                        });
                    }
                    
                    // Konversi ke GeoJSON
                    $features = [];
                    foreach ($records as $record) {
                        $x = floatval($record['koordinat_x']);
                        $y = floatval($record['koordinat_y']);
                        
                        if ($x != 0 && $y != 0) {
                        $features[] = [
                            'type' => 'Feature',
                            'geometry' => [
                                'type' => 'Point',
                                'coordinates' => [$y, $x]  
                            ],
                            'properties' => $record
                        ];
                        }
                    }
                    
                    return response()->json([
                        'type' => 'FeatureCollection',
                        'features' => $features,
                        'total' => count($features)
                    ]);
                }
            }
            
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => [],
                'total' => 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}