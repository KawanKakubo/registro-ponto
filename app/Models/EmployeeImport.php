<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeImport extends Model
{
    protected $fillable = [
        'file_path',
        'original_filename',
        'file_size',
        'status',
        'total_rows',
        'success_count',
        'updated_count',
        'error_count',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];
}
