<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function index(){
        $title = "Warehouse";
        $table_name = 'warehouse';
        $field=  DB::getSchemaBuilder()->getColumnListing('warehouse');
        return view('warehouse.warehouse',compact('field','title','table_name'));
    }
    public function show(Request $request){
        if($request->ajax()){
            return DataTables::eloquent(
                Warehouse::query())
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
        $field=  DB::getSchemaBuilder()->getColumnListing('warehouse');
        return response()->json([
            'data'=>$field,
        ]);
    }
    public function  showadd(Request $request)
    {
    
        if($request->ajax()) {
        $tablename="Warehouse";
          $data=  DB::getSchemaBuilder()->getColumnListing('warehouse');
            return view('layouts.modal',compact('data','tablename'))->render();
    }
    }
    public function save(Request $request){
        $data =$request->all();
        $item=new 
        Warehouse();
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
            $tablename="Warehouse";
            $code=
            Warehouse::where('id',($request->code))->get();
            $data=  DB::getSchemaBuilder()->getColumnListing('warehouse');
            return view('layouts.modal',compact('code','data','tablename'))->render();
    }
      
    }
    public function editValue(Request $request){
        $data =$request->all();
        
        $item=
        Warehouse::where('id',$request->id)->first();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        return response()->json([
            'status'=>'Success'
        ]);
    }
    public function destroy(Request $request){
        $item=
        Warehouse::where('id',$request->code_to_delete    
        )->first();
        $item->delete();
        return response()->json([
            'status'=>"Warehouse Code has been delete sucessfully",
        ]);
    }
}
