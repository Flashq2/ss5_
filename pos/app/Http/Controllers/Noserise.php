<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NoseriseModel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class Noserise extends Controller
{
    public function index(){
        $title = "No Serise";
        $table_name = 'no_serise';
        $is_form = 1;
        $field=  DB::getSchemaBuilder()->getColumnListing('lineseries');
        return view('no_serise.no_serise',compact('field','title','table_name'));
    }
    public function show(Request $request){
        if($request->ajax()){
            return DataTables::eloquent(NoseriseModel::query())
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actionBtn = " </button>
                <button class='edit' data-edit=$row->id> Edit
                </button>
                     
                </button>
                <button class='actiondelete' data-delete=$row->id> Delete
                </button>" ;
                return $actionBtn;
            })
            ->rawColumns(['action']) 
            ->make(true);
        }
    }
    public function getfield(){
        $field=  DB::getSchemaBuilder()->getColumnListing('lineseries');
        return response()->json([
            'data'=>$field,
        ]);
    }
    public function  showadd(Request $request)
    {
    
        if($request->ajax()) {
            $tablename="Line Series";
            $data=  DB::getSchemaBuilder()->getColumnListing('lineseries');
            return view('layouts.modal',compact('data','tablename'))->render();
    }
    }
    public function save(Request $request){
        $data =$request->all();
        $item=new NoseriseModel();
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
            $code=NoseriseModel::where('id',($request->code))->get();
            $data=  DB::getSchemaBuilder()->getColumnListing('lineseries');
            return view('layouts.modal',compact('code','data','tablename'))->render();
    }
      
    }
    public function editValue(Request $request){
        $data =$request->all();
        $item=NoseriseModel::where('id',$request->code)->first();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        return response()->json([
            'status'=>'Success'
        ]);
    }
    public function destroy(Request $request){
        $item=NoseriseModel::where('id',$request->code_to_delete)->first();
        $item->delete();
        return response()->json([
            'status'=>"Item Category Code has been delete sucessfully",
        ]);
    }
}
