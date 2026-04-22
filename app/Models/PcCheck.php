<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PcCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'check_name', 'pc_name', 'pc_ip', 'check_file_name', 'total_software',
        'legitimate_count', 'illegitimate_count', 'version_mismatch_count', 'results'
    ];

    protected $casts = [
        'results' => 'array'
    ];

    public function items()
    {
        return $this->hasMany(PcCheckItem::class);
    }
}
