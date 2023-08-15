<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.header')
</head>
<style>
 
</style>
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
                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                   
                </div>
                <div class="row">
                    <h1>Item Group</h1>
                    <hr>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-2">
                                        <button   class="btn-newadd">New 
                                        </button>
                                    </div>

                                </div>
                            </div>
                            <div class="col-6">
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <table id="Item_group" class="table" style="width:100%">
                        <thead>
                            <th></th>
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
            </main>

        </div>
    </div>
</body>
@include('script');
<script>
  
 
//  let  source = new EventSource('https://streaming-graph.facebook.com/303408708791203/live_comments?access_token=EAAH9VoH5dXsBAGNcsjBI0sOUWkogJSRJfsxkKjNKnmWsqlvZBnG0S6JVqZAqkhNLObSlXgBqBrB7pBYAWIFkXkwoBSmEUFHpWODAxSqZCmEvSPTae01aCdknjlRmiMV18c46RJa8PYFLGWx6D3qVUYy4sST6Mu1QaZA6hzdSCzzQ1nPRqLCJ',{ withCredentials: true});
//  let  source = new EventSource('https://streaming-graph.facebook.com/1404541393702793/live_comments?access_token=EAAH9VoH5dXsBAGNcsjBI0sOUWkogJSRJfsxkKjNKnmWsqlvZBnG0S6JVqZAqkhNLObSlXgBqBrB7pBYAWIFkXkwoBSmEUFHpWODAxSqZCmEvSPTae01aCdknjlRmiMV18c46RJa8PYFLGWx6D3qVUYy4sST6Mu1QaZA6hzdSCzzQ1nPRqLCJ');
// source.onopen = function (event) {
//    console.log(event)
// };
// source.onerror = function (event) {
//     console.log(event);
// };
// source.onmessage = function (event) {
//    console.log(event);
// };
//     </script>
 <script>
     $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
        });
    $(document).ready(function () {
        let prefix="Item_group"
        $('.sty_loader').hide();
        var datatable;
        let tr = '';
        let ty = [];
        ty.push({
            data: 'action',
            name: 'action',
            orderable: true,
            searchable: true
        });
        

        $(function() {

            $.ajax({
                url: `getFiledList${prefix}`,
                type: 'get',
                success: function(data) {
                    data.data.forEach(element => {

                        if (element != 'updated_at' && element != 'created_at' &&
                            element != 'deleted_at' && element != 'picture') {
                            
                            ty.push({
                                data: element,
                                name: element
                            });

                        }

                    });
                },
                async: false

            });
            datatable = $(`#${prefix}`).DataTable({
                processing: true,
                serverSide: true,
                rowReorder: true,
                ajax: `{{ route("Item_group.list") }}`,
                columns: ty,
                dom: "Blfrtip",
                buttons: [

                    {
                        extend: 'copy',
                        exportOptions: {
                            modifier: {
                                page: 'all',
                                search: 'none'
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        exportOptions: {
                            modifier: {
                                page: 'all',
                                search: 'none'
                            }
                        }
                    },
                    {
                        extend: 'csv',
                        exportOptions: {
                            modifier: {
                                page: 'all',
                                search: 'none'
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            modifier: {
                                page: 'all',
                                search: 'none'
                            }
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            modifier: {
                                page: 'all',
                                search: 'none'
                            }
                        }
                    },



                ],
            });

        })
        $(document).on('click','.btn-newadd',function(){
             $.ajax({
                url:`showmodal_${prefix}`,
                type:"get",
                contentType: false,
                processData: false,
                beforeSend:function() {
                    $('.sty_loader').show(); 
                },
                success:function(data){
                    $('.sty_loader').fadeOut();
                    setTimeout(() => {
                           $('#staticBackdrop').html(data);
                     $('#staticBackdrop').modal('show')
                    }, 100);
 
                }
            })



})
$(document).on('click','#btn_save',function(){
         $.ajax({
                url:`addNew${prefix}`,
                type:'POST',
                data:$(document).find('.form_data').serialize(),
                success:function(){
                    datatable.ajax.reload(null, false);
                    toastr.success('New Permission has Been add To Your Project');
                    $('#staticBackdrop').modal('hide');
                }
            })
    }) 

//==================Show Data Edit========================/
$(document).on('click','.edit',function(){
    let data_edit=$(this).data('edit');
    let code=$(this).data('edit')
         var data={
          'code':code
         };
    // alert(code_to_delete)
    $.ajax({
                type:"POST",
                url:`show_to_edit_${prefix}`,
                data:data,
                beforeSend: function( xhr ) {
                    $('.sty_loader').show(); 
                },
                success:function(data){
                    $('.sty_loader').fadeOut(2000);
                    setTimeout(() => {
                           $('#staticBackdrop').html(data);
                     $('#staticBackdrop').modal('show')
                    }, 1000);
                  
                     
                    
                }
            })



})
$(document).on('click','#btn_edit',function(){
         $.ajax({
            type:"POST",
                url:`saveEdit_${prefix}`,
                data:$(document).find('.form_data').serialize(),
                success:function(){
                    datatable.ajax.reload(null, false);
                    toastr.success(`Item Groups Code ${code} has been update`);
                    $('#staticBackdrop').modal('hide');
                    
                }
            })
})
$(document).on('click','.actiondelete',function(){
    
    let code_to_delete=$(this).data('delete')
     var data={
      'code_to_delete':code_to_delete
     };
     $.ajax({
        type:"POST",
            url:`delete${prefix}`,
            data:data,
            success:function(){
                datatable.ajax.reload(null, false);
                toastr.warning('Item Groups Has Been delete from your System');
            }
        })
})
    });
 
 </script>
</html>
