<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoveryProjection extends Model
{
    use HasFactory;

    protected $fillable = [
        'tabla',
        'row_index',
        'concept',
        'mes',
        'valor',
    ];
}
