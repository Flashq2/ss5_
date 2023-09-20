<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ItemGroupModel;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ItemGroupConttroller extends Controller
{
    public function index(){
        $title = 'Item Group';
        $table_name = 'Item_group';
        $field=  DB::getSchemaBuilder()->getColumnListing('item_groups');
        return view('items_group.item_group',compact('field','title','table_name'));
    }
    public function show(Request $request){
        if($request->ajax()){
            return DataTables::eloquent(ItemGroupModel::query())
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
        $field=  DB::getSchemaBuilder()->getColumnListing('item_groups');
        return response()->json([
            'data'=>$field,
        ]);
    }
    public function  showadd(Request $request)
    {
    
        if($request->ajax()) {
        $tablename="Item Group";
          $data=  DB::getSchemaBuilder()->getColumnListing('item_groups');
            return view('layouts.modal',compact('data','tablename'))->render();
    }
    }
    public function save(Request $request){
        $data =$request->all();
        $item=new ItemGroupModel();
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
            $tablename="Item Group";
            $code=ItemGroupModel::where('code',($request->code))->get();
            $data=  DB::getSchemaBuilder()->getColumnListing('item_groups');
            return view('layouts.modal',compact('code','data','tablename'))->render();
    }
      
    }
    public function editValue(Request $request){
        $data =$request->all();
        $item=ItemGroupModel::where('code',$request->code)->first();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        return response()->json([
            'status'=>'Success'
        ]);
    }
    public function destroy(Request $request){
        $item=ItemGroupModel::where('code',$request->code_to_delete    
        )->first();
        $item->delete();
        return response()->json([
            'status'=>"Item Group Code has been delete sucessfully",
        ]);
    }
}
