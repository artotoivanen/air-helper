<?php
/**
 *  Force to address in wp_mail function so that test emails wont go to client.
 *  Turn off by using `remove_filter( 'wp_mail', 'air_helper_helper_force_mail_to' )`
 *
 *  @since  0.1.0
 *  @package air-helper
 */

if ( getenv( 'WP_ENV' ) === 'development' ) {
	add_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
}

// Turn off by using `remove_filter( 'wp_mail', 'air_helper_helper_force_mail_to' )`
if ( getenv( 'WP_ENV' ) === 'staging' ) {
	add_filter( 'wp_mail', 'air_helper_helper_force_mail_to' );
}

/**
 *  Force to address in wp_mail.
 *  Change allowed staging roles by using `add_filter( 'air_helper_helper_mail_to_allowed_roles', 'myprefix_override_air_helper_helper_mail_to_allowed_roles' )`
 *  Change address from admin_email by using `add_filter( 'air_helper_helper_mail_to', 'myprefix_override_air_helper_helper_mail_to' )`
 *
 *  @since  0.1.0
 *  @param 	array $args Default wp_mail agruments.
 *  @return array         New wp_mail agruments with forced to address
 */
function air_helper_helper_force_mail_to( $args ) {
	$to = get_option( 'admin_email' );

	if ( getenv( 'WP_ENV' ) === 'staging' ) {
		$allowed_roles = apply_filters( 'air_helper_helper_mail_to_allowed_roles', array( 'administrator', 'editor', 'author' ) );
		$user = get_user_by( 'email', $args['to'] );

		if ( is_a( $user, 'WP_User' ) ) {
			if ( array_intersect( $allowed_roles, $user->roles ) ) {
				$to = $args['to'];
			}
		}
	}

	$args['to'] = apply_filters( 'air_helper_helper_mail_to', $to );
	return $args;
}

/**
 * Remove archive title prefix.
 * Turn off by using `remove_filter( 'get_the_archive_title', 'air_helper_helper_remove_archive_title_prefix' )`
 *
 * @since  0.1.0
 * @param  string $title Default title.
 * @return string Title without prefix
 */
function air_helper_helper_remove_archive_title_prefix( $title ) {
	return preg_replace( '/^\w+: /', '', $title );
}
add_filter( 'get_the_archive_title', 'air_helper_helper_remove_archive_title_prefix' );

/**
 * Disable emojicons introduced with WP 4.2.
 * Turn off by using `remove_action( 'init', 'air_helper_helper_disable_wp_emojicons' )`
 *
 * @since  0.1.0
 * @link http://wordpress.stackexchange.com/questions/185577/disable-emojicons-introduced-with-wp-4-2
 */
function air_helper_helper_disable_wp_emojicons() {
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	add_filter( 'emoji_svg_url', '__return_false' );

	// Disable classic smilies.
	add_filter( 'option_use_smilies', '__return_false' );

	add_filter( 'tiny_mce_plugins', 'air_helper_helper_disable_emojicons_tinymce' );
}
add_action( 'init', 'air_helper_helper_disable_wp_emojicons' );

/**
 * Disable emojicons introduced with WP 4.2.
 *
 * @since 0.1.0
 * @param array $plugins Plugins.
 */
function air_helper_helper_disable_emojicons_tinymce( $plugins ) {
	if ( is_array( $plugins ) ) {
		return array_diff( $plugins, array( 'wpemoji' ) );
	} else {
		return array();
	}
}

/**
 * Clean up admin bar.
 * Turn off by using `remove_action( 'wp_before_admin_bar_render', 'air_helper_helper_remove_admin_bar_links' )`
 * Modify list by using `add_filter( 'air_helper_helper_remove_admin_bar_links', 'myprefix_override_air_helper_helper_remove_admin_bar_links' )`
 *
 * @since  0.1.0
 */
