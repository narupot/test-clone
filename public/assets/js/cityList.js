$('body').on('change','#country',function(){

    var country_id = $(this).val();
    if(country_id > 0) {
        window.location = redirect_url+'?country='+country_id;
    }

    // var country_id = $(this).val();
    // if(country_id > 0) {
    //     var data = {'country_id':country_id};
    //     callAjax(ajax_url, 'post', data, function(result){
    //     result = JSON.parse(result);
    //         if(result.status == 'success') {
    //             $('#province').html(result.province_list);
    //         }
    //     });
    // }
    // else {
    //     $('#province').html('');
    // }
}); 

$('body').on('change','#province',function(){

    var country_id = $('#country').val();
    var province_id = $(this).val();
    if(province_id > 0 && country_id > 0) {
        window.location = redirect_url+'?country='+country_id+'&province='+province_id;
    }
});

$('body').on('change','#city',function(){

    var country_id = $('#country').val();
    var province_id = $('#province').val();
    var city_id = $(this).val();
    if(province_id > 0 && country_id > 0 && city_id > 0) {
        window.location = redirect_url+'?country='+country_id+'&province='+province_id+'&city='+city_id;
    }
}); 