<?php
/*
 * Touch Icons
 *
 */
function get_pingeroo_touch_image_sizes() {
	return apply_filters( 'pingeroo_touch_image_sizes', array(192, 180, 152, 144, 120, 114, 76, 72, 57) );
}
function add_pingeroo_touch_icon_sizes() {
	//See https://mathiasbynens.be/notes/touch-icons#sizes
	$touch_icon_sizes = get_pingeroo_touch_image_sizes();
	foreach( $touch_icon_sizes as $width) {
		add_image_size( 'pingeroo-touch-icon-' . $width, $width, $width, true );
	}
}
add_pingeroo_touch_icon_sizes(); //Figure out a better place to put this.

function remove_pingeroo_touch_icon_sizes() {
	//See https://mathiasbynens.be/notes/touch-icons#sizes
	$touch_icon_sizes = get_pingeroo_touch_image_sizes();
	foreach( $touch_icon_sizes as $width) {
		remove_image_size( 'pingeroo-touch-icon-' . $width);
	}
}
function pingeroo_touch_icon_wp_head() {
	$options = get_pingeroo_options();
	if(
		!$options ||
		!isset( $options['touch-icon-id'] ) ||
		!$options['touch-icon-id']
	) {
		return false;
	}
	
	$sizes = get_pingeroo_touch_image_sizes();
	$touch_icon_id = $options['touch-icon-id'];
	
	$output = array("\n<!-- Pingeroo Touch Icons -->");
	foreach( $sizes as $size ) {
		$rel = 'apple-touch-icon-precomposed';
		if( $size == 192 ) {
			$rel = 'icon';
		}
		
		$img = wp_get_attachment_image_src( $touch_icon_id, 'pingeroo-touch-icon-' . $size );
		if( isset( $img[0] ) ) {
			$sizes = $size . 'x' . $size;
			$href = $img[0];
			
			$output[] = '<link rel="' . $rel . '" sizes="' . $sizes . '" href="' . $href . '">';
			
		}
	}
	
	$output = apply_filters( 'pingeroo_touch_icons', $output );
	$output[] = "\n\n";
	echo implode("\n", $output);
}
add_action( 'wp_head', 'pingeroo_touch_icon_wp_head' );

//We don't want all image uloads to be resized to touch icon sizes. If a file is being uploaded and $_FILES['pingeroo-touch-icon-file'] is not set then we remove the touch icon sizes so they aren't resized.
function pingeroo_wp_handle_upload_prefilter($file) {
	if( !isset( $_FILES['pingeroo-touch-icon-file'] ) ) {
		remove_pingeroo_touch_icon_sizes();
	}
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'pingeroo_wp_handle_upload_prefilter' );

function pingeroo_touch_icon_delete_attachment ($attachment_id) {
	//If the attachment being deleted is our Touch Icon attachment then we need to reflect that in the Pingeroo options.
	$options = get_pingeroo_options();
	if( !$options || !isset( $options['touch-icon-id'] ) ) {
		return;
	}
	
	if( $attachment_id == $options['touch-icon-id'] ) {
		$options['touch-icon-id'] = 0;
		update_option( 'pingeroo', $options );
	}
}
add_action( 'delete_attachment', 'pingeroo_touch_icon_delete_attachment' );