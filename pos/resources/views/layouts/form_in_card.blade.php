<form id="item_form">

    <div class="row">
        @foreach ($field as $item_field)
            @if (
                $item_field != 'updated_at' &&
                    $item_field != 'created_at' &&
                    $item_field != 'deleted_at' &&
                    $item_field != 'picture' 
                    )
                <div class="col-lg-6">
                    <label for="" style="padding:5px;" class="titles">{{ str_replace('_', ' ', $item_field) }}
                        @if ($item_field == 'no')
                            <span style="color: red">*</span>
                        @endif
                    </label>
                    @if ($item_field == 'no' && isset($value))
                        <input type="text" class="form-control" id="{{ $item_field }}" name="{{ $item_field }}"
                            value="{{ $value->$item_field ?? '' }}" readonly autocomplete="off">
                    @elseif($item_field == 'user')
                            <input type="text" class="form-control" id="{{ $item_field }}" name="{{ $item_field }}"
                            value="{{ Auth::user()->email }}" readonly autocomplete="off">
                    @elseif(in_array($item_field,['item_no','unit_of_measure_code','item_group_code','permission_code','user_role_code','item_category_code','warehouse_code','adjustment_type','inactived','no_serise']) )
                        
                        <select name="{{$item_field}}" id="{{$item_field}}" class="form-control" style="padding: 10px !important;">
                                @if(isset($value))
                                <option value="{{ $value->$item_field ?? '' }}" selected>{{ $value->$item_field ?? '' }}</option>
                                @endif
                        </select>
                    @else
                        <input type="{{in_array($item_field,['quantity','quantity_to_apply','remain_quantity_in_stock']) ? 'number' : 'text'}}" class="form-control" id="{{ $item_field }}" name="{{ $item_field }}"
                            value="{{ $value->$item_field ?? '' }}" {{$item_field == 'remain_quantity_in_stock' ? 'readonly' : ''}} autocomplete="off">
                    @endif

                </div>
            @endif
        @endforeach
    </div>


</form> 
@if (isset($value))
    <button class="submit_edit">Submit Edit</button>
@else
    <button class="submit">Submit</button>
@endif
<button style="background-color:red;">Resets</button><br><br><br>
