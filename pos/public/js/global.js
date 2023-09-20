// $('#item_no').select2();
var config={
    select2 : function (url,element){
        
        $(`#${element}`).select2({
            dropdownParent: $(`#${element}`).parent(),
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: `[${item.no}] ${item.description}`,
                                id: item.no
                            }
                        })
                    };
                },
                cache: true,
            },
            placeholder: 'Search for a Item',
            minimumInputLength: 1,
          });
        function formatState(state) {
            if (!state.id) {
                return state.text;
            }
           
            var $state = $(
                '<span>' +state.text + '</span>'
            );
            return $state;
        }
    } 
    
}
function reniUli(){
            config.select2('/system/search/item_no','item_no');
            config.select2('/system/search/unit_of_measure_code','unit_of_measure_code');
            config.select2('/system/search/item_group_code','item_group_code');
            config.select2('/system/search/permission_code','permission_code');   
            config.select2('/system/search/user_role_code','user_role_code'); 
            config.select2('/system/search/warehouse_code','warehouse_code');
            config.select2('/system/search/adjustment_type','adjustment_type');  
            config.select2('/system/search/inactived','inactived');
            config.select2('/system/search/item_category_code','item_category_code'); 
}
// Select 2 Customize
            config.select2('/system/search/item_no','item_no');
            config.select2('/system/search/unit_of_measure_code','unit_of_measure_code');
            config.select2('/system/search/item_group_code','item_group_code');
            config.select2('/system/search/permission_code','permission_code');   
            config.select2('/system/search/user_role_code','user_role_code'); 
            config.select2('/system/search/item_category_code','item_category_code'); 
            config.select2('/system/search/warehouse_code','warehouse_code'); 
            config.select2('/system/search/adjustment_type','adjustment_type'); 
            config.select2('/system/search/inactived','inactived');
// $(document).on('click','.js-sidebar-toggle',function(e){
//     $('#sidebar').css({'margin-left':'-250px'});
// })