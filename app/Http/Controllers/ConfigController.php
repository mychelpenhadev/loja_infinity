<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use Illuminate\Support\Facades\Auth;

class ConfigController extends Controller
{
    public function apiHandler(Request $request)
    {
        $action = $request->input('action', 'get');

        try {
            switch ($action) {
                case 'get':
                    $key = $request->input('key');
                    if ($key) {
                        $config = Config::where('config_key', $key)->first();
                        return response()->json(["value" => $config ? $config->config_value : null]);
                    } else {
                        $configs = Config::pluck('config_value', 'config_key')->toArray();
                        return response()->json($configs);
                    }

                case 'save':
                    if (!Auth::check() || Auth::user()->role !== 'admin') {
                        return response()->json(['error' => 'Não autorizado'], 403);
                    }
                    
                    $data = $request->json()->all() ?: $request->all();
                    if (!$data) throw new \Exception("Dados inválidos");
                    
                    // Cleanup removed banners if hero_banners is being updated
                    if (isset($data['hero_banners'])) {
                        $this->cleanupRemovedBanners($data['hero_banners']);
                    }

                    foreach ($data as $key => $value) {
                        Config::updateOrCreate(
                            ['config_key' => $key],
                            ['config_value' => (is_array($value) || is_object($value)) ? json_encode($value) : $value]
                        );
                    }
                    return response()->json(["status" => "success"]);

                case 'upload-banner':
                    if (!Auth::check() || Auth::user()->role !== 'admin') {
                        return response()->json(['error' => 'Não autorizado'], 403);
                    }
                    if (!$request->hasFile('banner')) {
                        return response()->json(["status" => "error", "message" => "Nenhum arquivo enviado"], 400);
                    }
                    if (!$request->file('banner')->isValid()) {
                        return response()->json(["status" => "error", "message" => "Erro no upload do arquivo"], 400);
                    }
                    
                    $file = $request->file('banner');
                    
                    // Limit to 4MB for banners
                    if ($file->getSize() > 4 * 1024 * 1024) {
                        return response()->json(["status" => "error", "message" => "O banner é muito grande. O limite é de 4MB."], 400);
                    }
                    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    if (!in_array($file->getMimeType(), $allowed)) {
                        return response()->json(["status" => "error", "message" => "Tipo não permitido"], 400);
                    }
                    
                    $filename = 'banner_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $uploadDir = public_path('uploads/banners');
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    // Additional check for permissions in hosted environment
                    if (!is_writable($uploadDir)) {
                        @chmod($uploadDir, 0777);
                    }
                    
                    $file->move($uploadDir, $filename);
                    
                    $url = asset('uploads/banners/' . $filename);
                    return response()->json(["status" => "success", "url" => $url]);

                default:
                    return response()->json(['error' => 'Action not supported yet'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()], 400);
        }
    }

    private function cleanupRemovedBanners($newBannersJson)
    {
        try {
            $oldBannersConfig = Config::where('config_key', 'hero_banners')->first();
            if (!$oldBannersConfig) return;

            $oldBanners = json_decode($oldBannersConfig->config_value, true) ?: [];
            $newBanners = is_array($newBannersJson) ? $newBannersJson : (json_decode($newBannersJson, true) ?: []);

            $newUrls = array_column($newBanners, 'url');

            foreach ($oldBanners as $oldBanner) {
                if (!isset($oldBanner['url'])) continue;
                $url = $oldBanner['url'];

                // If old URL is not in new URLs, delete file
                if (!in_array($url, $newUrls)) {
                    $this->deleteFileFromUrl($url);
                }
            }
        } catch (\Exception $e) {
            // Log or ignore cleanup errors
        }
    }

    private function deleteFileFromUrl($url)
    {
        try {
            // Check if it's a local URL (from this domain)
            $baseUrl = asset('/');
            if (str_starts_with($url, $baseUrl)) {
                $relativePath = str_replace($baseUrl, '', $url);
                $filePath = public_path($relativePath);
                if (file_exists($filePath) && !is_dir($filePath)) {
                    @unlink($filePath);
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }
    }
}
