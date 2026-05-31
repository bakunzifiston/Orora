<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id',
        'document_type',
        'document_name',
        'file_path',
        'issued_date',
        'expiry_date',
        'notes',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
