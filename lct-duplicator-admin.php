<?php
// Added by WarmStal
if(!is_admin())
	return;

require_once (dirname(__FILE__).'/lct-duplicator-options.php');

include_once (dirname(__FILE__).'/compat/lct-duplicator-wpml.php');
include_once (dirname(__FILE__).'/compat/lct-duplicator-jetpack.php');

/**
 * Wrapper for the option 'lct_duplicator_version'
*/
function lct_duplicator_get_installed_version() {
	return get_option( 'lct_duplicator_version' );
}

/**
 * Wrapper for the defined constant LCT_DUPLICATOR_CURRENT_VERSION
 */
function lct_duplicator_get_current_version() {
	return LCT_DUPLICATOR_CURRENT_VERSION;
}


add_action('admin_init','lct_duplicator_admin_init');

function lct_duplicator_admin_init(){
	lct_duplicator_plugin_upgrade();

    if (get_option('lct_duplicator_show_row') == 1){
		add_filter('post_row_actions', 'lct_duplicator_make_duplicate_link_row',10,2);
		add_filter('page_row_actions', 'lct_duplicator_make_duplicate_link_row',10,2);
	}
		
	if (get_option('lct_duplicator_show_submitbox') == 1){
		add_action( 'post_submitbox_start', 'lct_duplicator_add_lct_duplicator_button' );
	}


	if(get_option('lct_duplicator_show_original_column') == 1){
		lct_duplicator_show_original_column();
	}

	if(get_option('lct_duplicator_show_original_in_post_states') == 1){
		add_filter( 'display_post_states', 'lct_duplicator_show_original_in_post_states', 10, 2);
	}

	if(get_option('lct_duplicator_show_original_meta_box') == 1){
		add_action('add_meta_boxes', 'lct_duplicator_add_custom_box');
		add_action( 'save_post', 'lct_duplicator_save_quick_edit_data' );
	}

    $user = wp_get_current_user();
    $roles = $user->roles[0];
    $role_check = in_array( $roles, (array) get_option('lct_duplicator_roles')) ;

    if($role_check) {
        $lct_duplicator_types_enabled = get_option( 'lct_duplicator_types_enabled' );

        if (is_array($lct_duplicator_types_enabled)){
            if(in_array('menu', $lct_duplicator_types_enabled)){
                // Add the Duplicate Menu button to the nav-menus admin-page
                add_action('admin_footer', 'lct_duplicate_menu_button', 10);

                // Redirect the nav-menus to the required plugin pages
                add_action('admin_footer', 'lct_clone_duplicate', 5);
            }

            if(in_array('widget', $lct_duplicator_types_enabled)){
                add_filter('admin_head', 'lct_enqueue_duplicate_widgets_script');
            }

            if(in_array('attachment', $lct_duplicator_types_enabled)){
                add_filter('media_row_actions', 'lct_media_row_action', 10, 2);
                lct_duplicate_file();
            }
        }
    }

	/**
	 * Connect actions to functions
	 */
	add_action('admin_action_lct_duplicator_save_as_new_post', 'lct_duplicator_save_as_new_post');
	add_action('admin_action_lct_duplicator_save_as_new_post_draft', 'lct_duplicator_save_as_new_post_draft');
	
	add_filter('removable_query_args', 'lct_duplicator_add_removable_query_arg', 10, 1);
	
	// Using our action hooks
	
	add_action('dp_lct_duplicator', 'lct_duplicator_copy_post_meta_info', 10, 2);
	add_action('dp_duplicate_page', 'lct_duplicator_copy_post_meta_info', 10, 2);
	
	if(get_option('lct_duplicator_copychildren') == 1){
		add_action('dp_lct_duplicator', 'lct_duplicator_copy_children', 20, 3);
		add_action('dp_duplicate_page', 'lct_duplicator_copy_children', 20, 3);
	}
	
	if(get_option('lct_duplicator_copyattachments') == 1){
		add_action('dp_lct_duplicator', 'lct_duplicator_copy_attachments', 30, 2);
		add_action('dp_duplicate_page', 'lct_duplicator_copy_attachments', 30, 2);
	}
	
	if(get_option('lct_duplicator_copycomments') == 1){
		add_action('dp_lct_duplicator', 'lct_duplicator_copy_comments', 40, 2);
		add_action('dp_duplicate_page', 'lct_duplicator_copy_comments', 40, 2);
	}
	
	add_action('dp_lct_duplicator', 'lct_duplicator_copy_post_taxonomies', 50, 2);
	add_action('dp_duplicate_page', 'lct_duplicator_copy_post_taxonomies', 50, 2);
	add_action( 'admin_notices', 'lct_duplicator_action_admin_notice' );
}


/**
 * Plugin upgrade
 */
