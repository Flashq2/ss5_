<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.header')
</head>

 @include('layouts.content');
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
            datatable = $('#user_role').DataTable({
                processing: true,
                serverSide: true,
                rowReorder: true,
                ajax: " {{ route('userrole.list') }}",
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


    $(document).on('click','.btn-newadd',function(){
    $.ajax({
                url:`showmodaluserrole`,
                type:"get",
                processData: false,
                contentType: false,
                beforeSend:function() {
                    $('.sty_loader').show(); 
                },
                success:function(data){
                    $('.sty_loader').fadeOut();
                    setTimeout(() => {
                        $('#staticBackdrop').html(data);
                        $('#staticBackdrop').modal('show')
                        reniUli()
                    }, 100);
 
                }
            })



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
                url:'addnewuserrole',
                type:'POST',
                data:data,
                success:function(data){
                    datatable.ajax.reload(null, false);
                    toastr.success(data.success);
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
                url:`edituserrole`,
                data:data,
                beforeSend: function( xhr ) {
                    $('.sty_loader').show(); 
                },
                success:function(data){
                    $('.sty_loader').fadeOut(2000);
                    setTimeout(() => {
                        $('#staticBackdrop').html(data);
                        $('#staticBackdrop').modal('show')
                        reniUli()
                    }, 1000);
                  
                     
                    
                }
            })
})
$(document).on('click','#btn_edit',function(){
    let code=$('#code').val()
        let des=$('#description').val()
        let des2=$('#description_2').val()
        let inactived=$('#inactived').val()
         var data={
            'code':code,
            'description2':des2,
            'description':des,
            'inactived':inactived
         };
         $.ajax({
                url:`clickedituserrole`,
                data:data,
                success:function(){
                    datatable.ajax.reload(null, false);
                    toastr.success(`Permission Code ${code} has been update`);
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
                url:`deleteuserrole`,
                data:data,
                success:function(data){
                    datatable.ajax.reload(null, false);
                    toastr.success(data.status);
                }
            })
})

    });
</script>


</html>
