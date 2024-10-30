<?php

/**
 * Admin Settings & Setup file.
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Admin class.
 */
class KairaSiteChat_Admin
{
    /**
     * Constructor function.
     */
    public function __construct()
    {
        // register_activation_hook( $this->file, array( $this, 'install' ) );
        add_action(
            'admin_menu',
            array( $this, 'kaira_scp_create_admin_menu' ),
            10,
            1
        );
        add_filter( 'plugin_action_links_kaira-site-chat/kaira-site-chat.php', array( $this, 'kaira_scp_add_plugins_settings_link' ) );
        add_filter(
            'plugin_row_meta',
            array( $this, 'kaira_scp_add_plugins_row_link' ),
            10,
            2
        );
        // Add a first time, dismissable notice
        add_action( 'admin_init', array( $this, 'kaira_scp_install_notice_ignore' ), 0 );
        add_action( 'admin_notices', array( $this, 'kaira_scp_installed_notice' ) );
        // Regoster Post/Page level options
        add_action( 'init', array( $this, 'kaira_scp_register_plugin_elements' ), 11 );
        add_filter( 'body_class', array( $this, 'kaira_scp_custom_body_class' ) );
        // Add footer div for React to render Site Chat
        add_filter( 'template_redirect', array( $this, 'kaira_scp_render_sc_element' ), 11 );
    }
    
    /**
     * Create an Admin Sub-Menu under WooCommerce
     */
    public function kaira_scp_create_admin_menu()
    {
        $capability = 'manage_options';
        $slug = 'kairasc-settings';
        add_submenu_page(
            'options-general.php',
            __( 'Site Chat Settings', 'kaira-site-chat' ),
            __( 'WhatsApp Settings', 'kaira-site-chat' ),
            $capability,
            $slug,
            array( $this, 'kaira_scp_menu_page_template' )
        );
    }
    
