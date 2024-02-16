<?php
return [
	'mode'                 => '',
	'format'               => 'A4',
	'margin_left'          => 10,
	'default_font_size'    => '24',
	'default_font'         => 'sans-serif',
	'margin_right'         => 10,
	'margin_top'           => 10,
	'margin_bottom'        => 10,
	'margin_header'        => 0,
	'margin_footer'        => 0,
	'orientation'          => 'P',
	'title'                => 'Laravel mPDF',
        'tempDir'               => base_path('storage/app/mpdf'),
	'author'               => '',
	'watermark'            => '',
	'show_watermark'       => false,
	'watermark_font'       => 'sans-serif',
	'display_mode'         => 'fullpage',
	'watermark_text_alpha' => 0.1,
	'dpi'				   => '72',
	'font_path' => base_path('resources/fonts/'), // don't forget the trailing slash!
	'font_data' => [
		'examplefont' => [
			'R'  => 'THSarabun.ttf',    // regular font
			'B'  => 'THSarabun Bold.ttf',       // optional: bold font
			'I'  => 'THSarabun Italic.ttf',     // optional: italic font
			'BI' => 'THSarabun Bold Italic.ttf' // optional: bold-italic font
		]
		// ...add as many as you want.
	]
];