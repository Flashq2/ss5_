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
                    <h1>Customer </h1>
                    <hr>
                    <div class="col-12">
                        <div class="row">
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-2">
                                        <a href="{{ url('customer/addnewcustomer') }}"> <button class="action"> Action
                                            </button></a>
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
                    <table id="customer" class="table customer" style="width:100%">
                        <thead>
                            <th>Action</th>
                            @foreach ($field as $fields)
                                <th> {{ str_replace('_', ' ', $fields) }} </th>
                            @endforeach
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </main>

        </div>
    </div>
</body>
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


    });
</script>

</html>
