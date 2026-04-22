<?php
// app/Models/ReportItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id', 'program_name', 'version', 'vendor', 'devices_count',
        'normalized_name', 'version_normalized', 'status',
        'matched_allowed_id', 'match_type', 'match_details'
    ];

    protected $casts = [
        'match_details' => 'array'
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function matchedAllowed()
    {
        return $this->belongsTo(AllowedSoftware::class, 'matched_allowed_id');
    }
}
