<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserroleModel;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UserroleController extends Controller
{
    public function index()
    {
        return view('role.user_role');
    }
    public function save(Request $request)
    {
        $data=new UserroleModel();
        $data->code=$request->code;
        $data->description=$request->description;
        $data->description_2=$request->description2;
        $data->inactived=$request->inactived;
        $data->save();
        return response()->json([
            'success'=>'New User Role has been add your project',
        ]);
    }
    public function  showadd(Request $request)
    {
        if($request->ajax()) {
            $tablename="User Role";
            $data=  DB::getSchemaBuilder()->getColumnListing('user_roles');
            return view('layouts.modal',compact('data','tablename'))->render();
    }
}
    public function edituserrole(Request $request)
    {
        if($request->ajax()) {
            $tablename="Permission";
            $code=UserroleModel::where('code',($request->code))->get();
            $data=  DB::getSchemaBuilder()->getColumnListing('user_roles');
            return view('layouts.modal',compact('code','data','tablename'))->render();
    }
    }
    public function clickedituserrole(Request $request)
    {
        UserroleModel::where('code',$request->code)
        ->update(['description'=>$request->description,
        'description'=>$request->description,
        'description_2'=>$request->description2,
        'inactived'=>$request->inactived

    ]);
        return response()->json([
            'success'=>'Permission has been update',
        ]);

    }
    public function user_role_datatable(Request $request)
    {
        if($request->ajax()){
            return DataTables::eloquent(UserroleModel::query())
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actionBtn = " </button>
                <button class='edit' data-edit=$row->code > Edit
                </button></button>
                <button class='actiondelete' data-delete=$row->code > Delete
                </button>" ;
                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }
    
    public function deleteuserrole(Request $request)
    {
         $data=UserroleModel::where('code',$request->code_to_delete)->delete();
         return response()->json([
            'status'=>'User Role code '.$request->code_to_delete.' has been delete from your Project'
         ]);
    }

}
