<?php

namespace App\Http\Controllers;

use App\Models\UserSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UserSetupController extends Controller
{
    public function index(){
        $field =  DB::getSchemaBuilder()->getColumnListing('user_setup');
        return view('user_setup.user_setup',compact('field'));
    }
    public function getField(){
        $data =  DB::getSchemaBuilder()->getColumnListing('user_setup');
        return response()->json([
            'status' => "success",
            'data' => $data,
        ]);
        // return view('user_setup.user_setup',compact('data'));
    }
    public function getcolumn(Request $request)
    {
     
         if($request->ajax()){
            
            return DataTables::eloquent(UserSetup::query())
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = " <a href='/user_setup/user_setup_card?code=$row->id '> </button>
                    <button class='' > Edit
                    </button></a></button>
                    <button class='actiondelete' data-delete=$row->id > Delete
                    </button>" ;
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
         }
    }
    public function indexCard(Request $request){
        return view('user_setup.user_setup_card');
    }
}
