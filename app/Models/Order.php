<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'external_id',
        'user_id',
        'user_name',
        'total',
        'status',
        'items_json',
        'method',
    ];

    protected function casts(): array
    {
        return [
            'items_json' => 'array',
        ];
    }
}
