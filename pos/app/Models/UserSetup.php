<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetup extends Model
{
    protected $table='user_setup';
    public $incrementing = false;
    protected $primaryKey="id";
    use HasFactory;
}
