<body class="g-sidenav-show  bg-gray-200">
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

</div>
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
            @include('layouts.side_left')
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg p-4">
        @include('layouts.side_top')
        <div class="row">
            <h1>{{$title}}</h1>
            <hr>
            @if(isset($is_form))
            <div class="row">
                <div class="col-lg-8 form_submit">
                    @include('layouts.form_in_card')
                </div>
                <div class="col-lg-4">

                </div>
                <div class="col-lg-6">

                </div>
                <div class="col-lg-6">


                </div>
            </div>
            @else
            <div class="col-12">
                <div class="row">
                    <div class="col-6">
                        <div class="row">
                            <div class="col-2">
                            @if(isset($is_card))
                             <a href="{{url($link)}}"> <button class="action"> Action  
                            </button></a> 
                            @else
                                <button   class="btn-newadd">New 
                                </button>
                            </div>
                            @endif
                            
                        </div>
                    </div>
                    <div class="col-6">
                    </div>
                </div>
            </div>
            @endif
        </div>
        <hr>
        <div class="row">
            <table id="{{$table_name}}" class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                <thead>
                    <th>Action</th>
                    @if(isset($show_image))
                    <th>Image</th>
                    @endif
                    @foreach ($field as $fields)
                    @if ($fields != 'updated_at' && $fields != 'deleted_at' && $fields != 'created_at' && $fields != 'picture')
                        <th> {{ str_replace('_', ' ', $fields) }} </th>
                    @endif
                    @endforeach
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        @include('layouts.loading')
        @include('layouts.modal_image')
        @include('layouts.loading')
    </main>
</body>
<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>