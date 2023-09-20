<?php

namespace App\Http\Controllers;

use App\Models\PermissionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index(){
        $title = 'Permission';
        $table_name = 'permission';
        $field=  DB::getSchemaBuilder()->getColumnListing('permissions');
        return view('permission.permission',compact('field','title','table_name'));
    }
    public function show(Request $request){
        if($request->ajax()){
            return DataTables::eloquent(PermissionModel::query())
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actionBtn = " </button>
                <button class='edit' data-edit=$row->code> Edit
                </button>
                     
                </button>
                <button class='actiondelete' data-delete=$row->code> Delete
                </button>" ;
                return $actionBtn;
            })
            ->rawColumns(['action']) 
            ->make(true);
        }
    }
    public function getfield(){
        $field=  DB::getSchemaBuilder()->getColumnListing('permissions');
        return response()->json([
            'data'=>$field,
        ]);
    }
    public function  showadd(Request $request)
    {
    
        if($request->ajax()) {
        $tablename="Item Category";
          $data=  DB::getSchemaBuilder()->getColumnListing('permissions');
            return view('layouts.modal',compact('data','tablename'))->render();
    }
    }
    public function save(Request $request){
        $data =$request->all();
        $item=new PermissionModel();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        return response()->json([
            'status'=>'Success'
        ]);
    }
    public function getValueEdit(Request $request){
        if($request->ajax()) {
            $tablename="Item Category";
            $code=PermissionModel::where('code',($request->code))->get();
            $data=  DB::getSchemaBuilder()->getColumnListing('permissions');
            return view('layouts.modal',compact('code','data','tablename'))->render();
    }
      
    }
    public function editValue(Request $request){
        $data =$request->all();
        $item=PermissionModel::where('code',$request->code)->first();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        return response()->json([
            'status'=>'Success'
        ]);
    }
    public function destroy(Request $request){
        $item=PermissionModel::where('code',$request->code_to_delete)->first();
        $item->delete();
        return response()->json([
            'status'=>"Item Category Code has been delete sucessfully",
        ]);
    }
}