function air_helper_helper_remove_admin_bar_links() {
	global $wp_admin_bar;

	$remove_items = apply_filters( 'air_helper_helper_remove_admin_bar_links', array(
		'about',
		'wporg',
		'documentation',
		'support-forums',
		'feedback',
		'updates',
		'comments',
	) );

	foreach ( $remove_items as $item ) {
		$wp_admin_bar->remove_menu( $item );
	}
}
add_action( 'wp_before_admin_bar_render', 'air_helper_helper_remove_admin_bar_links' );

/**
 * Clean up admin menu from stuff we usually don't need.
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_helper_remove_admin_menu_links', 999 )`
 * Modify list by using `add_filter( 'air_helper_helper_remove_admin_menu_links', 'myprefix_override_air_helper_helper_remove_admin_menu_links' )`
 *
 * @since  0.1.0
 */
function air_helper_helper_remove_admin_menu_links() {
	$remove_items = apply_filters( 'air_helper_helper_remove_admin_menu_links', array(
		'edit-comments.php',
		'themes.php?page=editcss',
		'widgets.php',
		'admin.php?page=jetpack',
	) );

	foreach ( $remove_items as $item ) {
		remove_menu_page( $item );
	}
}
add_action( 'admin_menu', 'air_helper_helper_remove_admin_menu_links', 999 );

/**
 * Hide WP updates nag.
 * Turn off by using `remove_action( 'admin_menu', 'air_helper_wphidenag' )`
 *
 * @since  0.1.0
 */
function air_helper_wphidenag() {
  remove_action( 'admin_notices', 'update_nag', 3 );
}
add_action( 'admin_menu', 'air_helper_wphidenag' );

/**
 * Add a pingback url auto-discovery header for singularly identifiable articles.
 * Turn off by using `remove_action( 'wp_head', 'air_helper_pingback_header' )`
 *
 * @since  0.1.0
 */
function air_helper_pingback_header() {
	if ( is_singular() && pings_open() ) :
		echo '<link rel="pingback" href="', esc_url( get_bloginfo( 'pingback_url' ) ), '">';
	endif;
}
add_action( 'wp_head', 'air_helper_pingback_header' );

/**
 *  Disable REST-API users endpoint.
 *  Turn off by using `remove_filter( 'rest_endpoints', 'air_helper_disable_rest_endpoints' )`
 *
 * 	@since  0.1.0
 */
function air_helper_disable_rest_endpoints( $endpoints ) {
	if ( isset( $endpoints['/wp/v2/users'] ) ) {
  	unset( $endpoints['/wp/v2/users'] );
  }

  if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
  	unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
  }

  return $endpoints;
}
add_filter( 'rest_endpoints', 'air_helper_disable_rest_endpoints' );

/**
 * Remove WordPress Admin Bar when not on development env.
 * Turn off by using `remove_action( 'get_header', 'air_helper_remove_admin_login_header' )`
 *
 * @since  1.0.1
 * @link 	 http://davidwalsh.name/remove-wordpress-admin-bar-css
 */
function air_helper_remove_admin_login_header() {
	remove_action( 'wp_head', '_admin_bar_bump_cb' );
}
add_action( 'get_header', 'air_helper_remove_admin_login_header' );

