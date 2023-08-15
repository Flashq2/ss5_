<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnitofMeasureModel;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class UnitOfMeausureController extends Controller
{
    public function index(){
        $field=  DB::getSchemaBuilder()->getColumnListing('item_groups');
        return view('unit_of_measure.unit_of_measure',compact('field'));
    }
    public function show(Request $request){
        if($request->ajax()){
            return DataTables::eloquent(UnitofMeasureModel::query())
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
        $field=  DB::getSchemaBuilder()->getColumnListing('unit_of_measures');
        return response()->json([
            'data'=>$field,
        ]);
    }
    public function  showadd(Request $request)
    {
    
        if($request->ajax()) {
        $tablename="Unit of Measure";
          $data=  DB::getSchemaBuilder()->getColumnListing('unit_of_measures');
            return view('layouts.modal',compact('data','tablename'))->render();
    }
    }
    public function save(Request $request){
        $data =$request->all();
        $item=new UnitofMeasureModel();
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
            $tablename="Unit of Measure";
            $code=UnitofMeasureModel::where('code',($request->code))->get();
            $data=  DB::getSchemaBuilder()->getColumnListing('unit_of_measures');
            return view('layouts.modal',compact('code','data','tablename'))->render();
    }
      
    }
    public function editValue(Request $request){
        $data =$request->all();
        $item=UnitofMeasureModel::where('code',$request->code)->first();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        return response()->json([
            'status'=>'Success'
        ]);
    }
    public function destroy(Request $request){
        $item=UnitofMeasureModel::where('code',$request->code_to_delete    
        )->first();
        $item->delete();
        return response()->json([
            'status'=>"Unit of Measure Code has been delete sucessfully",
        ]);
    }
}
