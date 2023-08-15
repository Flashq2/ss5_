<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemGroupModel extends Model
{
    use HasFactory;
    protected $table="item_groups";
    protected $primaryKey ='code';
    public $incrementing = false;

}
