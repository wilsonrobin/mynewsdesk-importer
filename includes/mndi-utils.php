<?php

function get_mndi_option($name) {
    return carbon_get_theme_option($name);
}

function upload_from_url( $url, $title = null, $content = null, $alt = null ) {
	// require_once( ABSPATH . "/wp-load.php");
	require_once( ABSPATH . "/wp-admin/includes/image.php");
	require_once( ABSPATH . "/wp-admin/includes/file.php");
	require_once( ABSPATH . "/wp-admin/includes/media.php");
	
	// Download url to a temp file
	$tmp = download_url( $url );
	if ( is_wp_error( $tmp ) ) return false;
	
	// Get the filename and extension ("photo.png" => "photo", "png")
	$filename = pathinfo($url, PATHINFO_FILENAME);
	$extension = pathinfo($url, PATHINFO_EXTENSION);
	
	// An extension is required or else WordPress will reject the upload
	if ( ! $extension ) {
		// Look up mime type, example: "/photo.png" -> "image/png"
		$mime = mime_content_type( $tmp );
		$mime = is_string($mime) ? sanitize_mime_type( $mime ) : false;
		
		// Only allow certain mime types because mime types do not always end in a valid extension (see the .doc example below)
		$mime_extensions = array(
			// mime_type         => extension (no period)
			'text/plain'         => 'txt',
			'text/csv'           => 'csv',
			'application/msword' => 'doc',
			'image/jpg'          => 'jpg',
			'image/jpeg'         => 'jpeg',
			'image/gif'          => 'gif',
			'image/png'          => 'png',
			'video/mp4'          => 'mp4',
		);
		
		if ( isset( $mime_extensions[$mime] ) ) {
			// Use the mapped extension
			$extension = $mime_extensions[$mime];
		}else{
			// Could not identify extension. Clear temp file and abort.
			wp_delete_file($tmp);
			return false;
		}
	}
	
	// Upload by "sideloading": "the same way as an uploaded file is handled by media_handle_upload"
	$args = array(
		'name' => "$filename.$extension",
		'tmp_name' => $tmp,
	);
	
	// Post data to override the post title, content, and alt text
	$post_data = array();
	if ( $title )   $post_data['post_title'] = $title;
	if ( $content ) $post_data['post_content'] = $content;
	
	// Do the upload
	$attachment_id = media_handle_sideload( $args, 0, null, $post_data );
    
    var_dump($attachment_id);
	
	// Clear temp file
	wp_delete_file($tmp);
	
	// Error uploading
	if ( is_wp_error($attachment_id) ) return false;
	
	// Save alt text as post meta if provided
	if ( $alt ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt );
	}
	
	// Success, return attachment ID
	return (int) $attachment_id;
}