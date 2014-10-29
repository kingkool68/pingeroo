<?php
function pingeroo_upload_settings() {
	$settings = (object) array(
		'runtimes' => 'html5,flash,silverlight,html4',
		'browse_button' => 'media-upload',
		'url' => admin_url('admin-ajax.php'),
		'flash_swf_url' => includes_url() . 'js/plupload/plupload.flash.swf',
		'silverlight_xap_url' => includes_url() . 'js/plupload/plupload.silverlight.xap',
		'file_data_name' => 'test',
		'multipart_params' => (object) array(
			'_wpnonce' => wp_create_nonce( 'pingeroo-add-media' ),
			'action' => 'pingeroo_frontend_add_media'
		),
		'filters' => (object) array(
			'mime_types' => array(
				(object) array(
					'title' => 'Image files',
					'extensions' => 'jpg,gif,png'
				)
			)
		)
	);
	?>
	<script>
		var pingerooUploadSettings = <?php echo json_encode( $settings ); ?>;
	</script>
	<?php
}
add_action('wp_footer', 'pingeroo_upload_settings');

function pingeroo_frontend_add_media() {
	//var_dump( $_REQUEST, $_FILES );
	
	//Save it to the media library
	
	$random_color_arr = array('0099ff', 'ff0099', 'aa0676', '74b317', '62b4ce', '7881ac');
	shuffle( $random_color_arr );
	
	$response = (object) array(
		'img' => 'http://dummyimage.com/140/' . $random_color_arr[0] . '/&text=' . $_FILES['test']['name']
	);
	wp_send_json( $response );
	
	die();
}
add_action( 'wp_ajax_pingeroo_frontend_add_media', 'pingeroo_frontend_add_media' );
add_action( 'wp_ajax_nopriv_pingeroo_frontend_add_media', 'pingeroo_frontend_add_media' );