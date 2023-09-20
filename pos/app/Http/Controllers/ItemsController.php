<?php

namespace App\Http\Controllers;

use Facebook\Facebook;
use App\Models\ItemModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class ItemsController extends Controller
{

    public function index()
    {
        $title = 'Item Setup';
        $table_name = 'item';
        $is_form = '1';
        $show_image = '1';
        $field=  DB::getSchemaBuilder()->getColumnListing('items');
        return view('items.item',compact('field','title','table_name','is_form','show_image'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'no' => 'required|unique:items',
            ]

        );
        if ($validator->fails()) {
            return response()->json([
                'status' => $validator->errors()->toArray(),
                'message'=>'fails',
            ]);
        }
        $data =$request->all();
        $item=new ItemModel();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        $field=  DB::getSchemaBuilder()->getColumnListing('items');
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
            return DataTables::eloquent(ItemModel::query())
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actionBtn = " </button>
                <button class='edit' data-edit=$row->no> Edit
                </button>
                     
                </button>
                <button class='actiondelete' data-delete=$row->no > Delete
                </button>" ;
                return $actionBtn;
            })
            ->addColumn('product_brand_logo', function ($product_brand) {
                if($product_brand->picture==null){
                    $url=asset("/img/no_profile.jpg");
                }
                else{
                      $url=asset("/item/$product_brand->picture"); 
                }
              
                return '<img src='.$url.' data-src='.$url.' data-code='.$product_brand->no.'   width="40" class="img-rounded" align="center"; border-radius: 10px !important; />'; 
         })
            ->rawColumns(['action','product_brand_logo']) 
            ->make(true);
        }
    }
    public function getfield(Request $request)
    {
        $field=  DB::getSchemaBuilder()->getColumnListing('items');
        return response()->json([
            'data'=>$field,
        ]);
    }
    public function update(Request $request)
    {
        if($request->code){
            $value=ItemModel::where('no',$request->code)->first();
            $field=  DB::getSchemaBuilder()->getColumnListing('items');
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
        $item=ItemModel::where('no',$request->no)->first();
        foreach($data as $key=>$d){
            $item->$key=$d;
        }
        $item->save();
        $field=  DB::getSchemaBuilder()->getColumnListing('items');
        $form=view('layouts.form_in_card',compact('field'))->render();
        return response()->json([
            'status' =>" Scuucess",
            'toas'=>"Data has been saved successfully!",
            'form'=>$form,
        ]);
    }
    public function uploadimage(Request $request){
        $data=ItemModel::where('no',$request->code)->first();
         
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
        $data=ItemModel::where('no',$request->code)->first();
        $data->picture="";
        $data->save();
        return response()->json([
            'status'=>"Image has been reset",
        ]);
    }
    public function delete(Request $request){
        $data=ItemModel::where('no',$request->code)->first();
        $data->delete();
        return response()->json([
            'status'=>"Item has been reset from your Project",
        ]);
    }
}
