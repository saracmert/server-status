<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

$slug = 'server_status';

if ( !is_multisite() ) {
	$options = get_option('dashboard_widget_options');
	unset($options[$slug]);
	update_option('dashboard_widget_options', $options);
} else{
	global $wpdb;
	$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	$original_blog_id = get_current_blog_id();
	foreach ( $blog_ids as $blog_id ) 
	{
		switch_to_blog( $blog_id );
		$options = get_option('dashboard_widget_options');
		unset($options[$slug]);
		update_option('dashboard_widget_options', $options);
	}
	switch_to_blog( $original_blog_id );
	$options = get_site_option('dashboard_widget_options');
	unset($options[$slug]);
	update_site_option('dashboard_widget_options', $options);
}

?>