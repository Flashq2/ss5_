<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.header')
</head>

@include('layouts.content')
@include('script');
<script src="{{asset('js/barcode.js')}}"></script>

 <script>
        $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
        });
    $(document).ready(function () {
        let prefix="barcode"
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
                ajax: `{{ route("barcode.list") }}`,
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

        });
        $(document).on("click",'.print', function () {
            let code = $(this).data("edit");
            $.ajax({
                type: "get",
                url: `${prefix}/create`,
                data: {
                    code:code
                },
                beforeSend: function(){
                    $('.sty_loader').show();
               },
                success: function (response) {
                     $('.print-class').html(response.view);
                     $('.print-class').printThis({});
                    $('.sty_loader').hide();

                     
                }
            });
            
        });
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
                            toastr.warning('Barcode Delete');
                        }
        })
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
                            reniUli()
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
                     reniUli()
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

    });
 </script>
</html>
