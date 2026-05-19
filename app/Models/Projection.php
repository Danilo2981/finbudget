<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'user_id',
    ];

    public function parameters()
    {
        return $this->hasOne(ProjectionParameter::class);
    }
}
