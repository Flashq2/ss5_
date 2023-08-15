<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\eCommereceController;
use App\Http\Controllers\FormatDatetimes;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\ItemGroupConttroller;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\ItemUnitofMeasureController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\UnitOfMeausureController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserroleController;
use App\Http\Controllers\WarehouseController;
use App\Models\CustomerModel;
use App\Models\PermissionModel;
use App\Models\SystemModel;
use App\Models\UserroleModel;
use Illuminate\Support\Facades\Route;
Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
Route::group(['middleware' => 'admin'], function () {
Route::get('/home',[TestingController::class,'index']);
Route::group(['prefix' => 'user'], function () {
    Route::resource('/user',UserController::class);
    Route::get('/newrecord',[UserController::class,'save']);
    Route::get('userlist',[UserController::class,'store'])->name('user.list');
    Route::post('adduser',[UserController::class,'adduser']);
    Route::post('updateuser',[UserController::class,'update']);
    Route::post('deleteuser/{id}',[UserController::class,'delete']);

});




});

Route::group(['prefix' => 'customer'], function () {
    Route::resource('/customer',UserController::class);
    
    


});
Route::group(['prefix' => 'pos'], function () {
    Route::resource('/pos',PosController::class);
    Route::get('/additem',[PosController::class,'addItem']);

});
Route::group(['prefix' => 'permission'], function () {
    Route::resource('/permission',PermissionController::class);
    Route::get('/show',[PermissionController::class,'showedit']);
    Route::get('/showmodal',[PermissionController::class,'showadd']);
    Route::get('/editpermission',[PermissionController::class,'edit']);
    Route::post('addnewpermission',[PermissionController::class,'save']);
    Route::get('/deletepermission',[PermissionController::class,'destroy']);
    Route::get('/permissionlist',[PermissionController::class,'create'])->name('permission.list');

});
Route::group(['prefix' => 'ecommerce'], function () {
    Route::resource('/ecommerce',eCommereceController::class);
     

});
Route::group(['prefix' => 'userrole'], function () {
    Route::resource('/userrole',UserroleController::class);
    Route::get('/userrolelist',[UserroleController::class,'user_role_datatable'])->name('userrole.list');
    Route::post('/addnewuserrole',[UserroleController::class,'save']);
    Route::get('/edituserrole',[UserroleController::class,'edituserrole']);
    Route::get('/clickedituserrole',[UserroleController::class,'clickedituserrole']);
    Route::get('/deleteuserrole',[UserroleController::class,'deleteuserrole']);
    Route::get('/showmodaluserrole',[UserroleController::class,'showadd']);
     

});
Route::group(['prefix' => 'customer'], function () {
    Route::resource('/customer',CustomerController::class);
    Route::get('/addnewcustomer',[CustomerController::class,'store']);
    Route::post('/submitform',[CustomerController::class,'create']);
    Route::get('/customerlist',[CustomerController::class,'show'])->name('customer.list');
    Route::post('/deletecustomer/{id}',[CustomerController::class,'destroy']);
    Route::get('/getfiledlist',[CustomerController::class,'getfield']);
    Route::post('/updatecusotmer',[CustomerController::class,'updaterecord']);
     

});
Route::group(['prefix' => 'items'], function () {
    Route::resource('/items',ItemsController::class);
    Route::post('/item/save',[ItemsController::class,'store']);
    Route::get('/item',[ItemsController::class,'show'])->name('item.list');
    Route::get('/getfiledlistitme',[ItemsController::class,'getfield']);
    Route::get('/item/edit',[ItemsController::class,'update']);
    Route::post('/item/submmit_edit',[ItemsController::class,'submit_edit']);
    Route::post('item/uploadImage',[ItemsController::class,'uploadimage']);
    Route::post('item/deleteImage',[ItemsController::class,'deleteimage']);
    Route::get('item/delete',[ItemsController::class,'delete']);
     

});

Route::group(['prefix' => 'webservice'], function () {
    Route::resource('/webservice',FormatDatetimes::class);
    Route::get('/returnvalue/{field}/{value}',[FormatDatetimes::class,'getSpecialConditionValue']);
});

