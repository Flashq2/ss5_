<form action="{{$url}}" enctype="multipart/form-data" method="POST">
    @csrf
    <div class="row">
    @foreach ($data as $datas)
        @if($datas !='created_at' && $datas !='deleted_at' && $datas !='updated_at')
        <div class="col-lg-6">
            <div class="row">
                <div class="col-lg-2">
             <div class="title" style="float:left;">
                {{$datas}}
             </div>
        </div>
        <div class="col-lg-8">
                
            <input type="text" class="form-control" name="{{$datas}}" id="{{$datas}}" value="{{$header->$datas??'';}}"> 
        </div> 
            </div>
           
        </div>
        @endif 
     
    @endforeach
     </div>
    <div class="row">
        <div class="col-1">
            
        </div>
        <div class="col-2">
            <button type="submit">Save</button>
            <button type="submit">Back</button>
        </div>
    </div>
   

</form>