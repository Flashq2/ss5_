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
                                        $header = App\Models\UserModel::select('*')
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
                    <form action="{{url($url)}}" enctype="multipart/form-data" method="POST">
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
                </div>

            </main>

        </div>
    </div>
</body>
@include('script')
<script>
    $(document).ready(function() {

        $('#userrole').select2();
        $('#permission').select2();
        $('#inactived').select2();
        $('document').on('submit', function(e) {
            e.preventDefault();
        })
    });
</script>

</html>
