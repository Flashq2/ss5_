<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemModel extends Model
{
    use HasFactory;
    protected $table='items';
    protected $primaryKey = 'no';
    public $incrementing = false;
    // public $incrementing = false;
   
}