function lct_duplicator_plugin_upgrade() {
	$installed_version = lct_duplicator_get_installed_version();
	
	if ( $installed_version == lct_duplicator_get_current_version() )
		return;

	if (empty($installed_version)) {
		// Get default roles
			$default_roles = array(
				3 => 'editor',
				8 => 'administrator',
		);
		
		// Cycle all roles and assign capability if its level >= lct_duplicator_copy_user_level
		foreach ($default_roles as $level => $name){
			$role = get_role($name);
			if(!empty($role)) $role->add_cap( 'copy_posts' );
		}
	} else {
		$min_user_level = get_option('lct_duplicator_copy_user_level');
			
		if (!empty($min_user_level)){
			// Get default roles
			$default_roles = array(
					1 => 'contributor',
					2 => 'author',
					3 => 'editor',
					8 => 'administrator',
			);
				
			// Cycle all roles and assign capability if its level >= lct_duplicator_copy_user_level
			foreach ($default_roles as $level => $name){
				$role = get_role($name);
				if ($role && $min_user_level <= $level)
					$role->add_cap( 'copy_posts' );
			}
			delete_option('lct_duplicator_copy_user_level');
		}
	}
	
	add_option('lct_duplicator_copytitle','1');
	add_option('lct_duplicator_copydate','0');
	add_option('lct_duplicator_copystatus','0');
	add_option('lct_duplicator_copyslug','0');
	add_option('lct_duplicator_copyexcerpt','1');
	add_option('lct_duplicator_copycontent','1');
	add_option('lct_duplicator_copythumbnail','1');
	add_option('lct_duplicator_copytemplate','1');
	add_option('lct_duplicator_copyformat','1');
	add_option('lct_duplicator_copyauthor','0');
	add_option('lct_duplicator_copypassword','0');
	add_option('lct_duplicator_copyattachments','0');
	add_option('lct_duplicator_copychildren','0');
	add_option('lct_duplicator_copycomments','0');
	add_option('lct_duplicator_copymenuorder','1');
	add_option('lct_duplicator_taxonomies_blacklist',array());
	add_option('lct_duplicator_blacklist','');
	add_option('lct_duplicator_types_enabled',array('post', 'page'));
	add_option('lct_duplicator_show_row','1');
	add_option('lct_duplicator_show_adminbar','1');
	add_option('lct_duplicator_show_submitbox','1');
	add_option('lct_duplicator_show_bulkactions','1');
	add_option('lct_duplicator_show_original_column','0');
	add_option('lct_duplicator_show_original_in_post_states','0');
	add_option('lct_duplicator_show_original_meta_box','0');
	
	$taxonomies_blacklist = get_option('lct_duplicator_taxonomies_blacklist');
	if ($taxonomies_blacklist == "") $taxonomies_blacklist = array();

	if(in_array('post_format',$taxonomies_blacklist)){
		update_option('lct_duplicator_copyformat', 0);
		$taxonomies_blacklist = array_diff($taxonomies_blacklist, array('post_format'));
		update_option('lct_duplicator_taxonomies_blacklist', $taxonomies_blacklist);
	}
	
	$meta_blacklist = explode(",",get_option('lct_duplicator_blacklist'));

	if ($meta_blacklist == "") $meta_blacklist = array();

	$meta_blacklist = array_map('trim', $meta_blacklist);

	if(in_array('_wp_page_template', $meta_blacklist)){
		update_option('lct_duplicator_copytemplate', 0);
		$meta_blacklist = array_diff($meta_blacklist, array('_wp_page_template'));	
	}	

	if(in_array('_thumbnail_id', $meta_blacklist)){
		update_option('lct_duplicator_copythumbnail', 0);
		$meta_blacklist = array_diff($meta_blacklist, array('_thumbnail_id'));
	}

	update_option('lct_duplicator_blacklist', implode(',',$meta_blacklist));

	delete_option('lct_duplicator_admin_user_level');
	delete_option('lct_duplicator_create_user_level');
	delete_option('lct_duplicator_view_user_level');
	delete_option('dp_notice');
	
	delete_site_option('lct_duplicator_version');
	update_option( 'lct_duplicator_version', lct_duplicator_get_current_version() );
}

function lct_duplicator_show_original_column() {
	$lct_duplicator_types_enabled = get_option( 'lct_duplicator_types_enabled', array( 'post', 'page' ) );
	if ( ! is_array( $lct_duplicator_types_enabled ) ) {
		$lct_duplicator_types_enabled = array( $lct_duplicator_types_enabled );
	}

	if ( count( $lct_duplicator_types_enabled ) ) {
		foreach ( $lct_duplicator_types_enabled as $enabled_post_type ) {
			add_filter( "manage_{$enabled_post_type}_posts_columns", 'lct_duplicator_add_original_column' );
			add_action( "manage_{$enabled_post_type}_posts_custom_column", 'lct_duplicator_show_original_item', 10, 2 );
		}

		add_action( 'quick_edit_custom_box', 'lct_duplicator_quick_edit_remove_original', 10, 2 );
		add_action( 'save_post', 'lct_duplicator_save_quick_edit_data' );
		add_action( 'admin_enqueue_scripts', 'lct_duplicator_admin_enqueue_scripts' );
	}
}

function lct_duplicator_add_original_column( $post_columns ) {
	$post_columns['lct_duplicator_original_item'] = __( 'Original item', 'lct-duplicator' );
	return $post_columns;
}

function lct_duplicator_show_original_item( $column_name, $post_id ) {
	if ( 'lct_duplicator_original_item' === $column_name ) {
		$column_value = '<span data-no_original>-</span>';
		$original_item = lct_duplicator_get_original( $post_id );
		if ( $original_item ) {
			$column_value = lct_duplicator_get_edit_or_view_link( $original_item );
		}
		echo $column_value;
	}
}

