<style>
    .title{
        text-transform:capitalize !important;
        text-align: right !important;
    }
</style>
<div class="modal-dialog modal-lg">
    <div class="modal-content ">
        <div class="modal-header">

            <h1 class="modal-title fs-5" id="staticBackdropLabel">
                <?php
                if (isset($code)) {
                    $textitle = 'Edit ';
                    $class_btn_save = 'btn_edit';
                    $title_btn_save = 'Save Edit';
                }
                ?>
                {{ $textitle ?? 'Add New ' . $tablename }}
            </h1>
        </div>
        <div class="modal-body"  >
            <form action="" class="form_data" id="form_data">
                <div class="row">
                    @foreach ($data as $datas)
                        @if ($datas != 'updated_at' && $datas != 'deleted_at' && $datas != 'created_at' && $datas != 'picture')
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-3">
                                        <div class="title">
                                            {{str_replace('_',' ',$datas)  }}
                                        </div>
                                    </div>
                                    <div class="col-8">
                                        @if(in_array($datas,['item_no','unit_of_measure_code','item_group_code','permission_code','user_role_code','item_category_code','inactived']) )
                        
                                            <select name="{{$datas}}" id="{{$datas}}" class="form-control" style="padding: 10px !important;">
                                                    <option value="{{ $code[0]->$datas ?? '' }}" selected>{{ $code[0]->$datas ?? '' }}</option>
                                            </select>
                                        @else
                                        <input type="text" class="form-control" id="{{ $datas }}"
                                            name="{{ $datas }}" placeholder="{{ $datas }}"
                                            value="{{ $code[0]->$datas ?? '' }}"
                                            @if (isset($code[0]->code) && $datas == 'code') readonly @endif>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="{{ $class_btn_save ?? 'btn_save' }}">
                {{ $title_btn_save ?? 'Save ' . $tablename }}
            </button>
        </div>
    </div>
</div>
