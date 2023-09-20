<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.header')
</head>
<style>
    /* .title::first-letter{
        text-transform: capitalize;
    } */
</style>

@include('layouts.content');

@include('script');
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).ready(function() {
        var datatable;
        let tr = '';
        let ty = [];
        ty.push({
            data: 'action',
            name: 'action',
            orderable: true,
            searchable: true
        })




        $(function() {
            
            $.ajax({
                url: 'getfiledlist',
                type: 'get',
                success: function(data) {
                    data.data.forEach(element => {
                        if (element != 'updated_at' && element != 'created_at' &&
                            element != 'deleted_at') {
                            ty.push({
                                data: element,
                                name: element
                            });
                        }

                    });
                },
                async: false

            });
            console.log(ty)

            datatable = $('#customer').DataTable({
                processing: true,
                serverSide: true,
                rowReorder: true,
                ajax: " {{ route('customer.list') }}",
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

        $(document).on('click', ' .actiondelete', function() {
            //    alert($(this).data("delete")) ;
            let codetodelete = $(this).data("delete");
            $.ajax({
                url: 'deletecustomer/' + codetodelete,
                type: 'POST',
                contentType: false,
                cache: false,
                processData: false,
                success: function() {
                    datatable.ajax.reload(null, false);
                    toastr.success(`${codetodelete} has been delete from your System`)
                }
            })
        });
        $(document).on('click','.show_modal',function(e){
            $('#table_record').modal("show");
        })


    });
</script>

</html>
