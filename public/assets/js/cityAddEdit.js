$('body').on('change','#country',function(){

    cityOptionShowHide('hide');
    $('#city').html(select_opt);
    $('#city_level').text(city_dist_level_txt);

    var country_id = $(this).val();
    if(country_id != '1') {
        $('#zip_div').hide();
    }

    if(country_id > 0) {

        var ajax_url = ajax_url_province_list;
        var data = {'country_id':country_id};
        callAjax(ajax_url, 'post', data, function(result){
        result = JSON.parse(result);
            if(result.status == 'success') {
                $('#province').html(result.province_list);
            }
            else {
                $('#province').html(select_opt);
            }                
        });
    }
    else {
        $('#province').html(select_opt);
    }        
}); 

$('body').on('change','#province',function(){

    var country_id = $('#country').val();
    if(country_id != '1') {
        return false;
    }
    var province_id = $(this).val();
    if(province_id>0) {

        var ajax_url = ajax_url_city_list;
        var data = {'province_id':province_id};
        callAjax(ajax_url, 'post', data, function(result){
            result = JSON.parse(result);
            if(result.status == 'success') {
                $('#city').html(result.city_list);
                $('#city_level').text(sub_dist_level_txt);
                cityOptionShowHide('show');
                $('#zip_div').hide();
            }
            else {
                $('#city').html(select_opt);
                $('#city_level').text(city_dist_level_txt);
                cityOptionShowHide('hide');
                $('#zip_div').show();
            }                 
        });
    }
    else {
        $('#city').html(select_opt);
    }        
});

$('body').on('click', '.radio', function(){

    var district_type = $(this).val();
    if(district_type == '2') {
        $('#city_level').text(sub_dist_level_txt);
        $('#city_div').show();
        $('#zip_div').hide();
    }
    else {
        $('#city_level').text(city_dist_level_txt);
        $('#city_div').hide();
        $('#zip_div').show();
    }   
});

function cityOptionShowHide(type) {
    if (type=='show') {
        $('#city_div_radio').show();
        $('#city_div').show();
        $('#district_type_2').prop("checked", true);
    }
    else{
        $('#city_div_radio').hide();
        $('#city_div').hide();
        $('#district_type_1').prop("checked", true);
    }
}