if ( getenv( 'WP_ENV' ) === 'development' ) {
	/**
	 *  Better styles for admin bar when in development env.
	 *  Turn off by using `remove_action( 'wp_head', 'air_helper_dev_adminbar' )`
	 *
	 *  @since  1.0.1
	 */
  function air_helper_dev_adminbar() {

  	if ( ! is_user_logged_in() ) {
  		return;
  	} ?>
    <style type="text/css">
      html {
        height: auto;
        top: 32px;
        position: relative;
      }

      @media screen and (max-width: 600px) {
        html {
          top: 46px;
        }
      }

     /* Hide WordPress logo */
     #wp-admin-bar-wp-logo {
       display: none;
     }

     /* Invert admin bar */
     #wpadminbar {
       background: #fff;
     }

     @media screen and (max-width: 600px) {
       #wpadminbar {
         position: fixed;
       }
     }

     #wpadminbar .ab-empty-item,
     #wpadminbar a.ab-item,
     #wpadminbar > #wp-toolbar span.ab-label,
     #wpadminbar > #wp-toolbar span.noticon {
       color: #23282d;
     }

     #wpadminbar #adminbarsearch:before,
     #wpadminbar .ab-icon:before,
     #wpadminbar .ab-item:before {
       color: #23282d;
       background: transparent;
     }

     #wpadminbar.nojs li:hover > .ab-sub-wrapper,
     #wpadminbar li.hover > .ab-sub-wrapper {
       top: 32px;
     }

     #wp-admin-bar-airhelperenv.air-helper-env-prod a {
  		background: #00bb00 !important;
  		color: black !important;
  	}

  	#wp-admin-bar-airhelperenv.air-helper-env-stage a {
  		background: orange !important;
  		color: black !important;
  	}

  	#wp-admin-bar-airhelperenv.air-helper-env-dev a {
  		background: red !important;
  		color: black !important;
  	}
   </style>
	<?php }
	add_action( 'wp_head', 'air_helper_dev_adminbar' );
} else {
	show_admin_bar( false );
}

/**
 * Add envarioment marker to adminbar.
 * Turn off by using `remove_action( 'admin_bar_menu', 'air_helper_adminbar_show_env' )`
 *
 * @since  1.1.0
 */
function air_helper_adminbar_show_env( $wp_admin_bar ) {
	$env = esc_attr__( 'tuotanto', 'air-helper' );
	$class = 'air-helper-env-prod';

	if ( getenv( 'WP_ENV' ) === 'staging' ) {
		$env = esc_attr__( 'näyttöversio', 'air-helper' );
		$class = 'air-helper-env-stage';
	} else if ( getenv( 'WP_ENV' ) === 'development' ) {
		$env = esc_attr__( 'kehitys', 'air-helper' );
		$class = 'air-helper-env-dev';
	}

	$wp_admin_bar->add_node( array(
		'id'    => 'airhelperenv',
		'title' => wp_sprintf( __( 'Ympäristö: %s', 'air-helper' ), $env ),
		'href'  => '#',
		'meta'  => array( 'class' => $class )
	) );
}
add_action( 'admin_bar_menu', 'air_helper_adminbar_show_env', 999 );

/**
 * Add envarioment marker styles.
 * Turn off by using `remove_action( 'admin_head', 'air_helper_adminbar_show_env_styles' )`
 *
 * @since  1.1.0
 */
function air_helper_adminbar_show_env_styles() { ?>
  <style>
  	#wp-admin-bar-airhelperenv.air-helper-env-prod a {
  		background: #00bb00 !important;
  		color: black !important;
  	}

  	#wp-admin-bar-airhelperenv.air-helper-env-stage a {
  		background: orange !important;
  		color: black !important;
  	}

  	#wp-admin-bar-airhelperenv.air-helper-env-dev a {
  		background: red !important;
  		color: black !important;
  	}
  </style>
<?php }
add_action( 'admin_head', 'air_helper_adminbar_show_env_styles' );

/**
 * Allow Gravity Forms to hide labels to add placeholders.
 * Turn off by using `add_filter( 'gform_enable_field_label_visibility_settings', '__return_false' )`
 *
 * @since  0.1.0
 */
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

/**
 *  Set Yoast SEO plugin metabox priority to low.
 *  Turn off by using `add_filter( 'wpseo_metabox_prio', 'air_helper_lowpriority_yoastseo' )`
 *
 *  @since  0.1.0
 */
function air_helper_lowpriority_yoastseo() {
  return 'low';
}
add_filter( 'wpseo_metabox_prio', 'air_helper_lowpriority_yoastseo' );
