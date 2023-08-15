<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $table="warehouse";
    protected $primaryKey="id";
    public $incrementing = false;
    use HasFactory;
}
