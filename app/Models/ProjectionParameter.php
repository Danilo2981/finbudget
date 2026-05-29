<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectionParameter extends Model
{
    use HasFactory;

    protected $fillable = [
        'projection_id',
        'target_growth_productive',
        'target_growth_consumer',
        'target_growth_microcredit',
        'target_growth_refinanced',

        'target_growth_restructured',
        'recovery_refinanced',
        'recovery_restructured',
        'sight_deposit_growth_rate',
        'term_deposit_growth_rate',
        'productive_interest_rate',
        'consumer_interest_rate',
        'microcredit_interest_rate',
        'sight_deposit_interest_rate',
        'term_deposit_interest_rate',
        'recovery_productive',
        'recovery_consumer',
        'recovery_microcredit',
        'recovery_written_off',
        'reversal_provisions',
        'tech_investment',
        'image_investment',
        'operating_expenses',
        'auto_credits_per_exec',
        'auto_exec_count',
        'auto_avg_credit_value',
        'auto_months',
    ];

    public function projection()
    {
        return $this->belongsTo(Projection::class);
    }
}