function lct_duplicator_quick_edit_remove_original( $column_name, $post_type ) {
	if ( 'lct_duplicator_original_item' != $column_name ) {
		return;
	}

	printf(
'<fieldset class="inline-edit-col-right" id="lct_duplicator_quick_edit_fieldset">
			<div class="inline-edit-col">
        		<label class="alignleft">
					<input type="checkbox" name="lct_duplicator_remove_original" value="lct_duplicator_remove_original">
					<span class="checkbox-title">%s</span>
				</label>
			</div>
		</fieldset>',
		__(
			'Delete reference to original item: <span class="lct_duplicator_original_item_title_span"></span>',
			'lct-duplicator'
			)
	);
}

function lct_duplicator_save_quick_edit_data( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}

	if ( ! empty( $_POST['lct_duplicator_remove_original'] ) ) {
		delete_post_meta( $post_id, '_dp_original' );
	}
}

function lct_duplicator_show_original_in_post_states( $post_states, $post ){
	$original_item = lct_duplicator_get_original( $post->ID );

	if ( $original_item ) {
		// translators: Original item link (to view or edit) or title.
		$post_states['lct_duplicator_original_item'] = sprintf( __( 'Original: %s', 'lct-duplicator' ), lct_duplicator_get_edit_or_view_link( $original_item ) );
	}

	return $post_states;
}

function lct_duplicator_admin_enqueue_scripts( $hook ) {
	if ( 'edit.php' === $hook ) {
		wp_enqueue_script( 'lct_duplicator_admin_script', plugin_dir_url(__FILE__). 'assets/js/lct_duplicator_admin_script.js', false, LCT_DUPLICATOR_CURRENT_VERSION, true );
	}
}

function lct_duplicator_add_custom_box(){
	$screens = get_option('lct_duplicator_types_enabled');

	if(!is_array($screens)) $screens = array($screens);

	foreach ($screens as $screen) {
		add_meta_box(
			'lct_duplicator_show_original',           // Unique ID
			'Duplicate Post',  // Box title
			'lct_duplicator_custom_box_html',  // Content callback, must be of type callable
			$screen,                   // Post type
			'side'
		);
	}
}

function lct_duplicator_custom_box_html( $post ) {
	$original_item = lct_duplicator_get_original( $post->ID );
	if ( $original_item ) {
	?>
	<label>
		<input type="checkbox" name="lct_duplicator_remove_original" value="lct_duplicator_remove_original">
		<?php printf( __( 'Delete reference to original item: <span class="lct_duplicator_original_item_title_span">%s</span>', 'lct-duplicator' ), lct_duplicator_get_edit_or_view_link( $original_item ) ); ?>
	</label>
	<?php
	} else { ?>
		<script>
			(function(jQuery){
				jQuery('#lct_duplicator_show_original').hide();
			})(jQuery);
		</script>
	<?php }
}

/**
 * Add the link to action list for post_row_actions
 */
function lct_duplicator_make_duplicate_link_row($actions, $post) {
	if (lct_duplicator_is_current_user_allowed_to_copy() && lct_duplicator_is_post_type_enabled($post->post_type)) {
		$title = _draft_or_post_title( $post );
		$actions['clone'] = '<a href="'.lct_duplicator_get_clone_post_link( $post->ID , 'display', false).'" aria-label="'
				. esc_attr( sprintf( __('Clone &#8220;%s&#8221;', 'lct-duplicator'), $title ) )
				. '">' .  esc_html__('Clone', 'lct-duplicator') . '</a>';
	}
	return $actions;
}

/**
 * Add a button in the post/page edit screen to create a clone
 */
function lct_duplicator_add_lct_duplicator_button() {
	if ( isset( $_GET['post'] )){
		$id = absint($_GET['post']);
		$post = get_post($id);
		if(lct_duplicator_is_current_user_allowed_to_copy() && lct_duplicator_is_post_type_enabled($post->post_type)) {
	 	?>
    <div id="duplicate-action">
        <a class="submitduplicate duplication"
            href="<?php echo esc_url( lct_duplicator_get_clone_post_link( $id ) ); ?>"><?php esc_html_e('Copy to a new draft', 'lct-duplicator'); ?>
        </a>
    </div>
    <?php
		}
	}
}

/*
 * This function calls the creation of a new copy of the selected post (as a draft)
* then redirects to the edit post screen
*/
function lct_duplicator_save_as_new_post_draft(){
	lct_duplicator_save_as_new_post('draft');
}

function lct_duplicator_add_removable_query_arg( $removable_query_args ){
	$removable_query_args[] = 'cloned';
	return $removable_query_args;
}

