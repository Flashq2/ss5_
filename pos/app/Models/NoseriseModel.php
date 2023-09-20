<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoseriseModel extends Model
{
    protected $table = 'lineseries';
    protected $primaryKey="id";
    public $incrementing = false; 
    use HasFactory;
}
