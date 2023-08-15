<?php

namespace App\Http\Controllers;

use toastr;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('user.user');
    }
    public function store(Request $request)
    {
     
         if($request->ajax()){
            
            return DataTables::eloquent(UserModel::query())
           
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $actionBtn = " <a href='/user/newrecord?code=$row->id '> </button>
                    <button class='' > Edit
                    </button></a></button>
                    <button class='actiondelete' data-delete=$row->id > Delete
                    </button>" ;
                    return $actionBtn;
                })
                ->rawColumns(['action'])
                ->make(true);
         }
    }
    public function save()
    {
        $field =  DB::getSchemaBuilder()->getColumnListing('users');
        return view('user.user_card',compact('field'));
    }
    public function adduser(Request $request)
    {
        $data=new UserModel();
        $data->name=$request->name;
        $data->email=$request->email ;
        $data->password=bcrypt($request->password) ;
        $data->status=$request->status ;
        $data->salesperson_code=$request->salecode ;
        $data->gender=$request->gender ;
        $data->date_of_birth=$request->dob ;
        $data->phone_no=$request->phone ;
        $data->address=$request->address ;
        $data->permission_code=$request->permission;
        $data->user_role_code=$request->userrole;
        toastr()->success('New user has been to your system');
        $data->save();
        return redirect()->back();
    }
    public function update(Request $request)
    {
        $data=UserModel::find($request->id);
        $data->name=$request->name;
        $data->email=$request->email ;
        $data->password=bcrypt($request->password) ;
        $data->status=$request->status ;
        $data->salesperson_code=$request->salecode ;
        $data->gender=$request->gender ;
        $data->date_of_birth=$request->dob ;
        $data->phone_no=$request->phone ;
        $data->address=$request->address ;
        $data->permission_code=$request->permission;
        $data->user_role_code=$request->userrole;
        $data->update();
        toastr()->success('User Update');
        return redirect()->back();
    }
    public function delete(Request $request,$id)
    {
        $data=UserModel::find($id);
        $data->delete();
        return response()->json([

        ]);
     }
    
}
