<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ItemUnitofMeasureModel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class ItemUnitofMeasureController extends Controller
{
    public function index()
    {
        $field=  DB::getSchemaBuilder()->getColumnListing('item_unit_of_measures');
        return view('itemUnitOfMeasure.itemUnitOfMeasure',compact('field'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'id' => 'required|unique:item_unit_of_measures',
            ]

        );
        if ($validator->fails()) {
            return response()->json([
                'status' => $validator->errors()->toArray(),
                'message'=>'fails',
            ]);
        }
        $data =$request->all();
        $item=new ItemUnitofMeasureModel();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        $field=  DB::getSchemaBuilder()->getColumnListing('item_unit_of_measures');
        $form=view('layouts.form_in_card',compact('field'))->render();
        return response()->json([
            'status' =>" Scuucess",
            'toas'=>"Data has been saved successfully!",
            'form'=>$form,
        ]);
    }
 
    public function show(Request $request)
    {
        if($request->ajax()){
            return DataTables::eloquent(ItemUnitofMeasureModel::query())
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actionBtn = " </button>
                <button class='edit' data-edit=$row->id> Edit
                </button>
                     
                </button>
                <button class='actiondelete' data-delete=$row->id > Delete
                </button>" ;
                return $actionBtn;
            })
           
            ->rawColumns(['action']) 
            ->make(true);
        }
    }
    public function getfield(Request $request)
    {
        $field=  DB::getSchemaBuilder()->getColumnListing('item_unit_of_measures');
        return response()->json([
            'data'=>$field,
        ]);
    }
    public function update(Request $request)
    {
        if($request->code){
            $value=ItemUnitofMeasureModel::where('id',$request->code)->first();
            $field=  DB::getSchemaBuilder()->getColumnListing('item_unit_of_measures');
            $form=view('layouts.form_in_card',compact('field','value'))->render();
         return response()->json([
            'status'=>"success",
            'form'=>$form,
        ]);   
        }
        else{
            return response()->json([
                'status'=>"Something went wrong",
            ]);  
        }

        
    }
    public function submit_edit(Request $request)
    {
        
        $data =$request->all();
        $item=ItemUnitofMeasureModel::where('id',$request->id)->first();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        $field=  DB::getSchemaBuilder()->getColumnListing('item_unit_of_measures');
        $form=view('layouts.form_in_card',compact('field'))->render();
        return response()->json([
            'status' =>" Scuucess",
            'toas'=>"Data has been saved successfully!",
            'form'=>$form,
        ]);
    }
    public function uploadimage(Request $request){
        $data=ItemUnitofMeasureModel::where('id',$request->code)->first();
         
        $file=$request->file('file');
        $extension=$file->getClientOriginalExtension();
        $filename=time().'.'.$extension;
        $file->move(public_path('item'),$filename);
        $data->picture=$filename;
        $data->save();
        return response()->json([
            'status'=>"Image has been upload sucessfully",
        ]);
        
        
    }
    public function deleteimage(Request $request){
        $data=ItemUnitofMeasureModel::where('id',$request->code)->first();
        $data->picture="";
        $data->save();
        return response()->json([
            'status'=>"Image has been reset",
        ]);
    }
    public function delete(Request $request){
        $data=ItemUnitofMeasureModel::where('id',$request->code)->first();
        $data->delete();
        return response()->json([
            'status'=>"Item has been reset from your Project",
        ]);
    }
}
