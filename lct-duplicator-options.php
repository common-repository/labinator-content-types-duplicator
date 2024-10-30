<?php
/**
 * Add an option page
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_admin() ){ // admin actions
	add_action( 'admin_menu', 'lct_duplicator_menu' );
	add_action( 'admin_init', 'lct_duplicator_register_settings' );
	add_action( 'admin_enqueue_scripts', 'lct_duplicator_scripts' );
}

function lct_duplicator_scripts($hook){
    if('settings_page_lct-duplicator' == $hook){
        wp_enqueue_style( 'lct-duplicator-semantic-style', plugin_dir_url( __FILE__ ) . 'assets/css/semantic.min.css', array(), LCT_DUPLICATOR_CURRENT_VERSION );
        wp_enqueue_script( 'lct-duplicator-semantic-script', plugin_dir_url( __FILE__ ) . 'assets/js/semantic.min.js', array(), LCT_DUPLICATOR_CURRENT_VERSION, true );
    }
}

function lct_duplicator_menu() {
    add_options_page(__("Labinator Content Types Duplicator Options", 'lct-duplicator'), __("Labinator Content Types Duplicator", 'lct-duplicator'), 'manage_options', 'lct-duplicator', 'lct_duplicator_options');
}

function lct_duplicator_register_settings() { // whitelist options
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copytitle');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copydate');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copystatus');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copyslug');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copyexcerpt');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copycontent');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copythumbnail');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copytemplate');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copyformat');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copyauthor');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copypassword');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copyattachments');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copychildren');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copycomments');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_copymenuorder');
    register_setting( 'lct_duplicator_group', 'lct_duplicator_blacklist');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_taxonomies_blacklist');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_title_prefix');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_title_suffix');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_increase_menu_order_by');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_roles');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_types_enabled');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_show_row');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_show_adminbar');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_show_submitbox');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_show_bulkactions');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_show_original_column');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_show_original_in_post_states');
	register_setting( 'lct_duplicator_group', 'lct_duplicator_show_original_meta_box');
}

function lct_duplicator_options() {

	if ( current_user_can( 'promote_users' ) && (isset($_GET['settings-updated'])  && $_GET['settings-updated'] == true)){
		global $wp_roles;
		$roles = $wp_roles->get_names();

		$dp_roles = get_option('lct_duplicator_roles');
		if ( $dp_roles == "" ) $dp_roles = array();

		foreach ($roles as $name => $display_name){
			$role = get_role($name);

			/* If the role doesn't have the capability and it was selected, add it. */
			if ( !$role->has_cap( 'copy_posts' )  && in_array($name, $dp_roles) )
				$role->add_cap( 'copy_posts' );

			/* If the role has the capability and it wasn't selected, remove it. */
			elseif ( $role->has_cap( 'copy_posts' ) && !in_array($name, $dp_roles) )
			$role->remove_cap( 'copy_posts' );
		}
	}
	?>
    <div class="wrap">
        <div id="icon-options-general" class="icon32">
            <br>
        </div>
        <h1>
            <?php esc_html_e("Labinator Content Types Duplicator Options", 'lct-duplicator'); ?>
        </h1>
	
        <script>
            function toggle_private_taxonomies(){
                jQuery('.taxonomy_private').toggle(300);
            }


            jQuery(function(){
                jQuery('.taxonomy_private').hide(300);
            });
        </script>

	<style>
        a.toggle_link {
            font-size: small;
            margin-bottom: 10px;
            display: block;
        }

        .labinator-marketplace-calltoaction{
            display: block;
            padding: 30px;
            border: 1px solid #0A4CA1;
            margin: 20px 0;
            background-color: #ECF4FF;
            box-sizing: border-box;
        }

        .labinator-marketplace-calltoaction .desc{
            display: inline-block;
            width: 80%;
            text-align: left;
            box-sizing: border-box;
            vertical-align: middle;
            font-size: 18px;
        }

        .labinator-marketplace-calltoaction .action{
            width: 19%;
            text-align: right;
            box-sizing: border-box;
            display: inline-block;
        }

        .labinator-marketplace-calltoaction .btn{
            display:inline-block;
            padding: 20px 30px;
            background-color: #0A4CA1;
            color:#fff;
            border-radius: 5px;

        }

        .btn:hover{
            background-color: #0a4384;
        }
    </style>

    <div class="labinator-marketplace-calltoaction">
        <div class="desc">
            <?php esc_html_e('All your WordPress needs in one package! 📦 Learn more →', 'lct-duplicator'); ?>
        </div>
        <div class="action">
            <a class="btn" href="https://labinator.com/wordpress-marketplace/">Labinator WordPress Marketplace</a>
        </div>
    </div>

	<form method="post" action="options.php" style="clear: both" id="lct_duplicator_settings_form">
		<?php settings_fields('lct_duplicator_group'); ?>

        <div class="ui tabular menu">
            <div class="item active" data-tab="what-tab"><?php esc_html_e('Cloning Options', 'lct-duplicator'); ?></div>
            <div class="item" data-tab="who-tab"><?php esc_html_e('Permission Options', 'lct-duplicator'); ?></div>
            <div class="item" data-tab="where-tab"><?php esc_html_e('Display Options', 'lct-duplicator'); ?></div>
        </div>

        <div class="ui tab active" data-tab="what-tab">
            <div class="ui four column grid">
                <div class="column">
                    <h3 style="padding-top: 32px">
                        <?php esc_html_e("Enable/Disable Duplicator For:", 'lct-duplicator'); ?>
                    </h3>
                    <div class="ui form">
                        <?php
                        $post_types = get_post_types(array(),'objects');
                        $excludes = array(
                            'revision', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block',
                            'scheduled-action', 'acf-field-group', 'acf-field', 'nav_menu_item'
                        );
                        foreach ($post_types as $post_type_object ) :
                            if (in_array($post_type_object->name, $excludes)) continue; ?>
                            <div class="inline field">
                                <div class="ui toggle checkbox">
                                    <input type="checkbox" name="lct_duplicator_types_enabled[]" value="<?php echo $post_type_object->name; ?>"
                                        <?php if(lct_duplicator_is_post_type_enabled($post_type_object->name)) echo 'checked="checked"'; ?> />
                                    <label><?php echo $post_type_object->labels->name; ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_types_enabled[]" value="menu"
                                    <?php if(lct_duplicator_is_post_type_enabled("menu")) echo 'checked="checked"'; ?> />
                                <label><?php esc_html_e("Menu", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_types_enabled[]" value="widget"
                                    <?php if(lct_duplicator_is_post_type_enabled("widget")) echo 'checked="checked"'; ?> />
                                <label><?php esc_html_e("Widget", 'default'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <h3 style="padding-top: 32px">
                        <?php esc_html_e('Post/Page Elements To Copy:', 'lct-duplicator'); ?>
                    </h3>
                    <div class="ui form">
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copytitle" value="1" <?php  if(get_option('lct_duplicator_copytitle') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Title", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copydate" value="1" <?php  if(get_option('lct_duplicator_copydate') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Date", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copystatus" value="1" <?php  if(get_option('lct_duplicator_copystatus') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Status", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copyslug" value="1" <?php  if(get_option('lct_duplicator_copyslug') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Slug", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copyexcerpt" value="1" <?php  if(get_option('lct_duplicator_copyexcerpt') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Excerpt", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copycontent" value="1" <?php  if(get_option('lct_duplicator_copycontent') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Content", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copythumbnail" value="1" <?php  if(get_option('lct_duplicator_copythumbnail') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Featured Image", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copytemplate" value="1" <?php  if(get_option('lct_duplicator_copytemplate') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Template", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copyformat" value="1" <?php  if(get_option('lct_duplicator_copyformat') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php echo esc_html_x("Format", 'post format', 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copyauthor" value="1" <?php  if(get_option('lct_duplicator_copyauthor') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Author", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copypassword" value="1" <?php  if(get_option('lct_duplicator_copypassword') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Password", 'default'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copyattachments" value="1" <?php  if(get_option('lct_duplicator_copyattachments') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Attachments", 'lct-duplicator');  ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copychildren" value="1" <?php  if(get_option('lct_duplicator_copychildren') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Children", 'lct-duplicator');  ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copycomments" value="1" <?php  if(get_option('lct_duplicator_copycomments') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Comments", 'default');  ?> <span class="description">(<?php esc_html_e("except pingbacks and trackbacks", 'lct-duplicator');  ?>)</span></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_copymenuorder" value="1" <?php  if(get_option('lct_duplicator_copymenuorder') == 1) echo 'checked="checked"'; ?>/>
                                <label> <?php esc_html_e("Menu order", 'default');  ?></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="ui form" style="padding-top: 70px">
                        <div class="field">
                            <label for="lct_duplicator_title_prefix"><?php esc_html_e("Title prefix", 'lct-duplicator'); ?></label>
                            <input type="text" name="lct_duplicator_title_prefix" id="lct_duplicator_title_prefix" value="<?php form_option('lct_duplicator_title_prefix'); ?>" style="float: right;" />
                            <span class="description"><?php esc_html_e("Prefix to be added before the title", 'lct-duplicator'); ?></span>
                        </div>
                        <div class="field">
                            <label for="lct_duplicator_title_prefix"><?php esc_html_e("Title suffix", 'lct-duplicator'); ?></label>
                            <input type="text" name="lct_duplicator_title_suffix" id="lct_duplicator_title_suffix" value="<?php form_option('lct_duplicator_title_suffix'); ?>" style="float: right;" />
                            <span class="description"><?php esc_html_e("Suffix to be added after the title", 'lct-duplicator'); ?></span>
                        </div>
                        <div class="field">
                            <label for="lct_duplicator_title_prefix"><?php esc_html_e("Increase menu order by", 'lct-duplicator'); ?></label>
                            <input type="text" name="lct_duplicator_increase_menu_order_by" id="lct_duplicator_increase_menu_order_by" value="<?php form_option('lct_duplicator_increase_menu_order_by'); ?>" style="float: right;" />
                            <span class="description"><?php esc_html_e("Add this number to the original menu order (blank or zero to retain the value)", 'lct-duplicator'); ?></span>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="ui form">
                        <h3 style="padding-top: 32px">
                            <?php esc_html_e("Do not copy these taxonomies", 'lct-duplicator'); ?>
                        </h3>
                        <a class="toggle_link" style="padding: 15px 0" href="#" onclick="toggle_private_taxonomies();return false;">
                            <?php esc_html_e('Show/hide private taxonomies', 'lct-duplicator');?>
                        </a>
                        <?php
                        $taxonomies=get_taxonomies(array(),'objects'); usort($taxonomies, 'lct_duplicator_tax_obj_cmp');
                        $taxonomies_blacklist = get_option('lct_duplicator_taxonomies_blacklist');
                        if ($taxonomies_blacklist == "") $taxonomies_blacklist = array();

                        foreach ($taxonomies as $taxonomy ) :
                            if($taxonomy->name == 'post_format'){
                                continue;
                            }
                            ?>
                            <div class="inline field">
                                <div class="ui toggle checkbox">
                                    <input type="checkbox" name="lct_duplicator_taxonomies_blacklist[]" value="<?php echo $taxonomy->name?>"<?php if(in_array($taxonomy->name, $taxonomies_blacklist)) echo 'checked="checked"'?> />
                                    <label class="taxonomy_<?php echo ($taxonomy->public)?'public':'private';?>"><?php echo $taxonomy->labels->name.' ['.$taxonomy->name.']'; ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="ui tab" data-tab="who-tab">
            <?php if ( current_user_can( 'promote_users' ) ){ ?>
                <div class="ui one column grid">
                    <div class="column">
                        <div class="ui form">
                            <h3 style="padding-top: 32px">
                                <?php esc_html_e("Roles allowed to copy", 'lct-duplicator'); ?>
                            </h3>
                            <?php
                            global $wp_roles;
                            $roles = $wp_roles->get_names();
                            $post_types = get_post_types( array( 'show_ui' => true ), 'objects' );
                            $edit_capabilities = array('edit_posts' => true);

                            foreach( $post_types as $post_type ) {
                                $edit_capabilities[$post_type->cap->edit_posts] = true;
                            }

                            foreach ( $roles as $name => $display_name ):
                                $role = get_role( $name );
                                if( count ( array_intersect_key( $role->capabilities, $edit_capabilities ) ) > 0 ): ?>
                                    <div class="inline field">
                                        <div class="ui toggle checkbox">
                                            <input type="checkbox" name="lct_duplicator_roles[]" value="<?php echo $name ?>"
                                                <?php if($role->has_cap('copy_posts')) echo 'checked="checked"'?> />
                                            <label><?php echo translate_user_role($display_name); ?></label>
                                        </div>
                                    </div>
                                <?php endif;
                            endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <div class="ui tab" data-tab="where-tab">
            <div class="ui two column grid">
                <div class="column">
                    <div class="ui form">
                        <h3 style="padding-top: 32px">
                            <?php esc_html_e("Show links in", 'lct-duplicator'); ?>
                        </h3>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_show_row" value="1" <?php  if(get_option('lct_duplicator_show_row') == 1) echo 'checked="checked"'; ?>/>
                                <label><?php esc_html_e("Post list", 'lct-duplicator'); ?> </label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_show_submitbox" value="1" <?php  if(get_option('lct_duplicator_show_submitbox') == 1) echo 'checked="checked"'; ?>/>
                                <label><?php esc_html_e("Edit screen", 'lct-duplicator'); ?> </label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_show_adminbar" value="1" <?php  if(get_option('lct_duplicator_show_adminbar') == 1) echo 'checked="checked"'; ?>/>
                                <label><?php esc_html_e("Admin bar", 'lct-duplicator'); ?> <span class="description">(<?php esc_html_e("Useful when Gutenberg is enabled", 'lct-duplicator');  ?>)</span></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <?php global $wp_version;
                                if( version_compare($wp_version, '4.7') >= 0 ){ ?>
                                    <input type="checkbox" name="lct_duplicator_show_bulkactions" value="1" <?php  if(get_option('lct_duplicator_show_bulkactions') == 1) echo 'checked="checked"'; ?>/>
                                    <label><?php esc_html_e("Bulk Actions", 'default'); ?> </label>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="ui form">
                        <h3 style="padding-top: 32px">
                            <?php esc_html_e("Show original item:", 'lct-duplicator'); ?>
                        </h3>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_show_original_meta_box" value="1" <?php  if(get_option('lct_duplicator_show_original_meta_box') == 1) echo 'checked="checked"'; ?>/>
                                <label><?php esc_html_e("In a metabox from the Edit screen of the Classic Editor", 'lct-duplicator'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_show_original_column" value="1" <?php  if(get_option('lct_duplicator_show_original_column') == 1) echo 'checked="checked"'; ?>/>
                                <label><?php esc_html_e("In a column in the Posts screen", 'lct-duplicator'); ?></label>
                            </div>
                        </div>
                        <div class="inline field">
                            <div class="ui toggle checkbox">
                                <input type="checkbox" name="lct_duplicator_show_original_in_post_states" value="1" <?php  if(get_option('lct_duplicator_show_original_in_post_states') == 1) echo 'checked="checked"'; ?>/>
                                <label><?php esc_html_e("After the title in the Posts screen", 'lct-duplicator'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function(){
                jQuery('.tabular.menu .item').tab();
            });
        </script>

		<p class="submit">
			<input type="submit" class="button-primary"
				value="<?php esc_html_e('Save Changes', 'lct-duplicator') ?>" />
		</p>

	</form>
</div>
<?php
}
?>