/*
 * This function calls the creation of a new copy of the selected post (by default preserving the original publish status)
* then redirects to the post list
*/
function lct_duplicator_save_as_new_post($status = ''){
	if(!lct_duplicator_is_current_user_allowed_to_copy()){
		wp_die(esc_html__('Current user is not allowed to copy posts.', 'lct-duplicator'));
	}
	
	if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'lct_duplicator_save_as_new_post' == $_REQUEST['action'] ) ) ) {
		wp_die(esc_html__('No post to duplicate has been supplied!', 'lct-duplicator'));
	}

	// Get the original post
	$id = (isset($_GET['post']) ? absint($_GET['post']) : absint($_POST['post']));
	
	check_admin_referer('lct-duplicator_' . $id);
	
	$post = get_post($id);	

	// Copy the post and insert it
	if (isset($post) && $post!=null) {
		$post_type = $post->post_type;
		$new_id = lct_duplicator_create_duplicate($post, $status);
		
		if ($status == ''){
			$sendback = wp_get_referer();
			if ( ! $sendback ||
					strpos( $sendback, 'post.php' ) !== false ||
					strpos( $sendback, 'post-new.php' ) !== false ) {
						if ( 'attachment' == $post_type ) {
							$sendback = admin_url( 'upload.php' );
						} else {
							$sendback = admin_url( 'edit.php' );
							if ( ! empty( $post_type ) ) {
								$sendback = add_query_arg( 'post_type', $post_type, $sendback );
							}
						}
					} else {
						$sendback = remove_query_arg( array('trashed', 'untrashed', 'deleted', 'cloned', 'ids'), $sendback );
					}
			// Redirect to the post list screen
			wp_redirect( add_query_arg( array( 'cloned' => 1, 'ids' => $post->ID), $sendback ) );
		} else {
			// Redirect to the edit screen for the new draft post
			wp_redirect( add_query_arg( array( 'cloned' => 1, 'ids' => $post->ID), admin_url( 'post.php?action=edit&post=' . $new_id ) ) );
		}
		exit;

	} else {
		wp_die(esc_html__('Copy creation failed, could not find original:', 'lct-duplicator') . ' ' . htmlspecialchars($id));
	}
}

/**
 * Copy the taxonomies of a post to another post
 */
function lct_duplicator_copy_post_taxonomies($new_id, $post) {
	global $wpdb;

	if (isset($wpdb->terms)) {
		// Clear default category (added by wp_insert_post)
		wp_set_object_terms( $new_id, NULL, 'category' );

		$post_taxonomies = get_object_taxonomies($post->post_type);
		// several plugins just add support to post-formats but don't register post_format taxonomy
		if(post_type_supports($post->post_type, 'post-formats') && !in_array('post_format', $post_taxonomies)){
			$post_taxonomies[] = 'post_format';
		}
		
		$taxonomies_blacklist = get_option('lct_duplicator_taxonomies_blacklist');

		if ($taxonomies_blacklist == "") $taxonomies_blacklist = array();

		if(get_option('lct_duplicator_copyformat') == 0){
			$taxonomies_blacklist[] = 'post_format';
		}

		$taxonomies = array_diff($post_taxonomies, $taxonomies_blacklist);

		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post->ID, $taxonomy, array( 'orderby' => 'term_order' ));
			$terms = array();
			for ($i=0; $i<count($post_terms); $i++) {
				$terms[] = $post_terms[$i]->slug;
			}
			wp_set_object_terms($new_id, $terms, $taxonomy);
		}
	}
}

/**
 * Copy the meta information of a post to another post
*/
function lct_duplicator_copy_post_meta_info($new_id, $post) {
	$post_meta_keys = get_post_custom_keys($post->ID);

	if (empty($post_meta_keys)) return;

	$meta_blacklist = get_option('lct_duplicator_blacklist');

	if ($meta_blacklist == ""){
		$meta_blacklist = array();
	} else {
		$meta_blacklist = explode(',', $meta_blacklist);
		$meta_blacklist = array_filter($meta_blacklist);
		$meta_blacklist = array_map('trim', $meta_blacklist);
	}

	$meta_blacklist[] = '_edit_lock'; // edit lock
	$meta_blacklist[] = '_edit_last'; // edit lock

    if(get_option('lct_duplicator_copytemplate') == 0){
		$meta_blacklist[] = '_wp_page_template';
	}

    if(get_option('lct_duplicator_copythumbnail') == 0){
		$meta_blacklist[] = '_thumbnail_id';
	}
	
	$meta_blacklist = apply_filters( 'lct_duplicator_blacklist_filter' , $meta_blacklist );
	
	$meta_blacklist_string = '('.implode(')|(',$meta_blacklist).')';

	if(strpos($meta_blacklist_string, '*') !== false){
		$meta_blacklist_string = str_replace(array('*'), array('[a-zA-Z0-9_]*'), $meta_blacklist_string);
	
		$meta_keys = array();
		foreach($post_meta_keys as $meta_key){
			if(!preg_match('#^'.$meta_blacklist_string.'$#', $meta_key))
				$meta_keys[] = $meta_key;
		}
	} else {
		$meta_keys = array_diff($post_meta_keys, $meta_blacklist);
	}

	$meta_keys = apply_filters( 'lct_duplicator_meta_keys_filter', $meta_keys );

	foreach ($meta_keys as $meta_key) {
		$meta_values = get_post_custom_values($meta_key, $post->ID);
		foreach ($meta_values as $meta_value) {
			$meta_value = maybe_unserialize($meta_value);
			add_post_meta($new_id, $meta_key, lct_duplicator_wp_slash($meta_value));
		}
	}
}

/*
 * Workaround for inconsistent wp_slash.
 * Works only with WP 4.4+ (map_deep)
 */
function lct_duplicator_addslashes_deep( $value ) {
	if (function_exists('map_deep')){
		return map_deep( $value, 'lct_duplicator_addslashes_to_strings_only' );
	} else {
		return wp_slash( $value );
	}
}

function lct_duplicator_addslashes_to_strings_only( $value ) {
	return is_string( $value ) ? addslashes( $value ) : $value;
}

function lct_duplicator_wp_slash( $value ) { 
	return lct_duplicator_addslashes_deep( $value ); 
} 
		
