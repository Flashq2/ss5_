<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function index(){
        return view('stockadjustment.stockadjustment');
    }
}
