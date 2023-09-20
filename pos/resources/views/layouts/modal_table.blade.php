<style>
    .title{
        text-transform:capitalize !important;
        text-align: right !important;
    }
    .form_data_table{
        overflow: scroll;
    }
</style>
<?php
 $table =  App\Models\TableModel::where('table_name','table')->get()->toArray();
?>
<div class="modal-dialog modal-fullscreen">
    <div class="modal-content  ">
        <div class="modal-header">

            <h1 class="modal-title fs-5" id="staticBackdropLabel">
                Create new Column
            </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="" class="form_data_table" id="form_data_table">
                <div class="row">
                    <table class="table">
                        <thead>
                                <tr>
                                    @foreach (array_keys($table) as $key)
                                    {{-- {{ dd($key);}} --}}
                                            @if(!in_array($key,['created_at','updated_at','deleted_at']))
                                            <th>1{{$key}}</th>
                                            @endif
                                    @endforeach
                                </tr>
                        <tbody>
                        </tbody>
                        </thead>
                    </table>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="{{ $class_btn_save ?? 'btn_save' }}">
                {{ $title_btn_save ?? 'Save ' }}
            </button>
        </div>
    </div>
</div>