/**
 * Copy the attachments
*/
function lct_duplicator_copy_attachments($new_id, $post){
	// get thumbnail ID
	$old_thumbnail_id = get_post_thumbnail_id($post->ID);
	// get children
	$children = get_posts(array( 'post_type' => 'any', 'numberposts' => -1, 'post_status' => 'any', 'post_parent' => $post->ID ));
	// clone old attachments
	foreach($children as $child){
		if ($child->post_type != 'attachment') continue;
		$url = wp_get_attachment_url($child->ID);
		// Let's copy the actual file
		$tmp = download_url( $url );
		if( is_wp_error( $tmp ) ) {
			@unlink($tmp);
			continue;
		}

		$desc = wp_slash($child->post_content);

		$file_array = array();
		$file_array['name'] = basename($url);
		$file_array['tmp_name'] = $tmp;
		// "Upload" to the media collection
		$new_attachment_id = media_handle_sideload( $file_array, $new_id, $desc );

		if ( is_wp_error($new_attachment_id) ) {
			@unlink($file_array['tmp_name']);
			continue;
		}
		$new_post_author = wp_get_current_user();
		$cloned_child = array(
				'ID'           => $new_attachment_id,
				'post_title'   => $child->post_title,
				'post_excerpt' => $child->post_excerpt,
				'post_content' => $child->post_content,
				'post_author'  => $new_post_author->ID
		);
		wp_update_post( wp_slash($cloned_child) );

		$alt_title = get_post_meta($child->ID, '_wp_attachment_image_alt', true);
		if($alt_title) update_post_meta($new_attachment_id, '_wp_attachment_image_alt', wp_slash($alt_title));

		// if we have cloned the post thumbnail, set the copy as the thumbnail for the new post
		if(get_option('lct_duplicator_copythumbnail') == 1 && $old_thumbnail_id == $child->ID){
				set_post_thumbnail($new_id, $new_attachment_id);
		}
		
	}
}

/**
 * Copy children posts
 */
function lct_duplicator_copy_children($new_id, $post, $status = ''){
	// get children
	$children = get_posts(array( 'post_type' => 'any', 'numberposts' => -1, 'post_status' => 'any', 'post_parent' => $post->ID ));
	// clone old attachments
	foreach($children as $child){
		if ($child->post_type == 'attachment') continue;
		lct_duplicator_create_duplicate($child, $status, $new_id);
	}
}

/**
 * Copy comments
 */
function lct_duplicator_copy_comments($new_id, $post){
	$comments = get_comments(array(
		'post_id' => $post->ID,
		'order' => 'ASC',
		'orderby' => 'comment_date_gmt'
	));

	$old_id_to_new = array();
	foreach ($comments as $comment){
		//do not copy pingbacks or trackbacks
		if(!empty($comment->comment_type)) continue;
		$parent = ($comment->comment_parent && $old_id_to_new[$comment->comment_parent])?$old_id_to_new[$comment->comment_parent]:0;
		$commentdata = array(
			'comment_post_ID' => $new_id,
			'comment_author' => $comment->comment_author,
			'comment_author_email' => $comment->comment_author_email,
			'comment_author_url' => $comment->comment_author_url,
			'comment_content' => $comment->comment_content,
			'comment_type' => '', 
			'comment_parent' => $parent,
			'user_id' => $comment->user_id,
			'comment_author_IP' => $comment->comment_author_IP,
			'comment_agent' => $comment->comment_agent,
			'comment_karma' => $comment->comment_karma,
			'comment_approved' => $comment->comment_approved,
		);
		if(get_option('lct_duplicator_copydate') == 1){
			$commentdata['comment_date'] = $comment->comment_date ;
			$commentdata['comment_date_gmt'] = get_gmt_from_date($comment->comment_date);
		}
		$new_comment_id = wp_insert_comment($commentdata);
		$old_id_to_new[$comment->comment_ID] = $new_comment_id;
	}
}

/**
 * Create a duplicate from a post
 */
