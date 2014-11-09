<?php
add_image_size( 'pingeroo-140', 140, 140, true );

function pingeroo_upload_settings() {
	$settings = (object) array(
		'runtimes' => 'html5,flash,silverlight,html4',
		'browse_button' => 'media-upload',
		'url' => admin_url('admin-ajax.php'),
		'flash_swf_url' => includes_url() . 'js/plupload/plupload.flash.swf',
		'silverlight_xap_url' => includes_url() . 'js/plupload/plupload.silverlight.xap',
		'file_data_name' => 'pingeroo-image',
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
	
	// These files need to be included as dependencies when on the front end.
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	
	//Save it to the media library
	$img_id = media_handle_upload( 'pingeroo-image', 0 );
	
	//Get the image source
	$src = wp_get_attachment_image_src( $img_id, 'pingeroo-140' );
	$full_src = wp_get_attachment_image_src( $img_id, 'large' );
	
	$response = (object) array(
		'id' => $img_id,
		'img' => $src[0],
		'full-img' => $full_src[0]
	);
	wp_send_json( $response );
	
	die();
}
add_action( 'wp_ajax_pingeroo_frontend_add_media', 'pingeroo_frontend_add_media' );
add_action( 'wp_ajax_nopriv_pingeroo_frontend_add_media', 'pingeroo_frontend_add_media' );