<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpReset extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'otp_code', 'expires_at'];
}
