<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.header')
</head>
<style>
    .titles::first-letter {
        text-transform: capitalize;
    }

    .control_img {
        height: 300px;
        margin-top: 30px;
        border-radius: 5px !important;
        /* border: 2px solid #ede3e3; */
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
    }

    .control_img img {
        width: 70%;
        height: 80%;
        object-fit: contain;



    }

    .card {
        margin: 20px;
        padding: 20px;
        width: 300px;
        min-height: 200px;
        display: grid;
        grid-template-rows: 20px 50px 1fr 50px;
        border-radius: 10px;
        box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.25);
        transition: all 0.2s;
    }

    .card:hover {
        box-shadow: 0px 6px 10px rgba(0, 0, 0, 0.4);
        transform: scale(1.01);
    }

    .card__link,
    .card__exit,
    .card__icon {
        position: relative;
        text-decoration: none;
        color: rgba(19, 17, 17, 0.9);
        width: 100%;
        height: 100px;
        /* background: aqua; */
        border-radius: 20px;
    }

    .card__icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;

    }

    .card__link::after {
        position: absolute;
        top: 25px;
        left: 0;
        content: "";
        width: 0%;
        height: 3px;
        background-color: rgba(255, 255, 255, 0.6);
        transition: all 0.5s;
    }

    .card__link:hover::after {
        width: 100%;
    }

    .text {
        color: rgb(187, 8, 8);
    }

    .card__exit {
        grid-row: 1/2;
        justify-self: end;
    }

    .card__icon {
        grid-row: 2/3;
        font-size: 30px;
    }

    .card__title {
        grid-row: 3/4;
        font-weight: 400;
        color: #c51818;
    }

    .card__apply {
        grid-row: 4/5;
        align-self: center;
    }

    .card-5 {

        /* background: radial-gradient(#f588d8, #c0a3e5); */
    }

    .img-rounded {
        width: 40px;
        height: 40px;
        border-radius: 10px !important;
        cursor: pointer;
    }

    @media (max-width: 1600px) {
        .cards {
            justify-content: center;
        }
    }
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
                    <h1>Items</h1>

                </div>
                <hr>
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
                <div class="row">
                    <table id="item" class="table" style="width:100%">
                        <thead>
                            <th>Action</th>
                            <th>Image</th>
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
                @include('layouts.modal_image')
                @include('layouts.loading')
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
        $('.sty_loader').hide();
        $(document).on('click', '.img-rounded', function() {
            let url = $(this).data('src')
            $('#exampleModal').modal('show')
            $('.pre_img').attr('src', url)
        })
        var datatable;
        let tr = '';
        let ty = [];
        ty.push({
            data: 'action',
            name: 'action',
            orderable: true,
            searchable: true
        });
        ty.push({
            data: 'product_brand_logo',
            name: 'product_brand_logo',
            orderable: true,
            searchable: true
        });

        $(function() {

            $.ajax({
                url: 'getfiledlistitme',
                type: 'get',
                success: function(data) {
                    data.data.forEach(element => {

                        if (element != 'updated_at' && element != 'created_at' &&
                            element != 'deleted_at' && element != 'picture') {
                            console.log(element)
                            ty.push({
                                data: element,
                                name: element
                            });

                        }

                    });
                },
                async: false

            });
            datatable = $('#item').DataTable({
                processing: true,
                serverSide: true,
                rowReorder: true,
                ajax: " {{ route('item.list') }}",
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
        $(document).on('click', '.submit', function(e) {
            e.preventDefault()
            $.ajax({
                type: "post",
                url: "item/save",
                data: $(document).find('form').serialize(),
                success: function(response) {
                    if (response.message === "fails") {
                        $(document).find('#no').focus();
                        toastr.warning(`${response.status.no}`)
                    } else {
                        $(document).find('.form_submit').html(response.form)
                        toastr.success(`New item has been created`);
                        datatable.ajax.reload(null, false)

                    }
                }
            });
        });
        $(document).on('click', '.edit', function() {

            let code = $(this).data('edit');
            let data = {
                'code': code,
            }
            $.ajax({
                type: "get",
                url: "item/edit",
                data: data,
                beforeSend: function() {
                    $('.sty_loader').show();
                },
                success: function(response) {
                    $('.sty_loader').fadeOut('2000');
                    $(document).find('.form_submit').html(response.form)
                }
            });
        })
        $(document).on('click', '.submit_edit', function(e) {
            e.preventDefault()
            $.ajax({
                type: "post",
                url: "item/submmit_edit",
                data: $(document).find('form').serialize(),
                success: function(response) {
                    if (response.message === "fails") {
                        $(document).find('#no').focus();
                        toastr.warning(`${response.status.no}`)
                    } else {
                        
                        $(document).find('.form_submit').html(response.form)
                        toastr.success(`New item has been created`);
                        datatable.ajax.reload(null, false)

                    }
                }
            });
        });

        $('#file').change(function() {
            let reader = new FileReader();
            reader.onload = (e) => {
                //  $('#pre').attr('src', e.target.result); 
                $('body').find('.pre_img').attr('src', e.target.result);

            }


            reader.readAsDataURL(this.files[0]);
        })
        $(document).on('click','.img-rounded',function(){
            let data=$(this).data('code');
             $('.uploadImage').attr('data-code',data);
             $('.resetImage').attr('data-code',data);
        })
        $(document).on('click','.uploadImage',function(){
            let code=$(this).data('code');
            let file=$('#file').val();
            let data=new FormData(imgForm);
            data.append('code',code);
            data.append('file',$('input[type=file]')[0].files[0]);
             
            // let data={
            //     'code':code,
            //     'file':file,
            // }
            $.ajax({
                type: "post",
                url: "item/uploadImage",
                data: data,
                processData: false,
                contentType: false,
                success: function (response) {
                    datatable.ajax.reload(null, false)
                    $('#exampleModal').modal('hide')
                    Swal.fire(
                        'Image has been upload!',
                        '&nbsp;',
                        'success'
                        )
                }
            });
        })

        $(document).on('click','.resetImage',function(){
            let code=$(this).data('code');
            $.ajax({
                type: "post",
                url: "item/deleteImage",
                data:{
                    'code':code
                },
                success: function (response) {
                    datatable.ajax.reload(null, false)
                    $('#exampleModal').modal('hide')
                    Swal.fire(
                        'Image has been Reset!',
                        '&nbsp;',
                        'success'
                        )
                }
            });
        })
        $(document).on('click','.actiondelete',function(){
            let code=$(this).attr('data-delete');
            $.ajax({
                type: "get",
                url: "item/delete",
                data:{
                    'code':code,
                },
                 
                success: function (response) {
                    datatable.ajax.reload(null, false)
                    Swal.fire(
                        'Item has been delete!',
                        '&nbsp;',
                        'success'
                        )
                }
            });
        })

    });
</script>

</html>
