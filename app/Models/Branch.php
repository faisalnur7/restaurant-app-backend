<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'restaurant_setting_id',
        'branch_name',
        'branch_code',
        'contact_number',
        'email',
        'status',
        'address',
    ];

}
