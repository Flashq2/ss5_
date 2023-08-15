<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemStockModel extends Model
{
    protected $table='item_adjustment';
    public $incrementing = false;
    protected $primaryKey="id"; 
    use HasFactory;
}
