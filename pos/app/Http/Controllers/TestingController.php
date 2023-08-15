<?php

namespace App\Http\Controllers;

use App\Models\TestingTable;
use Illuminate\Http\Request;

class TestingController extends Controller
{
    public function index(Request $request)
    {
        $items = TestingTable::paginate(5);
        if($request->ajax()){
            $data = TestingTable::paginate(5);
            return view('hello', compact('items'));
        }
        return view('hello',compact('items'));
    }
    public function ajaxpagination(Request $request)
    {
        if($request->ajax()){
            $data = TestingTable::paginate(5);
            return view('hello', compact('data'))->render();
        }
    }
}
