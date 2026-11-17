<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['user_id', 'nama', 'nik', 'telp', 'alamat', 'ktp_path', 'kk_path'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function testDrives(): HasMany
    {
        return $this->hasMany(TestDrive::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(ServiceBooking::class);
    }

    public function sparepartOrders(): HasMany
    {
        return $this->hasMany(SparepartOrder::class);
    }
}
