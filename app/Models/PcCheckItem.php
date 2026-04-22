<?php
// app/Models/PcCheckItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PcCheckItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pc_check_id', 'program_name', 'version', 'vendor',
        'normalized_name', 'version_normalized', 'status',
        'matched_allowed_id', 'match_details'
    ];

    public function pcCheck()
    {
        return $this->belongsTo(PcCheck::class);
    }

    public function matchedAllowed()
    {
        return $this->belongsTo(AllowedSoftware::class, 'matched_allowed_id');
    }
}
