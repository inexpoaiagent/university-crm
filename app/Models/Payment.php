<?php

namespace App\Models;

use App\Models\Concerns\TenantScoped;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use TenantScoped;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'type',
        'currency',
        'amount',
        'commission_rate',
        'commission_amount',
        'status',
        'paid_at',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
