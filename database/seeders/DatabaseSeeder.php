<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user
        \App\Models\User::create([
            'id' => 1,
            'name' => 'Administrador Geral',
            'email' => 'admin@infinity.com.br',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Default Config
        \App\Models\Config::create([
            'config_key' => 'whatsappNumber',
            'config_value' => '+5598985269184',
        ]);

        // Default Products
        $initialProducts = [
            ['external_id' => 'p1', 'name' => 'Caderno Inteligente Tons Pastéis', 'description' => 'Caderno de discos com folhas reposicionáveis.', 'price' => 89.90, 'category' => 'cadernos', 'image' => 'https://images.unsplash.com/photo-1531346878377-a541e4ab0eaf?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 'rating' => 5.0],
            ['external_id' => 'p2', 'name' => 'Kit Canetas Gel Pastel', 'description' => 'Conjunto com 6 cores incríveis.', 'price' => 34.50, 'category' => 'canetas', 'image' => 'https://images.unsplash.com/photo-1585336261022-680e295ce3fe?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 'rating' => 5.0],
            ['external_id' => 'p3', 'name' => 'Agulha Amigurumi Soft', 'description' => 'Agulha ergonômica para crochê.', 'price' => 15.50, 'category' => 'linhas', 'image' => 'https://images.unsplash.com/photo-1591815302525-756a9bcc3425?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80', 'rating' => 5.0]
        ];

        foreach ($initialProducts as $p) {
            \App\Models\Product::create($p);
        }
    }
}
