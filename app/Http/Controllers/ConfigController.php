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
                case 'all':
                    $configs = \Illuminate\Support\Facades\Cache::remember('global_configs', 3600, function() {
                        return Config::pluck('config_value', 'config_key')->toArray();
                    });
                    return response()->json($configs);

                case 'get':
                    $key = $request->input('key');
                    if ($key) {
                        $configValue = \Illuminate\Support\Facades\Cache::remember('config_' . $key, 3600, function() use ($key) {
                            $config = Config::where('config_key', $key)->first();
                            return $config ? $config->config_value : null;
                        });
                        return response()->json(["value" => $configValue]);
                    } else {
                        $configs = \Illuminate\Support\Facades\Cache::remember('global_configs', 3600, function() {
                            return Config::pluck('config_value', 'config_key')->toArray();
                        });
                        return response()->json($configs);
                    }

                case 'save':
                    if (!Auth::check() || Auth::user()->role !== 'admin') {
                        return response()->json(['error' => 'Não autorizado'], 403);
                    }
                    
                    $data = $request->json()->all() ?: $request->all();
                    if (!$data) throw new \Exception("Dados inválidos");
                    
                    foreach ($data as $key => $value) {
                        Config::updateOrCreate(
                            ['config_key' => $key],
                            ['config_value' => (is_array($value) || is_object($value)) ? json_encode($value) : $value]
                        );
                        \Illuminate\Support\Facades\Cache::forget('config_' . $key);
                    }
                    \Illuminate\Support\Facades\Cache::forget('global_configs');
                    return response()->json(["status" => "success"]);

                default:
                    return response()->json(['error' => 'Action not supported yet'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()], 400);
        }
    }
}
