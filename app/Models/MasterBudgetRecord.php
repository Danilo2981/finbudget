<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterBudgetRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'nivel',
        'tipo',
        'codigo',
        'cuenta',
        'mes',
        'año',
        'saldo',
    ];
}
