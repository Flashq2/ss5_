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
                                     <a href="{{url('user/newrecord')}}"> <button class="action"> Action  
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
                    <table id="user" class="table" style="width:100%">
                        <thead>
                            <th></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Sale_Persion</th>
                            <th>Gender</th>
                            <th>Phone_No</th>
                            <th>Permision_Code</th>
                            <th>User Role Code</th>
                            <th>Address</th>
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
    $(document).ready(function () {
        var datatable
        $(function() {
            datatable = $('#user').DataTable({
                processing: true,
                serverSide: true,
                rowReorder: true,
                ajax: " {{ route('user.list') }}",
                columns: [
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'salesperson_code',
                        name: 'salesperson_code'
                    },
                    {
                        data: 'gender',
                        name: 'gender'
                    },
                    {
                        data: 'phone_no',
                        name: 'phone_no'
                    },
                    {
                        data: 'permission_code',
                        name: 'permission_code',
                        
                    },
                    {
                        data: 'user_role_code',
                        name: 'user_role_code'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    // {data: 'picture', name: 'picture'},
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