function lct_duplicator_create_duplicate($post, $status = '', $parent_id = '') {
	
	do_action('lct_duplicator_pre_copy');

	if (!lct_duplicator_is_post_type_enabled($post->post_type) && $post->post_type != 'attachment')
		wp_die(esc_html__('Copy features for this post type are not enabled in options page', 'lct-duplicator'));
		
	$new_post_status = (empty($status))? $post->post_status: $status;
	
	if ($post->post_type != 'attachment'){
		$prefix = sanitize_text_field(get_option('lct_duplicator_title_prefix'));
		$suffix = sanitize_text_field(get_option('lct_duplicator_title_suffix'));
		$title = ' ';
		if (get_option('lct_duplicator_copytitle') == 1) {
			$title = $post->post_title;
			if (!empty($prefix)) $prefix.= " ";
			if (!empty($suffix)) $suffix = " ".$suffix;
			} else {
			$title = ' ';
		}
		$title = trim($prefix.$title.$suffix);

		if ($title == ''){
			// empty title
			$title = __('Untitled', 'default');
		}
		if (get_option('lct_duplicator_copystatus') == 0){
			$new_post_status = 'draft';
		} else {
			if ( 'publish' == $new_post_status || 'future' == $new_post_status ){
				// check if the user has the right capability
				if(is_post_type_hierarchical( $post->post_type )){
					if(!current_user_can('publish_pages')){
						$new_post_status = 'pending';
					}
				} else {
					if(!current_user_can('publish_posts')){
						$new_post_status = 'pending';
					}
				}
			}
		}
	}	
	
	$new_post_author = wp_get_current_user();
	$new_post_author_id = $new_post_author->ID;
	if ( get_option('lct_duplicator_copyauthor') == '1' ){
		// check if the user has the right capability
		if(is_post_type_hierarchical( $post->post_type )){
			if(current_user_can('edit_others_pages')){
				$new_post_author_id = $post->post_author;
			}
		} else {
			if(current_user_can('edit_others_posts')){
				$new_post_author_id = $post->post_author;
			}
		}
	}

	$menu_order = (get_option('lct_duplicator_copymenuorder') == '1') ? $post->menu_order : 0;
	$increase_menu_order_by = get_option('lct_duplicator_increase_menu_order_by');
	if(!empty($increase_menu_order_by) && is_numeric($increase_menu_order_by)){
		$menu_order += intval($increase_menu_order_by);
	}
	
	$post_name = $post->post_name;
	if(get_option('lct_duplicator_copyslug') != 1){
		$post_name = '';
	}

	$new_post = array(
	'menu_order' => $menu_order,
	'comment_status' => $post->comment_status,
	'ping_status' => $post->ping_status,
	'post_author' => $new_post_author_id,
	'post_content' => (get_option('lct_duplicator_copycontent') == '1') ? $post->post_content : "" ,
	'post_content_filtered' => (get_option('lct_duplicator_copycontent') == '1') ? $post->post_content_filtered : "" ,			
	'post_excerpt' => (get_option('lct_duplicator_copyexcerpt') == '1') ? $post->post_excerpt : "",
	'post_mime_type' => $post->post_mime_type,
	'post_parent' => $new_post_parent = empty($parent_id)? $post->post_parent : $parent_id,
	'post_password' => (get_option('lct_duplicator_copypassword') == '1') ? $post->post_password: "",
	'post_status' => $new_post_status,
	'post_title' => $title,
	'post_type' => $post->post_type,
	'post_name' => $post_name
	);

	if(get_option('lct_duplicator_copydate') == 1){
		$new_post['post_date'] = $new_post_date =  $post->post_date ;
		$new_post['post_date_gmt'] = get_gmt_from_date($new_post_date);
	}

	$new_post_id = wp_insert_post(wp_slash($new_post));

	// If you have written a plugin which uses non-WP database tables to save
	// information about a post you can hook this action to dupe that data.
	
	if($new_post_id !== 0 && !is_wp_error($new_post_id)){
		
		if ($post->post_type == 'page' || is_post_type_hierarchical( $post->post_type ))
			do_action( 'dp_duplicate_page', $new_post_id, $post, $status );
		else
			do_action( 'dp_lct_duplicator', $new_post_id, $post, $status );
	
		delete_post_meta($new_post_id, '_dp_original');
		add_post_meta($new_post_id, '_dp_original', $post->ID);
	
		do_action('lct_duplicator_post_copy');
		
	}
	
	return $new_post_id;
}

/*** NOTICES ***/
function lct_duplicator_action_admin_notice() {
  if ( ! empty( $_REQUEST['cloned'] ) ) {
    $copied_posts = intval( $_REQUEST['cloned'] );
    printf( '<div id="message" class="updated fade"><p>' .
      _n( '%s item copied.',
        '%s items copied.',
        $copied_posts,
        'lct-duplicator'
      ) . '</p></div>', $copied_posts );
    remove_query_arg( 'cloned' );
  }
}


/*** BULK ACTIONS ***/
add_action('admin_init', 'lct_duplicator_add_bulk_filters_for_enabled_post_types');

function lct_duplicator_add_bulk_filters_for_enabled_post_types(){
	if(get_option('lct_duplicator_show_bulkactions') != 1) return;
	$lct_duplicator_types_enabled = get_option('lct_duplicator_types_enabled', array ('post', 'page'));
	if(!is_array($lct_duplicator_types_enabled)) $lct_duplicator_types_enabled = array($lct_duplicator_types_enabled);
	foreach($lct_duplicator_types_enabled as $lct_duplicator_type_enabled){
		add_filter( "bulk_actions-edit-{$lct_duplicator_type_enabled}", 'lct_duplicator_register_bulk_action' );
		add_filter( "handle_bulk_actions-edit-{$lct_duplicator_type_enabled}", 'lct_duplicator_action_handler', 10, 3 );
	}
}

function lct_duplicator_register_bulk_action($bulk_actions) {
	$bulk_actions['lct_duplicator_clone'] = esc_html__( 'Clone', 'lct-duplicator');
	return $bulk_actions;
}

function lct_duplicator_action_handler( $redirect_to, $doaction, $post_ids ) {
	if ( $doaction !== 'lct_duplicator_clone' ) {
		return $redirect_to;
	}
	$counter = 0;
	foreach ( $post_ids as $post_id ) {
		$post = get_post($post_id);
		if(!empty($post)){
			if( get_option('lct_duplicator_copychildren') != 1
					|| !is_post_type_hierarchical( $post->post_type )
					|| (is_post_type_hierarchical( $post->post_type ) && !lct_duplicator_has_ancestors_marked($post, $post_ids))){
						if(lct_duplicator_create_duplicate($post)){
							$counter++;
						}
			}
		}
	}
	$redirect_to = add_query_arg( 'cloned', $counter, $redirect_to );
	return $redirect_to;
}

