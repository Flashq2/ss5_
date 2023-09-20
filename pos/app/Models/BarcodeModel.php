<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeModel extends Model
{
    use HasFactory;
    protected $table = "barcode";
    public $incrementing = false;
    protected $primaryKey = 'id';
}
