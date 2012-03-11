<?php
/**
 * Plugin Name: Custom roles for author
 * Plugin URI: http://david.binda.cz/plugin-custom-roles-for-author
 * Description: Allows to set users with custom role as post/page/custom post type author.
 * Author: David Biňovec
 * Author URI: http://david.binda.cz
 * Version: 1.0
 * License: GPLv3
 */

add_action('admin_menu', 'register_custom_roles_for_author_settins_submenu_page');

function register_custom_roles_for_author_settins_submenu_page() {
	add_submenu_page( 'users.php', 'Custom roles for author', 'Custom roles 4 author', 'manage_options', 'custom-roles-for-author', 'custom_roles_for_author_settins_page' );
}

function custom_roles_for_author_settins_page(){
	if ( !current_user_can( 'administrator' ) )
		return;
	if ( isset( $_POST['custom_roles_for_author_submit'] ) && wp_verify_nonce($_POST['custom_roles_for_author_nonce_field'],'custom_roles_for_author_submit') ){
		if ( !isset( $_POST['roles'] ) )
			$_POST['roles'] = '';
		update_option( 'custom_roles_for_author', maybe_serialize($_POST['roles']) );
	} ?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br/></div>
		<h2><?php _e('Custom roles for author Settings'); ?></h2>
		<?php $roles = maybe_unserialize( get_option( 'custom_roles_for_author' ) ); ?>
		<form method="post" action="">
		<?php wp_nonce_field('custom_roles_for_author_submit','custom_roles_for_author_nonce_field'); ?>
		<table class="form-table">
		<tbody>
		<tr valign="top">
		<th scope="row"><label for="roles[]"><?php _e('Roles to show in authors dropdown'); ?></label></th>
		<td>
		<label>
			<input type="checkbox" disabled="disabled" name="roles[]" value="administrator" checked="checked"/>
			Administrator
		</label><br/>
		<?php 
		global $wp_roles; 		
		$roles = maybe_unserialize( get_option( 'custom_roles_for_author' ) );
		if ( !is_array( $roles ) )
			$roles = array();
		foreach ($wp_roles->roles as $role_key => $role ) {
			$checked = "";
			if ( in_array($role_key, $roles) )
				$checked = ' checked="checked"'; 
			if ( $role_key == 'administrator' )
				continue;
		?>
		<label>
			<input type="checkbox" name="roles[]" value="<?php echo $role_key; ?>"<?php echo $checked; ?>/>
			<?php echo $role['name']; ?>
		</label><br/>
		<?php } ?>
		</td>
		</tr>
		</tbody></table>
		
		
		<p class="submit"><input type="submit" name="custom_roles_for_author_submit" id="submit" class="button-primary" value="<?php _e('Save Changes'); ?>"></p></form>
		
	</div>
	<div class="clear"></div>
<?php	
}

function list_custom_roles_to_asign_author_wp_dropdown_users( $output ) {
	$roles = maybe_unserialize( get_option( 'custom_roles_for_author' ) );
	if ( empty( $roles ) )
		return $output;	
	if ( !is_array( $roles ) )
		return $output; 	
	
	$defaults = array(
		'show_option_all' => '', 'show_option_none' => '', 'hide_if_only_one_author' => '',
		'orderby' => 'display_name', 'order' => 'ASC',
		'include' => '', 'exclude' => '', 'multi' => 0,
		'show' => 'display_name', 'echo' => 0,
		'name' => 'post_author_override', 'class' => '', 'id' => '',
		'blog_id' => $GLOBALS['blog_id'], 'who' => 'authors', 'include_selected' => true,
		'role' => $roles, 'selected' => false
	);
	global $post, $pagenow;
	if ($pagenow == 'post-new.php' || $pagenow == 'post.php' )
		$defaults['selected'] = empty($post->ID) ? $user_ID : $post->post_author;
	
			
	$args = '';	
	
	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );
	
	$name = esc_attr( $name );
		if ( $multi && ! $id )
			$id = '';
		else
			$id = $id ? " id='" . esc_attr( $id ) . "'" : " id='$name'";
		
	$query_args = wp_array_slice_assoc( $r, array( 'blog_id', 'include', 'exclude', 'orderby', 'order', 'role' ) );
	$query_args['fields'] = array( 'ID', $show );
	
	$queried_users = array();
	$output = "<select name='{$name}'{$id} class='$class'>\n";
	
	foreach ($query_args['role'] as $role) {
					
		$query_args_new['role'] = $role;
		$users = get_users( $query_args_new );	
		
		if ( !empty($users) && ( empty($hide_if_only_one_author) || count($users) > 1 ) ) {		
	
			if ( $show_option_all )
				$output .= "\t<option value='0'>$show_option_all</option>\n";
	
			if ( $show_option_none ) {
				$_selected = selected( -1, $selected, false );
				$output .= "\t<option value='-1'$_selected>$show_option_none</option>\n";
			}
	
			$found_selected = false;
			foreach ( (array) $users as $user ) {
				$user->ID = (int) $user->ID;
				$_selected = selected( $user->ID, $selected, false );
				if ( $_selected )
					$found_selected = true;
				$display = !empty($user->$show) ? $user->$show : '('. $user->user_login . ')';
				$queried_users[$user->ID] = "\t<option value='$user->ID'$_selected>" . esc_html($display) . "</option>\n";
			}
	
			if ( $include_selected && ! $found_selected && ( $selected > 0 ) ) {
				$user = get_userdata( $selected );
				$_selected = selected( $user->ID, $selected, false );
				$display = !empty($user->$show) ? $user->$show : '('. $user->user_login . ')';
				$queried_users[$user->ID] = "\t<option value='$user->ID'$_selected>" . esc_html($display) . "</option>\n";
			}
	
			
		}

	}	
	
	foreach ( $queried_users as $option ){
		$output .= $option;	
	}	
	
	$output .= "</select>";

	return $output;
}

add_action('wp_dropdown_users', 'list_custom_roles_to_asign_author_wp_dropdown_users', 0, 1);
?>