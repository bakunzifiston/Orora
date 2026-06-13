<?php

namespace App\Models\Central;

class ContactMessage extends CentralModel
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'inquiry_type',
        'message',
        'status',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
        ];
    }
}
