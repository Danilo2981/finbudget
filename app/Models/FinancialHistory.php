<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nivel',
        'tipo',
        'codigo',
        'cuenta',
        'mes',
        'año',
        'fecha',
        'saldo',
    ];
}
