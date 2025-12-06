<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantSetting extends Model
{
    protected $fillable = [
        // General Settings
        'restaurant_name',
        'logo',
        'favicon',
        'contact_number',
        'email',
        'address',
        'website',
        'social_links',
        'time_zone',
        'language',
        'opening_time',
        'closing_time',

        // Fee Settings
        'enable_tax',
        'tax_type',
        'tax_name',
        'tax_percentage',
        'service_charge_percentage',
        'delivery_charge',
        'packaging_charge',
    ];

    protected $casts = [
        'enable_tax' => 'boolean',
        'social_links' => 'array',
        'tax_percentage' => 'decimal:2',
        'service_charge_percentage' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'packaging_charge' => 'decimal:2',
    ];
}
