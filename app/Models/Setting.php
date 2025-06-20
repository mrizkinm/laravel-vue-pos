<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'email',
        'phone',
        'address',
        'tax_number',
        'currency',
        'tax_rate',
        'low_stock_threshold',
        'default_payment_term',
        'receipt_footer',
        'show_tax_number_on_receipt',
        'logo'
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'low_stock_threshold' => 'integer',
        'default_payment_term' => 'integer',
        'show_tax_number_on_receipt' => 'boolean'
    ];
}
