<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LookupValue extends Model
{
    protected $fillable = ['type', 'code', 'description', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
