<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class eCommereceController extends Controller
{
    public function index()
    {
        return view('ecommerce.ecommerce');
    }
}
