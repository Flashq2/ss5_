// $('#item_no').select2();
var config={
    select2 : function (url,element){
        $(`#${element}`).select2({
            placeholder: 'Select movie',
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.no,
                                id: item.description
                            }
                        })
                    };
                },
                cache: true
            }
            // placeholder: 'Search for a Item',
            // minimumInputLength: 1,
            // templateResult: formatRepo,
            // templateSelection: formatRepoSelection
          });
    }
}
config.select2('/system/search/item_no','item_no');
config.select2('/system/search/unit_of_measure_code','unit_of_measure_code');
config.select2('/system/search/item_group_code','item_group_code');
config.select2('/system/search/permission_code','permission_code');   
config.select2('/system/search/user_role_code','user_role_code'); 
config.select2('/system/search/item_category_code','item_category_code'); 