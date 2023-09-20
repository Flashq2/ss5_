<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentModel extends Model
{
    protected $table = 'item_adjustment';
    protected $primaryKey="id";
    public $incrementing = false; 
    use HasFactory;
}
