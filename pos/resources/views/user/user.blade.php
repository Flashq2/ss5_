
<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.header')
</head>
@include('layouts.content');
@include('script');

<script>
    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
        });
    $(document).ready(function () {
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
                url: `getFiledListuser`,
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
            datatable = $('#user').DataTable({
                processing: true,
                serverSide: true,
                rowReorder: true,
                ajax: " {{ route('user.list') }}",
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
        
        $(document).on('click',' .actiondelete',function(){
        //    alert($(this).data("delete")) ;
            let code_to_delete=$(this).data("delete");
            $.ajax({
                url:'deleteuser/'+code_to_delete,
                type:'POST',
                contentType: false,
                cache: false,
                processData: false,
                success:function(){
                    datatable.ajax.reload(null, false);
                    toastr.info('Are you the 6 fingered man?')
                }
            })
        });
     
        
    });
</script>
</html>
