<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function apiHandler(Request $request)
    {
        $action = $request->input('action', 'list');

        try {
            switch ($action) {
                case 'list':
                    if (!Auth::check() || Auth::user()->role !== 'admin') {
                        return response()->json(['error' => 'Não autorizado'], 403);
                    }
                    $limit = $request->input('limit', 20);
                    $query = Order::orderBy('created_at', 'desc');
                    
                    $total = $query->count();
                    $orders = $query->paginate($limit);

                    return response()->json([
                        'orders' => $orders->items(),
                        'pagination' => [
                            'total' => $total,
                            'page' => $orders->currentPage(),
                            'limit' => (int)$limit,
                            'pages' => $orders->lastPage()
                        ]
                    ]);

                case 'list_user':
                    $userId = $request->input('user_id', Auth::id());
                    if (!$userId) {
                        return response()->json([]);
                    }
                    $orders = Order::where('user_id', $userId)
                                   ->orderBy('created_at', 'desc')
                                   ->get();
                    return response()->json($orders);

                case 'save':
                    // Raw info might come as JSON payload
                    $data = $request->json()->all() ?: $request->all();
                    
                    $order = new Order();
                    $order->external_id = $data['external_id'] ?? $data['externalId'] ?? ('ORD' . mt_rand(1000, 9999));
                    $order->user_id = $data['user_id'] ?? $data['userId'] ?? Auth::id();
                    $order->user_name = $data['user_name'] ?? $data['userName'] ?? (Auth::check() ? Auth::user()->name : 'Visitante');
                    $order->total = $data['total'];
                    $order->status = $data['status'] ?? 'pendente';
                    $order->method = $data['method'] ?? 'WhatsApp';
                    
                    $items = $data['items'] ?? $data['items_json'] ?? [];
                    // Ensure it is stored as JSON string as in legacy, Eloquent casts handles it if configured,
                    // but we will cast to JSON string explicitly.
                    $order->items_json = is_string($items) ? $items : json_encode($items);

                    $order->save();

                    return response()->json(["status" => "success", "id" => $order->id]);

                case 'update_status':
                    if (!Auth::check() || Auth::user()->role !== 'admin') {
                        return response()->json(['error' => 'Não autorizado'], 403);
                    }
                    $id = $request->input('id');
                    $status = $request->input('status');
                    if (!$id || !$status) throw new \Exception("Parâmetros faltantes");
                    
                    $order = Order::findOrFail($id);
                    $order->status = $status;
                    $order->save();
                    return response()->json(["status" => "success"]);

                case 'delete_user':
                    $id = $request->input('id');
                    $userId = Auth::id();
                    if (!$id || !$userId) {
                        return response()->json(["status" => "error", "message" => "ID ou usuário não fornecido"], 400);
                    }
                    
                    $order = Order::where('id', $id)->where('user_id', $userId)->firstOrFail();
                    $allowedStatus = ['entregue', 'concluido'];
                    
                    if (!in_array(strtolower($order->status), $allowedStatus)) {
                        return response()->json(["status" => "error", "message" => "Só é possível excluir pedidos entregues ou concluídos"], 400);
                    }
                    
                    $order->delete();
                    return response()->json(["status" => "success"]);

                case 'delete':
                    if (!Auth::check() || Auth::user()->role !== 'admin') {
                        return response()->json(['error' => 'Não autorizado'], 403);
                    }
                    $id = $request->input('id');
                    if (!$id) throw new \Exception("ID não fornecido");
                    
                    Order::findOrFail($id)->delete();
                    return response()->json(["status" => "success"]);

                default:
                    return response()->json(['error' => 'Action not supported yet'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()], 400);
        }
    }
}
