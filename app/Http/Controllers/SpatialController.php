<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SpatialController extends Controller
{
    public function index()
    {
        return view('upload_spasial');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'spatial_file' => 'required|file'
        ]);

        $file = $request->file('spatial_file');
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $filename));
        
        $path = $file->storeAs('uploads', $file->getClientOriginalName(), 'public');
        $absolutePath = storage_path("app/public/" . $path);

        try {
            // 1. IMPORT KE POSTGIS
            $dbHost = env('DB_HOST');
            $dbPort = env('DB_PORT');
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');

            $command = "ogr2ogr -f \"PostgreSQL\" PG:\"host=$dbHost port=$dbPort user=$dbUser dbname=$dbName password=$dbPass\" \"$absolutePath\" -nln $cleanName -overwrite -lco GEOMETRY_NAME=geom -t_srs EPSG:4326";
            shell_exec($command);

            // 2. PUBLISH KE GEOSERVER
            $ws = "geocimancis";
            $ds = "postgis_db"; 
            $geoUrl = "http://localhost:8082/geoserver/rest/workspaces/$ws/datastores/$ds/featuretypes";

            $xml = "<featureType><name>$cleanName</name><title>$cleanName</title></featureType>";

            $response = Http::withBasicAuth('admin', 'geoserver')
                ->withHeaders(['Content-Type' => 'text/xml'])
                ->withBody($xml, 'text/xml')
                ->post($geoUrl);

            if ($response->successful() || $response->status() == 201) {
                return response()->json(['status' => 'success', 'message' => "Layer $cleanName berhasil dipublish!"]);
            }
            return response()->json(['status' => 'error', 'message' => 'Gagal publish ke GeoServer'], 500);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}