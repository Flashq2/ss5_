<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitofMeasureModel extends Model
{
    use HasFactory;
    protected $table="unit_of_measures";
    protected $primaryKey="code";
    public $incrementing = false;
}
