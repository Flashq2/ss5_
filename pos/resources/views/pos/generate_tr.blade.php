@if ($line_header)
<?php $i = 0 ?>
@foreach ($line_header as $lines)
    <?php 
        $i++;
        $uom = App\Models\ItemUnitofMeasureModel::select("unit_of_measure_code")
        ->where('item_no',$lines->item_no)->get();
    ?>
     <tr data-line ="{{$i}}" data-line_id ={{$lines->id}}  id="{{$lines->id}}">
        <td><p class="respon3"> <i class="fa-regular fa-trash-can remove"style="color: #cf204c;" data-line ="{{$i}}" data-line_id ="{{$lines->id}}"></i></p></td>
        <td><p class="respon3" name="item_{{$lines->id}}" id="item_{{$lines->id}}">{{$lines->item_no}}</p> </td>
        <td><p class="respon3">{{$lines->description}}</p>  </td>
        <td>
            <select name="uom_{{$lines->id}}" id="uom_{{$lines->id}}" style="width: 100px;" class="uom" data-line_id ={{$lines->id}} data-document_no= {{$document_no}}>
                <option value="">&nbsp;</option>
                @if($uom)
                    @foreach ($uom as $uoms)
                      <option value="{{$uoms->unit_of_measure_code}}" {{$uoms->unit_of_measure_code == $lines->unit_of_measure?"selected":""}}>{{$uoms->unit_of_measure_code}}</option>
                    @endforeach
                @endif
            </select>   
        </td>
        <td><p class="respon3">{{\App\Helpers\GlobalFunction::numberFormate($lines->unit_price,'amount')}}</p> </td>
        <td><input type="number" class="form-control respone2 qty" name="qty_{{$lines->id}}" id="qty_{{$lines->id}}"  style="width: 100px;" value="{{\App\Helpers\GlobalFunction::numberFormate($lines->quantity,'quantity')}}" data-line_id ={{$lines->id}} data-document_no= {{$document_no}}></td>
        <td><input type="text" class="form-control respone2 des" name="des_{{$lines->id}}" id="des_{{$lines->id}}" style="width: 100px;"  placeholder=" % or $" value="{{\App\Helpers\GlobalFunction::numberFormate($lines->discount_amount,'amount')}}" data-line_id ={{$lines->id}} data-document_no= {{$document_no}}>  </td>
        <td><p class="respon3">{{\App\Helpers\GlobalFunction::numberFormate($lines->amount,'amount')}}</p></td>
        
    </tr>
@endforeach

@endif