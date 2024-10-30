<?php

/**
 * Plugin Name: Site Chat by Kaira
 * Version: 1.1.11
 * Plugin URI: https://wpsitechat.com/
 * Description: Website Chat & trusted support for your WordPress site and WooCommerce store.
 * Author: Kaira
 * Author URI: https://kairaweb.com/
 * Requires at least: 5.0
 * Tested up to: 6.2
 * WC requires at least: 3.2
 * WC tested up to: 7.8
 * Text Domain: kaira-site-chat
 * Domain Path: /lang/
 * 
 *
 * @package WordPress
 * @author Kaira
 * @since 1.0.0
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
define( 'KAIRA_SCP_PLUGIN_VERSION', '1.1.11' );
define( 'KAIRA_SCP_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define( 'KAIRA_SCP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if ( function_exists( 'kaira_scp_fs' ) ) {
    kaira_scp_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'kaira_scp_fs' ) ) {
        // Create a helper function for easy SDK access.
        function kaira_scp_fs()
        {
            global  $kaira_scp_fs ;
            
            if ( !isset( $kaira_scp_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $kaira_scp_fs = fs_dynamic_init( array(
                    'id'              => '10112',
                    'slug'            => 'kaira-site-chat',
                    'premium_slug'    => 'kaira-site-chat-pro',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_995647d5eb0ac17b574bee44ff23f',
                    'is_premium'      => false,
                    'premium_suffix'  => 'Pro',
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                    'has_affiliation' => 'selected',
                    'menu'            => array(
                    'slug'        => 'kairasc-settings',
                    'contact'     => false,
                    'support'     => false,
                    'affiliation' => false,
                    'parent'      => array(
                    'slug' => 'options-general.php',
                ),
                ),
                    'is_live'         => true,
                ) );
            }
            
            return $kaira_scp_fs;
        }
        
        // Init Freemius.
        kaira_scp_fs();
        // Signal that SDK was initiated.
        do_action( 'kaira_scp_fs_loaded' );
    }
    
    require_once 'classes/class-enqueue-scripts.php';
    require_once 'classes/class-admin.php';
    require_once 'classes/class-admin-settings.php';
    /**
     * Main instance of KairaSiteChat_Admin to prevent the need to use globals.
     *
     * @since  1.0.0
     * @return object KairaSiteChat_Admin
     */
    function kaira_scp_sitechat()
    {
        $instance = KairaSiteChat::instance( __FILE__, KAIRA_SCP_PLUGIN_VERSION );
        return $instance;
    }
    
    kaira_scp_sitechat();
}