function lct_duplicator_has_ancestors_marked($post, $post_ids){
	$ancestors_in_array = 0;
	$parent = $post->ID;
	while ($parent = wp_get_post_parent_id($parent)){
		if(in_array($parent, $post_ids)){
			$ancestors_in_array++;
		}
	}
	return ($ancestors_in_array !== 0);
}

function lct_duplicate_menu_button(){
    $current_screen = get_current_screen();
    $current_menu   = get_user_option ( 'nav_menu_recently_edited' );
    if ($current_screen->id == 'nav-menus' && $_GET['menu'] != '0') {
        $return = '';
        $return.= '<a class="button button-large" href="?duplicate='.$current_menu.'">'.__('Duplicate Menu', 'lct-duplicator').'</a>';
        ?>
        <script type="text/javascript">
            var update_menu_form = jQuery('#update-nav-menu');
            update_menu_form.find('.publishing-action').append('<?php echo addslashes_gpc($return); ?>');
        </script>

        <?php
    }
}

function lct_clone_duplicate(){
    $current_screen = get_current_screen();
    if ($current_screen->id == 'nav-menus') {
        if (isset($_GET['duplicate'])) {
            $id = intval( $_GET['duplicate'] );
            $source = wp_get_nav_menu_object($id);
            $prefix = sanitize_text_field(get_option('lct_duplicator_title_prefix'));
            $suffix = sanitize_text_field(get_option('lct_duplicator_title_suffix'));

            if(!empty($prefix) || !empty($suffix))
            {
                $duplicate = lct_duplicate($id, $prefix.' '.$source->name.' '.$suffix );
            }else{
                $duplicate = lct_duplicate($id, $source->name. ' ' .__('(Copy)', 'lct-duplicator'));
            }

            if ($duplicate) {
                ?>
                <script type="text/javascript">
                    window.location.replace("<?php echo admin_url( 'nav-menus.php?action=edit&menu='.$duplicate ); ?>");
                </script>
                <?php
            } else {
                ?>
                <script type="text/javascript">
                    window.location.replace("<?php echo admin_url( 'nav-menus.php' ); ?>");
                </script>
                <?php
            }
        }
    }
}

function lct_duplicate( $id = null, $name = null ){

    // sanity check
    if ( empty( $id ) || empty( $name ) ) {
        return false;
    }

    $id = intval( $id );
    $name = sanitize_text_field( $name );
    $source = wp_get_nav_menu_object( $id );
    $source_items = wp_get_nav_menu_items( $id );
    $new_id = wp_create_nav_menu( $name );

    if ( ! $new_id ) {
        return false;
    }

    // key is the original db ID, val is the new
    $rel = array();

    $increment = 1;
    foreach ( $source_items as $menu_item ) {
        $args = array(
            'menu-item-db-id'       => $menu_item->db_id,
            'menu-item-object-id'   => $menu_item->object_id,
            'menu-item-object'      => $menu_item->object,
            'menu-item-position'    => $increment,
            'menu-item-type'        => $menu_item->type,
            'menu-item-title'       => $menu_item->title,
            'menu-item-url'         => $menu_item->url,
            'menu-item-description' => $menu_item->description,
            'menu-item-attr-title'  => $menu_item->attr_title,
            'menu-item-target'      => $menu_item->target,
            'menu-item-classes'     => implode( ' ', $menu_item->classes ),
            'menu-item-xfn'         => $menu_item->xfn,
            'menu-item-status'      => $menu_item->post_status
        );

        $parent_id = wp_update_nav_menu_item( $new_id, 0, $args );

        $rel[$menu_item->db_id] = $parent_id;

        // did it have a parent? if so, we need to update with the NEW ID
        if ( $menu_item->menu_item_parent ) {
            $args['menu-item-parent-id'] = $rel[$menu_item->menu_item_parent];
            $parent_id = wp_update_nav_menu_item( $new_id, $parent_id, $args );
        }

        // allow developers to run any custom functionality they'd like
        do_action( 'duplicate_menu_item', $menu_item, $args );

        $increment++;
    }

    return $new_id;
}

function lct_enqueue_duplicate_widgets_script(){
    global $pagenow;

    if ($pagenow != 'widgets.php')
        return;

    wp_enqueue_script('lct_duplicate_widgets_script', plugin_dir_url(__FILE__) . 'assets/js/lct_duplicator-widgets.js', array('jquery'), LCT_DUPLICATOR_CURRENT_VERSION, true);

    wp_localize_script('lct_duplicate_widgets_script', 'lct_duplicate_widgets', array(
        'text' => __('Clone', 'lct-duplicate-widgets'),
        'title' => __('Clone this Widget', 'lct-duplicate-widgets')
    ));
}

function lct_media_row_action($actions, $post)
{
    $actions['lct_copy_media_file_link'] = '<a href="' .add_query_arg('lct-copy-media-file-postid', $post->ID) . '" title="' . __('create a new copy of this file', 'lct-duplicator') . '" class="lct_copy_media_file_link">' . __('Clone', 'lct-duplicator') . '</a>';
    return $actions;
}

