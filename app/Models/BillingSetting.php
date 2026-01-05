<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'address',
        'email',
        'phone',
        'vat_number',
        'legal_mentions',
        'footer_text',
        'logo_path',
    ];
}
