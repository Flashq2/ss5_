<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserroleModel extends Model
{
    protected $table='user_roles';
    public $incrementing = false;
    use HasFactory;
}
