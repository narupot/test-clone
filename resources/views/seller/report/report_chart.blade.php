<?php 
	use \koolreport\widgets\google\LineChart;
	LineChart::create(array(
        "title"=>$chart_title,
        "dataSource"=>$chart_data,
        "width"=>"100%",
        "height"=>"400px",
        "columns"=>array(
            $intrvl,
            $sale=>array(
                "label"=>$sale_label,
                "type"=>"number",
                "suffix"=>" ".$currency,
            ),
        )
    ));
?>