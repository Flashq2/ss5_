<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class lineNoserieModel extends Model
{
    use HasFactory;
    protected $table = "lineseries";
    protected $primaryKey="code";
    public $incrementing = false;   
}
