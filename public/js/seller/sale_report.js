jQuery(document).ready(function($){
	//$(".date-select").flatpickr(); 
	var from_date = $("#date_from").val();
    var to_date = $("#date_to").val();
	loadChartData(from_date,to_date);

    if (jQuery(window).width() < 992) {
        jQuery('.seller-carousel').flickity({             
          freeScroll: true,
          wrapAround: true,
          cellAlign: 'left',      
          pageDots: false
        });
    }

    $(".line_chart_rep").on('focusout',function(){
    	var date_from = $("#date_from").val();
    	var date_to = $("#date_to").val();
    	if(date_from!='' && date_to!=''){
    		loadChartData(date_from,date_to);
    	}
    });

    function loadChartData(from,to){
    	var data = {'from_date':from,'to_date':to};
		callAjaxRequest(data_loading_in_chart_url, 'post', data, function(response){
	        $('#graph_block').html(response.chart_html);
	    });
    }
});