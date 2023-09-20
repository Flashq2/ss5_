<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.header')
</head>

<body>
    <div class="wrapper">
        @include('layouts.side_left')

        <div class="main">
            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle js-sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>
            </nav>


            <main class="content">

                <div class="row">
                    <h1>User</h1>
                    <hr>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-2">

                                        <a href="{{ url('user/newrecord') }}"> <button class="action"> Action
                                            </button></a>
                                    </div>
                                    @if (isset($_GET['code']))
                                        <?php
                                        $url = 'user/updateuser';
                                        $value = App\Models\UserModel::select('*')
                                            ->where('id', $_GET['code'])
                                            ->first();
                                        ?>
                                    @else
                                        <?php
                                        $url = 'user/adduser';
                                        ?>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <form action="{{ url($url) }}" enctype="multipart/form-data" method="POST">
                        @csrf
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
                        <button type="submit">Submit</button>
                    </form>
                </div>

            </main>

        </div>
    </div>
</body>
@include('script')
<script>
    $(document).ready(function() {

        // $('#userrole').select2();
        // $('#permission').select2();
        $('#inactived').select2();
        $('document').on('submit', function(e) {
            e.preventDefault();
        })
    });
</script>

</html>
