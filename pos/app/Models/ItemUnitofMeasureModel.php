<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemUnitofMeasureModel extends Model
{
    use HasFactory;
    protected $table="item_unit_of_measures";
    protected $primaryKey="id";
    public $incrementing = false;
    
    protected static function KeyName() {
        return (new static)->getKeyName();
    }
    
}
