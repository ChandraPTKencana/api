<?php

namespace App\Models\MySql;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Str;
class Syslog extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'st_syslog';
    public $timestamps = false;

    protected $fillable = [
        'created_at',
        'ip_address',
        'created_user',
        'module',
        'module_id',
        'action',
        'note',
    ];
}
