<div class="modal-dialog modal-lg"  >
    <div class="modal-content ">
        <div class="modal-header">
            
            <h1 class="modal-title fs-5" id="staticBackdropLabel">
                <?php
                   if(isset($code)) 
                   {
                      
                     $textitle='Edit ';
                     $class_btn_save='btn_edit';
                     $title_btn_save='Save Edit';
                   }
                    ?>
                     
                     {{$textitle??'Add New '.$tablename}}

                     
            </h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal"
                aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form action="" class="form_data" id="form_data">

                <div class="row">
                @foreach ($data as $datas)
                  @if ($datas != 'updated_at' && $datas != 'deleted_at' && $datas != 'created_at' && $datas != 'picture')
                   <div class="col-12">
                    <div class="row">
                        <div class="col-2">
                     <div class="title">
                        {{$datas}}
                     </div>
                </div>
                <div class="col-8">
                    <input type="text" class="form-control" id="{{$datas}}" name="{{$datas}}"  placeholder="{{$datas}}" value="{{$code[0]->$datas??''}}" 
                    
                        @if (isset($code[0]->code) && $datas=="code")
                            readonly
                        @endif
                    >
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
            <button type="button" class="btn btn-primary" id="{{$class_btn_save??'btn_save'}}">
                {{$title_btn_save??'Save '.$tablename}}
            </button>
        </div>
    </div>
</div>