function lct_duplicate_file()
{
    global $pagenow;

    //Check to make sure we're on the right page and performing the right action
    if ('upload.php' != $pagenow) {
        return false;
    } elseif (empty($_GET['lct-copy-media-file-postid'])) {
        return false;
    } else {
        $post_id = intval($_GET['lct-copy-media-file-postid']);

        if (empty($post_id)) {
            return false;
        } else {
            $url = wp_get_attachment_url($post_id);
            $post_data = get_post($post_id);
            $prefix = sanitize_text_field(get_option('lct_duplicator_title_prefix'));
            $suffix = sanitize_text_field(get_option('lct_duplicator_title_suffix'));

            if(!empty($prefix) || !empty($suffix))
            {
                $desc = $prefix.' '.$post_data->post_title.' '.$suffix;
            }else{
                $desc = "Copy of " . $post_data->post_title;
            }

            $attachment_id = lct_save_external_file($url, $post_id, $desc);

            //Redirect to the edit page for that file
            wp_safe_redirect(admin_url('post.php?post=' . $attachment_id . '&action=edit'));
            exit();
        }
    }
}

function lct_mime_check($mime){
    $all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp","image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp","image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp","application\/x-win-bitmap"],"gif":["image\/gif"],"jpg":["image\/jpeg","image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],"wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],"ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg","video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],"kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],"rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application","application\/x-jar"],"zip":["application\/x-zip","application\/zip","application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],"7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],"svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],"mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],"webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],"pdf":["application\/pdf","application\/octet-stream"],"pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],"ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office","application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],"xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],"xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel","application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],"xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo","video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],"log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],"wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],"tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop","image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],"mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar","application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40","application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],"cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary","application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],"ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],"wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],"dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php","application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],"swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],"mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],"rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],"jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],"eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],"p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],"p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
    $all_mimes = json_decode($all_mimes, true);
    foreach ($all_mimes as $key => $value) {
        if (array_search($mime, $value) !== false) return $key;
    }
    return false;
}

function lct_check_headers($link){
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
        CURLOPT_URL => $link
    ));

    $file_headers = explode("\n", curl_exec($curl));
    $size = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    $mime = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
    curl_close($curl);

    $file_headers['size'] = absint($size);
    $file_headers['mime'] = trim($mime);
    return $file_headers;
}

function lct_checkValidLink($link){
    $file_headers = lct_check_headers($link);
    $headerStatus = trim(preg_replace('/\s\s+/', ' ', $file_headers[0]));
    $allow_files = array('HTTP/1.1 200 OK', 'HTTP/1.0 200 OK');

    if (in_array($headerStatus, $allow_files) && !empty($file_headers) && $file_headers['size'] > 0) {
        return $file_headers;
    } else {
        return false;
    }
}

function lct_get_existing_attachment_id($url){
    global $wpdb;
    $sql = "SELECT 	posts.ID, meta.meta_id, meta.meta_value FROM " . $wpdb->posts . " posts, " . $wpdb->postmeta . " meta WHERE posts.post_type = 'attachment' posts.ID = meta.post_id and  meta_key = '_lct_copy_from_url-original_file' and meta_value = '" . $url . "' LIMIT 1";
    $results = $wpdb->get_results($sql);
    if (isset($results[0]->ID)) {
        return $results[0]->ID;
    } else {
        return false;
    }
}

function lct_save_external_file($url, $post_id = 0, $desc = '', $check = false){
    if (isset($check) and ($check === TRUE)) {
        $existing = lct_get_existing_attachment_id($url);
    }

    if (isset($existing) and is_numeric($existing)) {
        return $existing;
    } else {
        $headers = lct_checkValidLink($url);

        if ($headers === false) {
            return new WP_Error('lct_copy_from_url', 'Invalid Link');
        } else {
            $tmp = download_url($url);
            $file_array = array();
            $fileextension = @image_type_to_extension(exif_imagetype($url));

            if (empty($fileextension) and isset($headers['mime'])) {
                $extension = lct_mime_check($headers['mime']);
                if (!empty($extension)) {
                    $fileextension = '.' . $extension;
                }
            }

            if (empty($fileextension)) {
                $filename_from_url = parse_url($url);
                $fileextension = '.' . pathinfo($filename_from_url['path'], PATHINFO_EXTENSION);
            }

            if ($fileextension == '.jpeg') {
                $fileextension = '.jpg';
            }

            $path = pathinfo($tmp);
            if (!isset($path['extension'])) {
                $tmpnew = $tmp . '.tmp';
                $file_array['tmp_name'] = $tmpnew;
            } else {
                $file_array['tmp_name'] = $tmp;
            }

            $name = pathinfo($url, PATHINFO_FILENAME) . $fileextension;
            $file_array['name'] = $name;

            // If error storing temporarily, unlink
            if (is_wp_error($tmp)) {
                @unlink($file_array['tmp_name']);
                return $tmp;
            }

            // do the validation and storage stuff
            $id = media_handle_sideload($file_array, $post_id, $desc);
            $local_url = wp_get_attachment_url($id);

            // If error storing permanently, unlink
            if (is_wp_error($id)) {
                @unlink($file_array['tmp_name']);
            } else {
                // create the thumbnails
                $attach_data = wp_generate_attachment_metadata($id, get_attached_file($id));
                wp_update_attachment_metadata($id, $attach_data);
                //save the original url as post meta
                add_post_meta($id, '_lct_copy_from_url-original_file', $url, true);
            }

            return $id;
        }
    }
}
