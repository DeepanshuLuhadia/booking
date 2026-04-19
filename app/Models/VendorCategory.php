<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorCategory extends Model
{
    protected $table = 'vendor_categories';

    protected $fillable = [
        'name',
        'slug',
        'image_path'
    ];

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }
}
