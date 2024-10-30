<?php
add_action( 'admin_init', 'lct_duplicator_jetpack_init' );


function lct_duplicator_jetpack_init() {
	add_filter('lct_duplicator_blacklist_filter', 'lct_duplicator_jetpack_add_to_blacklist', 10, 1 );
	
	if (class_exists('WPCom_Markdown')){
		add_action('lct_duplicator_pre_copy', 'lct_duplicator_jetpack_disable_markdown', 10);
		add_action('lct_duplicator_post_copy', 'lct_duplicator_jetpack_enable_markdown', 10);
	}	
}

function lct_duplicator_jetpack_add_to_blacklist($meta_blacklist) {
	$meta_blacklist[] = '_wpas*'; //Jetpack Publicize
	$meta_blacklist[] = '_publicize*'; //Jetpack Publicize
	
	$meta_blacklist[] = '_jetpack*'; //Jetpack Subscriptions etc.
	
	return $meta_blacklist;
}

// Markdown
function lct_duplicator_jetpack_disable_markdown(){
	WPCom_Markdown::get_instance()->unload_markdown_for_posts();
}

function lct_duplicator_jetpack_enable_markdown(){
	WPCom_Markdown::get_instance()->load_markdown_for_posts();
}