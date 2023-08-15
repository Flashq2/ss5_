<form id="item_form">

    <div class="row">
        @foreach ($field as $item_field)
            @if (
                $item_field != 'updated_at' &&
                    $item_field != 'created_at' &&
                    $item_field != 'deleted_at' &&
                    $item_field != 'picture')
                <div class="col-lg-6">
                    <label for="" style="padding:5px;" class="titles">{{ str_replace('_', ' ', $item_field) }}
                        @if ($item_field == 'no')
                            <span style="color: red">*</span>
                        @endif
                    </label>
                    @if ($item_field == 'no' && isset($value))
                        <input type="text" class="form-control" id="{{ $item_field }}" name="{{ $item_field }}"
                            value="{{ $value->$item_field ?? '' }}" readonly>
                    @elseif($item_field == 'item_no' || $item_field == 'unit_of_measure_code')
                        <?php
                        $item = App\Models\Itemmodel::where('inactived', '<>', 'Yes')->get();
                        ?>
                        <select name="{{$item_field}}" id="{{$item_field}}" class="form-control" style="padding: 10px !important;">
                                
                        </select>
                    @else
                        <input type="text" class="form-control" id="{{ $item_field }}" name="{{ $item_field }}"
                            value="{{ $value->$item_field ?? '' }} ">
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
<option value=""></option>
<button style="background-color:red;">Reset</button><br><br><br>
