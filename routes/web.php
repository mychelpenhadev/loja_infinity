<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;

Route::get('/', [HomeController::class, 'index']);
Route::get('/index.php', function() { return redirect('/'); });
Route::get('/index.php/pagamento.html', function() { return view('pagamento'); });
Route::get('/index.php/produtos.html', function() { return view('produtos'); });
Route::get('/index.php/carrinho.html', function() { return view('carrinho'); });
Route::get('/index.php/login.html', function() { return redirect('/'); });
Route::get('/index.php/perfil.php', function() { return view('perfil'); });

Route::get('/detalhes/{id}', [ProductController::class, 'show']);
Route::get('/detalhes.html', [ProductController::class, 'showLegacy']);

Route::get('/produtos.html', function() { return view('produtos'); });
Route::get('/produtos', function() { return view('produtos'); });

Route::get('/carrinho.html', function() { return view('carrinho'); });
Route::get('/carrinho', function() { return view('carrinho'); });

Route::get('/perfil.php', function() { return view('perfil'); });
Route::get('/perfil', function() { return view('perfil'); });

Route::get('/login.html', function() { return redirect('/'); });
Route::get('/login', function() { return redirect('/'); });

Route::get('/pagamento.html', function() { return view('pagamento'); });
Route::get('/pagamento', function() { return view('pagamento'); });

Route::get('/cadastro.html', function() { return redirect('/'); });
Route::get('/cadastro', function() { return redirect('/'); });

Route::get('/admin.php', function() { return view('admin'); });
Route::get('/admin', function() { return view('admin'); });

Route::get('/admin_config.php', function() { return view('admin_config'); });
Route::get('/admin_config', function() { return view('admin_config'); });

Route::get('/admin_pedidos.php', function() { return view('admin_pedidos'); });
Route::get('/admin_pedidos', function() { return view('admin_pedidos'); });

Route::any('/api/products', [ProductController::class, 'apiHandler']);
Route::any('/api/products.php', [ProductController::class, 'apiHandler']);

Route::any('/api/auth', [App\Http\Controllers\AuthController::class, 'apiHandler']);
Route::any('/api/auth.php', [App\Http\Controllers\AuthController::class, 'apiHandler']);

Route::any('/api/orders', [App\Http\Controllers\OrderController::class, 'apiHandler']);
Route::any('/api/orders.php', [App\Http\Controllers\OrderController::class, 'apiHandler']);

Route::any('/api/config', [App\Http\Controllers\ConfigController::class, 'apiHandler']);
Route::any('/api/config.php', [App\Http\Controllers\ConfigController::class, 'apiHandler']);

Route::any('/api/backup', [App\Http\Controllers\BackupController::class, 'apiBackup']);
Route::any('/api/backup.php', [App\Http\Controllers\BackupController::class, 'apiBackup']);

Route::any('/api/restore', [App\Http\Controllers\BackupController::class, 'apiRestore']);
Route::any('/api/restore.php', [App\Http\Controllers\BackupController::class, 'apiRestore']);

Route::post('/api/upload-chunk', [App\Http\Controllers\BackupController::class, 'uploadChunk']);
Route::post('/api/upload-chunk.php', [App\Http\Controllers\BackupController::class, 'uploadChunk']);

Route::post('/api/restore-final', [App\Http\Controllers\BackupController::class, 'restoreFinal']);
Route::post('/api/restore-final.php', [App\Http\Controllers\BackupController::class, 'restoreFinal']);

