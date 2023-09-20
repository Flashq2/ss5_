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
 @include('layouts.content')
    @include('script');
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')    
        }
    });
    $(document).ready(function() {
        let prefix="stock_adjustment";
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
                url: `getfiledlist${prefix}`,
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
            datatable = $(`#${prefix}`).DataTable({
                processing: true,
                serverSide: true,
                rowReorder: true,
                ajax: " {{ route('stockAdjustment.list') }}",
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
                url: `${prefix}/save` ,
                data: $(document).find('form').serialize(),
                success: function(response) {
                    if (response.message === "fails") {
                        $(document).find('#id').focus();
                        toastr.warning(`${response.status.id}`)
                    } else {
                        $(document).find('.form_submit').html(response.form)
                        reniUli();
                        toastr.success(`New Item Unit of Measures has been created`);
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
                url: `${prefix}/edit`,
                data: data,
                beforeSend: function() {
                    $('.sty_loader').show();
                },
                success: function(response) {
                    $('.sty_loader').fadeOut('2000');
                    $(document).find('.form_submit').html(response.form)
                    reniUli();
                }
            });
        })
        $(document).on('click', '.submit_edit', function(e) {
            e.preventDefault()
            $.ajax({
                type: "post",
                url: `${prefix}/submmit_edit`,
                data: $(document).find('form').serialize(),
                success: function(response) {
                    if (response.message === "fails") {
                        $(document).find('#no').focus();
                        toastr.warning(`${response.status.no}`)
                    } else {
                        
                        $(document).find('.form_submit').html(response.form)
                        toastr.success(`New Item Unit of Measures has been created`);
                        datatable.ajax.reload(null, false)
                        reniUli();
                    }
                }
            });
        });
 
        $(document).on('click','.actiondelete',function(){
            let code=$(this).attr('data-delete');
            $.ajax({
                type: "get",
                url: `${prefix}/delete`,
                data:{
                    'code':code,
                },
                 
                success: function (response) {
                    datatable.ajax.reload(null, false)
                    Swal.fire(
                        'Item Unithh of Measures has been delete!',
                        '&nbsp;',
                        'success'
                        )
                }
            });
        })
        $(document).on('change','#item_no ,#unit_of_measure_code',function(){
            let code=$('#item_no').val();
            let item_unit_of_measure=$('#unit_of_measure_code').val();
            
            $.ajax({
                type: "get",
                url: `${prefix}/remian_quantity`,
                data:{
                    'code':code,
                    'item_unit_of_measure' :item_unit_of_measure
                },
                success: function (response) {
                    $('#remain_quantity_in_stock').val(response.value);
                }
            });
        })
    });
</script>

</html>
