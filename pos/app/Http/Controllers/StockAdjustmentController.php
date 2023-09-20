<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\ItemUnitofMeasureModel;
use App\Models\StockAdjustmentModel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $title = "Stock Adjustment";
        $table_name = 'stock_adjustment';
        $is_form = 1;
        $field=  DB::getSchemaBuilder()->getColumnListing('item_adjustment');
        return view('stockadjustment.stockadjustment',compact('field','title','table_name','is_form'));
    }

    public function store(Request $request)
    {
        $data =$request->all();
        $item=new StockAdjustmentModel();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();

        $field=  DB::getSchemaBuilder()->getColumnListing('item_adjustment');
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
            return DataTables::eloquent(StockAdjustmentModel::query())
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
        $field=  DB::getSchemaBuilder()->getColumnListing('item_adjustment');
        return response()->json([
            'data'=>$field,
        ]);
    }
    public function update(Request $request)
    {
        if($request->code){
            $value=StockAdjustmentModel::where('id',$request->code)->first();
            $field=  DB::getSchemaBuilder()->getColumnListing('item_adjustment');
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
        $item=StockAdjustmentModel::where('id',$request->id)->first();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        $field=  DB::getSchemaBuilder()->getColumnListing('item_adjustment');
        $form=view('layouts.form_in_card',compact('field'))->render();
        return response()->json([
            'status' =>" Scuucess",
            'toas'=>"Data has been saved successfully!",
            'form'=>$form,
        ]);
    }
    public function uploadimage(Request $request){
        $data=StockAdjustmentModel::where('id',$request->code)->first();
         
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
        $data=StockAdjustmentModel::where('id',$request->code)->first();
        $data->picture="";
        $data->save();
        return response()->json([
            'status'=>"Image has been reset",
        ]);
    }
    public function delete(Request $request){
        $data=StockAdjustmentModel::where('id',$request->code)->first();
        $data->delete();
        return response()->json([
            'status'=>"Item has been reset from your Project",
        ]);
    }
    public function remainAmount(Request $request){
       $unit =  $request->item_unit_of_measure? $request->item_unit_of_measure:'Unit';
        $data = StockAdjustmentModel::select('item_no','quantity_to_apply')
        ->where('item_no',$request->code)
        ->where('unit_of_measure_code',$unit)
        ->get();
        $value =  $data->sum(function ($total) {
            return $total->quantity_to_apply;
        });
        return response()->json([
            'status'=>"Item has been reset from your Project",
            'value' =>$value,
        ]);
    }
}
