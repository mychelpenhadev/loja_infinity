<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function apiHandler(Request $request)
    {
        $action = $request->input('action') ?? $request->query('action');

        switch ($action) {
            case 'register':
                return $this->register($request);
            case 'login':
                return $this->login($request);
            case 'google_login':
                return $this->googleLogin($request);
            case 'check':
                return $this->check($request);
            case 'logout':
                return $this->logout($request);
            case 'update_profile':
                return $this->updateProfile($request);
            case 'change_password':
                return $this->changePassword($request);
            case 'update_security':
                return $this->updateSecurity($request);
            default:
                return response()->json(["status" => "error", "message" => "Ação não informada, inválida ou não suportada no Laravel."]);
        }
    }

    private function register(Request $request)
    {
        $data = $request->json()->all() ?: $request->all();

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'cpf' => 'nullable|string|unique:users',
            'telefone' => 'nullable|string|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "error", "message" => $validator->errors()->first()]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'cpf' => $data['cpf'] ?? null,
            'telefone' => $data['telefone'] ?? null,
            'role' => 'cliente',
        ]);

        Auth::login($user);

        return response()->json(["status" => "success", "message" => "Conta criada com sucesso."]);
    }

    private function login(Request $request)
    {
        $data = $request->json()->all() ?: $request->all();

        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            $user = Auth::user();
            return response()->json([
                "status" => "success", 
                "message" => "Login efetuado com sucesso.", 
                "role" => $user->role, 
                "id" => $user->id, 
                "profile_picture" => $user->profile_picture
            ]);
        }

        return response()->json(["status" => "error", "message" => "E-mail ou senha incorretos."]);
    }

    private function googleLogin(Request $request)
    {
        $data = $request->json()->all() ?: $request->all();
        $token = $data['token'] ?? '';

        if (!$token) {
            return response()->json(["status" => "error", "message" => "Autenticação falhou."]);
        }

        try {
            $client = new \Google\Client(['client_id' => '375279591438-7uirtbvgbtsd2c2pjti9kmmhal8r2sr3.apps.googleusercontent.com']);
            $payload = $client->verifyIdToken($token);
            
            if ($payload) {
                @ob_clean();
                $email = $payload['email'];
                $name = $payload['name'] ?? 'Usuário Google';
                $picture = $payload['picture'] ?? null;
                
                $user = User::where('email', $email)->first();
                
                if ($user) {
                    Auth::login($user);
                    return response()->json([
                        "status" => "success", 
                        "message" => "Bem-vindo de volta, " . $user->name . "!", 
                        "role" => $user->role, 
                        "id" => $user->id, 
                        "profile_picture" => $user->profile_picture
                    ]);
                } else {
                    $randomPass = bin2hex(random_bytes(8));
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($randomPass),
                        'role' => 'cliente',
                        'profile_picture' => $picture
                    ]);
                    
                    Auth::login($user);
                    
                    @ob_clean();
                    return response()->json([
                        "status" => "success", 
                        "message" => "Conta criada com sucesso pelo Google!", 
                        "role" => $user->role, 
                        "id" => $user->id, 
                        "profile_picture" => $picture
                    ]);
                }
            } else {
                @ob_clean();
                return response()->json(["status" => "error", "message" => "Token inválido ou expirado."]);
            }
        } catch(\Throwable $e) {
            @ob_clean();
            return response()->json(["status" => "error", "message" => "Erro na verificação do Google: " . $e->getMessage()]);
        }
    }

    private function check(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                "loggedIn" => true,
                "id" => $user->id,
                "name" => $user->name,
                "role" => $user->role,
                "profile_picture" => $user->profile_picture,
                "telefone" => $user->telefone,
                "cpf" => $user->cpf,
                "email" => $user->email
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }
        return response()->json(["loggedIn" => false])->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    private function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return response()->json(["status" => "success"]);
    }

    private function updateProfile(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(["status" => "error", "message" => "Não autorizado."]);
        }

        $user = Auth::user();
        
        // Em forms onde o e-mail ou CPF ficam desativados (disabled), 
        // eles não são enviados no POST. Aqui mesclamos com os dados atuais:
        $data = $request->all();
        $data['name'] = $request->filled('name') ? $request->input('name') : $user->name;
        $data['email'] = $request->filled('email') ? $request->input('email') : $user->email;
        $data['cpf'] = $request->filled('cpf') ? $request->input('cpf') : $user->cpf;
        $data['telefone'] = $request->filled('telefone') ? $request->input('telefone') : $user->telefone;

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'cpf' => 'nullable|string|unique:users,cpf,'.$user->id,
            'telefone' => 'nullable|string|unique:users,telefone,'.$user->id,
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => "error", "message" => $validator->errors()->first()]);
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (isset($data['cpf'])) $user->cpf = $data['cpf'];
        if (isset($data['telefone'])) $user->telefone = $data['telefone'];

        // Handling Profile Picture Upload if it is sent as Base64 like before
        $newPicture = $request->input('profile_picture');
        if (!empty($newPicture) && strpos($newPicture, 'data:image/') === 0) {
            // Limit to 2MB of base64 (~1.5MB of image data)
            if (strlen($newPicture) > 2 * 1024 * 1024) {
                return response()->json(["status" => "error", "message" => "A foto de perfil é muito grande."]);
            }
            list($type, $bdata) = explode(';', $newPicture);
            list(, $bdata) = explode(',', $bdata);
            $bdata = base64_decode($bdata);
            $filename = 'cliente_' . $user->id . '_' . time() . '.jpg';
            // Assuming storing in public/uploads/clientes
            $uploadDir = public_path('uploads/clientes/');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Additional check for permissions in hosted environment
            if (!is_writable($uploadDir)) {
                @chmod($uploadDir, 0777);
            }
            
            file_put_contents($uploadDir . $filename, $bdata);
            $user->profile_picture = 'uploads/clientes/' . $filename;
        } else if ($request->input('delete_photo') == '1') {
            $user->profile_picture = null;
        }

        $user->save();

        return response()->json(["status" => "success", "message" => "Perfil atualizado!"]);
    }

    private function changePassword(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(["status" => "error", "message" => "Não autorizado."]);
        }

        $user = Auth::user();
        $data = $request->all();

        if (!Hash::check($data['current_password'] ?? '', $user->password)) {
            return response()->json(["status" => "error", "message" => "Senha atual incorreta."]);
        }

        if (($data['new_password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            return response()->json(["status" => "error", "message" => "As senhas não conferem."]);
        }

        if (strlen($data['new_password'] ?? '') < 8) {
            return response()->json(["status" => "error", "message" => "A senha deve ter pelo menos 8 caracteres."]);
        }

        $user->password = Hash::make($data['new_password']);
        $user->save();

        return response()->json(["status" => "success", "message" => "Senha alterada com sucesso!"]);
    }

    private function updateSecurity(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(["status" => "error", "message" => "Não autorizado."]);
        }

        $user = Auth::user();
        $data = $request->all();

        // Validating only provided fields
        $rules = [];
        if (!empty($data['email'])) $rules['email'] = 'required|string|email|max:255|unique:users,email,'.$user->id;
        if (!empty($data['cpf'])) $rules['cpf'] = 'nullable|string|unique:users,cpf,'.$user->id;
        if (!empty($data['telefone'])) $rules['telefone'] = 'nullable|string|unique:users,telefone,'.$user->id;

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["status" => "error", "message" => $validator->errors()->first()]);
        }

        if (!empty($data['email'])) $user->email = $data['email'];
        if (!empty($data['cpf'])) $user->cpf = $data['cpf'];
        if (!empty($data['telefone'])) $user->telefone = $data['telefone'];

        if (!empty($data['new_password'])) {
            if (!Hash::check($data['current_password'] ?? '', $user->password)) {
                return response()->json(["status" => "error", "message" => "Senha atual incorreta."]);
            }
            if (strlen($data['new_password']) < 8) {
                return response()->json(["status" => "error", "message" => "A nova senha deve ter pelo menos 8 caracteres."]);
            }
            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        return response()->json(["status" => "success", "message" => "Dados atualizados!"]);
    }
}
