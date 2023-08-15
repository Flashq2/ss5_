<?php

namespace App\Http\Controllers;

use App\Models\PermissionModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Yajra\DataTables\Facades\DataTables;
 
use function PHPUnit\Framework\returnSelf;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         return view('permission.permission');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if($request->ajax()){
            return DataTables::eloquent(PermissionModel::query())
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $actionBtn = " </button>
                <button class='edit' data-edit=$row->code> Edit
                </button></button>
                <button class='actiondelete' data-delete=$row->code > Delete
                </button>" ;
                return $actionBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request)
    {
        $data=new PermissionModel();
        $data->code=$request->code;
        $data->description=$request->description;
        $data->description_2=$request->description2;
        $data->inactived=$request->inactived;
        $data->save();
        return response()->json([
            'success'=>'User has been add your project',
        ]);
    }

    public function show(Request $request)
    {
        
        $data=new PermissionModel();
        $data->code=$request->code;
        $data->description=$request->description;
        $data->description_2=$request->description2;
        $data->inactived=$request->inactived;
        $data->save();
        return response()->json([
            'success'=>'User has been add your project',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        PermissionModel::where('code',$request->code)
        ->update(['description'=>$request->description,
        'description'=>$request->description,
        'description_2'=>$request->description2,
        'inactived'=>$request->inactived

    ]);
        return response()->json([
            'success'=>'Permission has been update',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $data=PermissionModel::where('code',$request->code_to_delete);
        $data->delete();
        return response()->json([
            'success'=>'Any Messaage'
        ]);
        
    }
    public function showedit(Request $request)
    {
        if($request->ajax()) {
            $tablename="Permission";
            $code=PermissionModel::where('code',($request->code))->get();
            $data=  DB::getSchemaBuilder()->getColumnListing('permissions');
            return view('layouts.modal',compact('code','data','tablename'))->render();
    }
       
         
    }
    public function  showadd(Request $request)
    {
    
        if($request->ajax()) {
        $tablename="Permission";
          $data=  DB::getSchemaBuilder()->getColumnListing('permissions');
            return view('layouts.modal',compact('data','tablename'))->render();
    }
    }
}
