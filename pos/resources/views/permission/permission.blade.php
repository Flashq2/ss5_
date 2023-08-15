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
 
                <!-- Modal -->
                <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                   
                </div>
                <div class="row">
                    <h1>Permission</h1>
                    <hr>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-2">
                                        <button   class="btn-newadd">New 
                                        </button>
                                    </div>

                                </div>
                            </div>
                            <div class="col-lg-6 blue">
                                <div class="row">
                                    <div class="col-md-10">
                                    
                                </div>
                                <div class="col-md-2">
                                     <button class="setting">Setting<i class="fa-solid fa-gear"></i></button>
                                     {{-- <button type="button" class="btn btn-lg btn-danger" data-bs-toggle="popover" title="Popover title" data-bs-content="And here's some amazing content. It's very engaging. Right?">Click</button> --}}
                                </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <table id="permission" class="table" style="width:100%">
                        <thead>
                            <th></th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Description 2</th>
                            <th>Inactived</th>

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
@include('script')
<script>
     $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
        });
    $(document).ready(function() {
        $('.sty_loader').hide();
        
        $(function() {
            datatable = $('#permission').DataTable({
                processing: true,
                serverSide: true,
                rowReorder: true,
                ajax: " {{ route('permission.list') }}",
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'description_2',
                        name: 'description_2'
                    },
                    {
                        data: 'inactived',
                        name: 'inactived'
                    },

                ],
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
        staticBackdrop
        $('#inactived').select2({
        dropdownParent: $('#staticBackdrop')
    });

    $(document).on('click','#form_permission',function(){
     $('.form_data').trigger('reset');
    })
    //====================Add New Permission=========================// 
    $(document).on('click','#btn_save',function(){
        let code=$('#code').val()
        let des=$('#description').val()
        let des2=$('#description2').val()
        let inactived=$('#inactived').val()
         var data={
            'code':code,
            'description2':des2,
            'description':des,
            'inactived':inactived
         };
         $.ajax({
                url:'addnewpermission',
                type:'POST',
                data:data,
                success:function(){
                    datatable.ajax.reload(null, false);
                    toastr.success('New Permission has Been add To Your Project');
                    $('#staticBackdrop').modal('hide');
                }
            })
    })


//==================Add new Permision==================/
$(document).on('click','.btn-newadd',function(){
    $.ajax({
                url:`showmodal`,
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
//==================Show Data Edit========================/
$(document).on('click','.edit',function(){
    let data_edit=$(this).data('edit');
    let code=$(this).data('edit')
         var data={
          'code':code
         };
    // alert(code_to_delete)
    $.ajax({
                url:`show`,
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
    let code=$('#code').val()
        let des=$('#description').val()
        let des2=$('#description2').val()
        let inactived=$('#inactived').val()
         var data={
            'code':code,
            'description2':des2,
            'description':des,
            'inactived':inactived
         };
         $.ajax({
                url:`editpermission`,
                data:data,
                success:function(){
                    datatable.ajax.reload(null, false);
                    toastr.success(`Permission Code ${code} has been update`);
                    $('#staticBackdrop').modal('hide');
                    window.history.replaceState(null, null,'/permission/permission');
                }
            })
})
$(document).on('click','.actiondelete',function(){
    
        let code_to_delete=$(this).data('delete')
         var data={
          'code_to_delete':code_to_delete
         };
         $.ajax({
                url:`deletepermission`,
                data:data,
                success:function(){
                    datatable.ajax.reload(null, false);
                    toastr.warning('Permission Has Been delete from your System');
                }
            })
})

    });
</script>


</html>