Route::group(['prefix' => 'item_group'], function () {
    Route::get('/getFiledListItem_group',[ItemGroupConttroller::class,'getfield']);
    Route::resource('/item_group',ItemGroupConttroller::class);
    Route::get('/Item_groupList',[ItemGroupConttroller::class,'show'])->name('Item_group.list');
    Route::get('/showmodal_Item_group',[ItemGroupConttroller::class,'showadd']);
    Route::post('/addNewItem_group',[ItemGroupConttroller::class,'save']);
    Route::post('/show_to_edit_Item_group',[ItemGroupConttroller::class,'getValueEdit']);
    Route::post('/saveEdit_Item_group',[ItemGroupConttroller::class,'editValue']);
    Route::post('/deleteItem_group',[ItemGroupConttroller::class,'destroy']);

});
Route::group(['prefix' => 'item_category'], function () {
    Route::get('/getFiledListItem_category',[ItemCategoryController::class,'getfield']);
    Route::resource('/item_category',ItemCategoryController::class);
    Route::get('/Item_categoryList',[ItemCategoryController::class,'show'])->name('Item_category.list');
    Route::get('/showmodal_Item_category',[ItemCategoryController::class,'showadd']);
    Route::post('/addNewItem_category',[ItemCategoryController::class,'save']);
    Route::post('/show_to_edit_Item_category',[ItemCategoryController::class,'getValueEdit']);
    Route::post('/saveEdit_Item_category',[ItemCategoryController::class,'editValue']);
    Route::post('/deleteItem_category',[ItemCategoryController::class,'destroy']);

});
Route::group(['prefix' => 'unit_of_measure'], function () {
    Route::get('/getFiledListUnit_of_measure',[UnitOfMeausureController::class,'getfield']);
    Route::resource('/unit_of_measure',UnitOfMeausureController::class);
    Route::get('/Unit_of_measureList',[UnitOfMeausureController::class,'show'])->name('Unit_of_measure.list');
    Route::get('/showmodal_Unit_of_measure',[UnitOfMeausureController::class,'showadd']);
    Route::post('/addNewUnit_of_measure',[UnitOfMeausureController::class,'save']);
    Route::post('/show_to_edit_Unit_of_measure',[UnitOfMeausureController::class,'getValueEdit']);
    Route::post('/saveEdit_Unit_of_measure',[UnitOfMeausureController::class,'editValue']);
    Route::post('/deleteUnit_of_measure',[UnitOfMeausureController::class,'destroy']);

});


Route::group(['prefix' => 'item_unit_of_measures'], function () {
    Route::resource('unitOfMeasure',ItemUnitofMeasureController::class);
    Route::post('/item_unit_of_measures/save',[ItemUnitofMeasureController::class,'store']);
    Route::get('/item_unit_of_measuresl',[ItemUnitofMeasureController::class,'show'])->name('itemUnitOfMeasures.list');
    Route::get('/getfiledlistitem_unit_of_measures',[ItemUnitofMeasureController::class,'getfield']);
    Route::get('/item_unit_of_measures/edit',[ItemUnitofMeasureController::class,'update']);
    Route::post('/item_unit_of_measures/submmit_edit',[ItemUnitofMeasureController::class,'submit_edit']);
    Route::post('item_unit_of_measures/uploadImage',[ItemUnitofMeasureController::class,'uploadimage']);
    Route::post('item_unit_of_measures/deleteImage',[ItemUnitofMeasureController::class,'deleteimage']);
    Route::get('item_unit_of_measures/delete',[ItemUnitofMeasureController::class,'delete']);
     

});
Route::group(['prefix' => 'warehouse'], function () {
    Route::get('/getFiledListwarehouse',[WarehouseController::class,'getfield']);
    Route::resource('/warehouse',WarehouseController::class);
    Route::get('/warehouseList',[WarehouseController::class,'show'])->name('warehouse.list');
    Route::get('/showmodal_warehouse',[WarehouseController::class,'showadd']);
    Route::post('/addNewwarehouse',[WarehouseController::class,'save']);
    Route::post('/show_to_edit_warehouse',[WarehouseController::class,'getValueEdit']);
    Route::post('/saveEdit_warehouse',[WarehouseController::class,'editValue']);
    Route::post('/deletewarehouse',[WarehouseController::class,'destroy']);

});
Route::group(['prefix'=>'system'],function(){
    Route::get('search/{group}',[SystemController::class,'getItem']);
    // Route::get('search/uom',[SystemController::class,'getUom']);
});
 
