<?php 
	if ( !defined( 'WP_UNINSTALL_PLUGIN') )
		exit();
	delete_option('custom_roles_for_author');
		
?>