    /**
     * Create a Setting link on Plugins.php page
     */
    public function kaira_scp_add_plugins_settings_link( $links )
    {
        $settings_link = '<a href="admin.php?page=kairasc-settings">' . esc_html__( 'Settings', 'kaira-site-chat' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }
    
    /**
     * Create a Setting link on Plugins.php page
     */
    public function kaira_scp_add_plugins_row_link( $plugin_meta, $plugin_file )
    {
        
        if ( strpos( $plugin_file, 'kaira-site-chat.php' ) !== false ) {
            $new_links = array(
                'Documentation' => '<a href="' . esc_url( 'https://wpsitechat.com/documentation/' ) . '" target="_blank" aria-label="' . esc_attr__( 'View Site Chat documentation', 'kaira-site-chat' ) . '">' . esc_html__( 'Documentation', 'kaira-site-chat' ) . '</a>',
                'FAQs'          => '<a href="' . esc_url( 'https://wpsitechat.com/support/faqs/' ) . '" target="_blank" aria-label="' . esc_attr__( 'Go to Site Chat FAQ\'s', 'kaira-site-chat' ) . '">' . esc_html__( 'FAQ\'s', 'kaira-site-chat' ) . '</a>',
            );
            $plugin_meta = array_merge( $plugin_meta, $new_links );
        }
        
        return $plugin_meta;
    }
    
    /**
     * Create the Page Template html for React
     * Settings created in ../src/backend/settings/admin.js
     */
    public function kaira_scp_menu_page_template()
    {
        $allowed_html = array(
            'div' => array(
            'class' => array(),
            'id'    => array(),
        ),
            'h2'  => array(),
        );
        $html = '<div class="wrap">' . "\n";
        $html .= '<h2> </h2>' . "\n";
        $html .= '<div id="kaira-sitechat-root"><div id="kaira-sitechat-footer"></div></div>' . "\n";
        $html .= '</div>' . "\n";
        echo  wp_kses( $html, $allowed_html ) ;
    }
    
    /**
     * Function to check for active plugins
     */
    public function kaira_scp_is_plugin_active( $plugin_name )
    {
        // Get Active Plugin Setting
        $active_plugins = (array) get_option( 'active_plugins', array() );
        if ( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', array() ) ) );
        }
        $plugin_filenames = array();
        foreach ( $active_plugins as $plugin ) {
            
            if ( false !== strpos( $plugin, '/' ) ) {
                // normal plugin name (plugin-dir/plugin-filename.php)
                list( , $filename ) = explode( '/', $plugin );
            } else {
                // no directory, just plugin file
                $filename = $plugin;
            }
            
            $plugin_filenames[] = $filename;
        }
        return in_array( $plugin_name, $plugin_filenames );
    }
    
    /**
     * ADMIN NOTICES
     * 
     * Create an Error Notice if no WooCommerce
     */
    public function kaira_scp_installed_notice()
    {
        global  $pagenow ;
        global  $current_user ;
        $kaira_scp_user_id = $current_user->ID;
        $kaira_scp_page = ( isset( $_GET['page'] ) ? $pagenow . '?page=' . sanitize_text_field( $_GET['page'] ) . '&' : sanitize_text_field( $pagenow ) . '?' );
        
        if ( current_user_can( 'manage_options' ) && !get_user_meta( $kaira_scp_user_id, 'kaira_scp_install_notice_dismiss', true ) ) {
            ?>
			<div class="notice notice-info wasc-admin-notice">
                <h3><?php 
            esc_html_e( 'Thank you for trying out Site Chat !', 'kaira-site-chat' );
            ?></h3>

				<?php 
            ?>
					<p class="wasc-admin-txt"><?php 
            /* translators: 1: 'great launch specials'. */
            printf( esc_html__( 'We\'ve just released Site Chat so we\'re running %1$s on Site Chat Pro.', 'kaira-site-chat' ), wp_kses( '<a href="https://wpsitechat.com/purchase/" target="_blank">great launch specials</a>', array(
                'a' => array(
                'href'   => array(),
                'target' => array(),
            ),
            ) ) );
            ?>
					</p>
				<?php 
            ?>
				
				<div class="wasc-notice-cols">
					<div class="wasc-notice-col">
						<h5><?php 
            esc_html_e( 'Let\'s set up your Site Chat', 'kaira-site-chat' );
            ?></h5>
						<p>
							<?php 
            /* translators: 1: 'Site Chat Settings'. */
            printf( esc_html__( 'Go to the %1$s page to easily set up your WhatsApp chat box.', 'kaira-site-chat' ), wp_kses( '<a href="' . esc_url( admin_url( '/options-general.php?page=kairasc-settings' ) ) . '">Site Chat Settings</a>', array(
                'a' => array(
                'href' => array(),
            ),
            ) ) );
            ?>
						</p>
						<a href="<?php 
            echo  esc_url( admin_url( '/options-general.php?page=kairasc-settings' ) ) ;
            ?>" class="wasc-link">
							<?php 
            esc_html_e( 'Set up Site Chat', 'kaira-site-chat' );
            ?>
						</a>
					</div>
					<div class="wasc-notice-col">
						<h5><?php 
            esc_html_e( 'Is something not working?', 'kaira-site-chat' );
            ?></h5>
						<p>
							<?php 
            /* translators: 1: 'Read our documentation'. */
            printf( esc_html__( 'Have you found a bug? Are you not sure on how to set it up? %1$s or get help on setting up Site Chat.', 'kaira-site-chat' ), wp_kses( '<a href="https://wpsitechat.com/documentation/" target="_blank">Read our documentation</a>', array(
                'a' => array(
                'href'   => array(),
                'target' => array(),
            ),
            ) ) );
            ?>
						</p>
						<a href="https://wpsitechat.com/support/" class="wasc-link" target="_blank">
							<?php 
            esc_html_e( 'Contact our Support', 'kaira-site-chat' );
            ?>
						</a>
					</div>
					<div class="wasc-notice-col">
						<h5><?php 
            esc_html_e( 'Help Site Chat', 'kaira-site-chat' );
            ?></h5>
						<p>
							<?php 
            esc_html_e( 'If you\'re willing to, please consider giving us a 5 star rating... It\'ll really help us improve Site Chat and gain users trust.', 'kaira-site-chat' );
            ?>
						</p>
						<span class="wasc-link wasc-rating-click"><?php 
            esc_html_e( 'Sure, I\'ll rate Site Chat', 'kaira-site-chat' );
            ?></span>
						<div class="wasc-notice-rate">
							<p><?php 
            esc_html_e( 'If you\'re not happy, please get in contact and let us help you fix the issue right away.', 'kaira-site-chat' );
            ?></p>
							<a href="https://wordpress.org/support/plugin/site-chat/reviews/#new-post" class="wasc-link" target="_blank"><?php 
            esc_html_e( 'I\'m happy to give you 5 stars', 'kaira-site-chat' );
            ?></a><br />
							<a href="https://wpsitechat.com/support/contact/" class="wasc-link" target="_blank"><?php 
            esc_html_e( 'I\'m not happy. Please help!', 'kaira-site-chat' );
            ?></a>
						</div>
					</div>
				</div>
				<a href="<?php 
            echo  esc_url( admin_url( $kaira_scp_page . 'kaira_scp_install_notice_ignore' ) ) ;
            ?>" class="wasc-notice-close"></a>
			</div><?php 
        }
    
    }
    
    // Make Notice Dismissable
    public function kaira_scp_install_notice_ignore()
    {
        global  $current_user ;
        $kaira_scp_user_id = $current_user->ID;
        if ( isset( $_GET['kaira_scp_install_notice_ignore'] ) ) {
            update_user_meta( $kaira_scp_user_id, 'kaira_scp_install_notice_dismiss', true );
        }
    }
    
    /**
     * Register Post/Page Options
     */
    public function kaira_scp_register_plugin_elements()
    {
        // Page Meta for Page Sidebar Settings
        register_meta( 'post', 'kaira_scp_post_remove', array(
            'type'          => 'boolean',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
        ) );
        register_meta( 'post', 'kaira_scp_post_excl_users', array(
            'object_subtype' => 'page',
            'type'           => 'string',
            'single'         => true,
            'show_in_rest'   => true,
            'auth_callback'  => function () {
            return current_user_can( 'edit_posts' );
        },
        ) );
        register_meta(
            'post',
            // object type, can be 'post', 'comment', 'term', 'user'
            'kaira_scp_post_intro',
            // meta key
            array(
                'object_subtype' => 'page',
                'type'           => 'string',
                'single'         => true,
                'show_in_rest'   => true,
                'auth_callback'  => function () {
                return current_user_can( 'edit_posts' );
            },
            )
        );
        register_meta( 'post', 'kaira_scp_post_description', array(
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
        ) );
        register_meta( 'post', 'kaira_scp_post_reply_time', array(
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
        ) );
        register_meta( 'post', 'kaira_scp_post_message_placeholder', array(
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
        ) );
        register_meta( 'post', 'kaira_scp_post_call_to_action', array(
            'type'          => 'string',
            'single'        => true,
            'show_in_rest'  => true,
            'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
        ) );
        // Register Blocks
        // kaira_scp_register_block_type('wasc-button');
    }
    
    /**
     * Custom Body Classes to remove on devices
     */
    public function kaira_scp_custom_body_class( $classes )
    {
        $wascOptions = get_option( 'kaira_sitechat_options' );
        $wascChatOptions = ( $wascOptions ? json_decode( $wascOptions['wascOptions'] ) : '' );
        if ( isset( $wascChatOptions->remove_on_desktop ) && $wascChatOptions->remove_on_desktop === true ) {
            $classes[] = sanitize_html_class( 'wasc-remdesktop' );
        }
        if ( isset( $wascChatOptions->remove_on_tablet ) && $wascChatOptions->remove_on_tablet === true ) {
            $classes[] = sanitize_html_class( 'wasc-remtablet' );
        }
        if ( isset( $wascChatOptions->remove_on_mobile ) && $wascChatOptions->remove_on_mobile === true ) {
            $classes[] = sanitize_html_class( 'wasc-remmobile' );
        }
        return $classes;
    }
    
    /**
     * Frontend div display on WC Product Pages
     */
    public function kaira_scp_render_sc_element()
    {
        $wascOptions = get_option( 'kaira_sitechat_options' );
        $wascChatOptions = ( $wascOptions ? json_decode( $wascOptions['wascOptions'] ) : '' );
        
        if ( KairaSiteChat_Admin::kaira_scp_is_plugin_active( 'woocommerce.php' ) ) {
            // IF WC is active
            $scProductAddAt = ( isset( $wascChatOptions->prod_btn_position ) && isset( $wascChatOptions->wc_hook_pos ) ? $wascChatOptions->wc_hook_pos : 'wp_footer' );
            
            if ( is_product() ) {
                add_action( $scProductAddAt, array( $this, 'kaira_scp_add_sc_footer' ) );
            } else {
                add_action( 'wp_footer', array( $this, 'kaira_scp_add_sc_footer' ) );
            }
        
        } else {
            add_action( 'wp_footer', array( $this, 'kaira_scp_add_sc_footer' ) );
        }
    
    }
    
    /**
     * Site Chat element to React render to
     */
    public function kaira_scp_add_sc_footer()
    {
        echo  '<div id="kaira-sitechat-footer"></div>' ;
    }
    
    /**
     * Installation. Runs on activation.
     */
    public function install()
    {
        $this->_log_version_number();
    }
    
    /**
     * Log the plugin version number.
     */
    private function _log_version_number()
    {
        //phpcs:ignore
        update_option( 'wa_sitechat_version', KAIRA_SCP_PLUGIN_VERSION );
    }

}
new KairaSiteChat_Admin();