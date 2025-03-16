<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
  use HasApiTokens, HasFactory;

  protected $table = 'admins';
  protected $fillable = [
    'first_name',
    'last_name',
    'username',
    'password',
  ];

  protected $hidden = [
    'password',
  ];
}
