<?php
// app/Models/Report.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name', 'total_entries', 'legitimate_count',
        'illegitimate_count', 'version_mismatch_count', 'summary', 'filters_used'
    ];

    protected $casts = [
        'summary' => 'array',
        'filters_used' => 'array'
    ];

    public function items()
    {
        return $this->hasMany(ReportItem::class);
    }
}
