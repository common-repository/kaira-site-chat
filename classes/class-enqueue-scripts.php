<?php

/**
 * Scripts & Styles file
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Main plugin class.
 */
class KairaSiteChat
{
    /**
     * The single instance of KairaSiteChat
     */
    private static  $_instance = null ;
    //phpcs:ignore
    /**
     * The version number
     */
    public  $_version ;
    //phpcs:ignore
    /**
     * Constructor funtion
     */
    public function __construct( $file = '', $version = KAIRA_SCP_PLUGIN_VERSION )
    {
        $this->file = $file;
        $this->_version = $version;
        // register_activation_hook( $this->file, array( $this, 'install' ) );
        // Load frontend JS & CSS.
        add_action( 'wp_enqueue_scripts', array( $this, 'kaira_scp_frontend_scripts' ), 10 );
        // Load admin JS & CSS.
        add_action(
            'admin_enqueue_scripts',
            array( $this, 'kaira_scp_admin_scripts' ),
            10,
            1
        );
        $this->kaira_scp_load_plugin_textdomain();
        add_action( 'init', array( $this, 'kaira_scp_load_localisation' ), 0 );
    }
    
    // End __construct ()
    /**
     * Load frontend Scripts & Styles
     */
    public function kaira_scp_frontend_scripts()
    {
        $suffix = ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ? '' : '.min' );
        $wascOptions = get_option( 'kaira_sitechat_options' );
        $wascChatOptions = ( $wascOptions ? json_decode( $wascOptions['wascOptions'] ) : '' );
        $frontend_translations = $this->kaira_scp_translation_strings()['frontend'];
        $wascPostSettings = array();
        $showonpages = true;
        
        if ( $showonpages ) {
            wp_register_style(
                // Frontend Style
                'sitechat-frontend-style',
                esc_url( KAIRA_SCP_PLUGIN_URL . '/dist/frontend' . $suffix . '.css' ),
                array( 'dashicons' ),
                KAIRA_SCP_PLUGIN_VERSION
            );
            wp_enqueue_style( 'sitechat-frontend-style' );
            wp_enqueue_media();
            wp_register_script(
                // Frontend JS
                'sitechat-frontend-script',
                esc_url( KAIRA_SCP_PLUGIN_URL . '/dist/frontend' . $suffix . '.js' ),
                array( 'wp-i18n' ),
                KAIRA_SCP_PLUGIN_VERSION,
                true
            );
            wp_localize_script( 'sitechat-frontend-script', 'wascJsOption', array(
                'apiUrl'               => esc_url( get_rest_url() ),
                'nonce'                => wp_create_nonce( 'wp_rest' ),
                'wascOptions'          => $wascOptions,
                'wascPostOptions'      => $wascPostSettings,
                'isProductPage'        => ( defined( 'WC_VERSION' ) && is_product() ? true : false ),
                'userIsAdmin'          => current_user_can( 'manage_options' ),
                'translations'         => $frontend_translations,
                'can_use_premium_code' => kaira_scp_fs()->can_use_premium_code(),
            ) );
        }
        
        wp_enqueue_script( 'sitechat-frontend-script' );
        // wp_set_script_translations('sitechat-frontend-script', 'kaira-site-chat', KAIRA_SCP_PLUGIN_DIR . 'languages/');
    }
    
    // End kaira_scp_frontend_scripts ()
    /**
     * Admin enqueue Scripts & Styles
     */
    public function kaira_scp_admin_scripts( $hook = '' )
    {
        $suffix = ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ? '' : '.min' );
        global  $kaira_scp_fs ;
        wp_register_style(
            // Admin CSS
            'kaira-sc-admin-style',
            esc_url( KAIRA_SCP_PLUGIN_URL . '/dist/admin' . $suffix . '.css' ),
            array(),
            KAIRA_SCP_PLUGIN_VERSION
        );
        wp_enqueue_style( 'kaira-sc-admin-style' );
        wp_register_script(
            // Admin JS
            'kaira-sc-admin-script',
            esc_url( KAIRA_SCP_PLUGIN_URL . '/dist/admin' . $suffix . '.js' ),
            array(),
            KAIRA_SCP_PLUGIN_VERSION,
            true
        );
        wp_enqueue_script( 'kaira-sc-admin-script' );
        $sitechat_admin_page = ( isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '' );
        // Return if not on Settings Page
        if ( 'kairasc-settings' !== $sitechat_admin_page ) {
            return;
        }
        $admin_translations = $this->kaira_scp_translation_strings();
        wp_register_style(
            // Settings CSS
            'kaira-sc-admin-settings-style',
            esc_url( KAIRA_SCP_PLUGIN_URL . '/dist/settings' . $suffix . '.css' ),
            array(),
            KAIRA_SCP_PLUGIN_VERSION
        );
        wp_enqueue_style( 'kaira-sc-admin-settings-style' );
        wp_enqueue_media();
        wp_register_script(
            // Settings JS
            'kaira-sc-admin-settings-script',
            esc_url( KAIRA_SCP_PLUGIN_URL . '/dist/settings' . $suffix . '.js' ),
            array( 'wp-i18n' ),
            KAIRA_SCP_PLUGIN_VERSION,
            true
        );
        wp_localize_script( 'kaira-sc-admin-settings-script', 'wascJsOption', array(
            'apiUrl'               => esc_url( get_rest_url() ),
            'nonce'                => wp_create_nonce( 'wp_rest' ),
            'wcActive'             => ( defined( 'WC_VERSION' ) ? true : false ),
            'accountUrl'           => esc_url( $kaira_scp_fs->get_account_url() ),
            'upgradeUrl'           => esc_url( $kaira_scp_fs->get_upgrade_url() ),
            'userIsAdmin'          => current_user_can( 'manage_options' ),
            'translations'         => $admin_translations,
            'can_use_premium_code' => kaira_scp_fs()->can_use_premium_code(),
        ) );
        wp_enqueue_script( 'kaira-sc-admin-settings-script' );
        // wp_set_script_translations('kaira-sc-admin-settings-script', 'kaira-site-chat', KAIRA_SCP_PLUGIN_DIR . 'languages/');
    }
    
    // End kaira_scp_admin_scripts ()
    /**
     * Load Block Editor Scripts & Styles
     */
    public function kaira_scp_block_editor_scripts( $hook = '' )
    {
        return;
        // wp_set_script_translations('kaira-sc-editor-sidebar-script', 'kaira-site-chat', KAIRA_SCP_PLUGIN_DIR . 'languages/');
    }
    
    // End kaira_scp_block_editor_scripts ()
    /**
     * Load plugin localisation
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function kaira_scp_load_localisation()
    {
        load_plugin_textdomain( 'kaira-site-chat', false, KAIRA_SCP_PLUGIN_DIR . 'languages/' );
    }
    
    // End kaira_scp_load_localisation ()
    /**
     * Load plugin textdomain
     *
     * @access  public
     * @return  void
     * @since   1.0.0
     */
    public function kaira_scp_load_plugin_textdomain()
    {
        $domain = 'kaira-site-chat';
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
        load_textdomain( $domain, KAIRA_SCP_PLUGIN_DIR . 'languages/' . $domain . '-' . $locale . '.mo' );
        load_plugin_textdomain( $domain, false, KAIRA_SCP_PLUGIN_DIR . 'languages/' );
    }
    
    // End kaira_scp_load_plugin_textdomain ()
    /**
     * String Translations
     * Done through PHP because JS is still a workaround
     */
    public function kaira_scp_translation_strings()
    {
        $translations = array(
            'admin'    => array(
            'title'    => __( "Site Chat Settings", "kaira-site-chat" ),
            'tabs'     => array(
            'general' => __( "General Settings", "kaira-site-chat" ),
            'design'  => __( "Design Settings", "kaira-site-chat" ),
            'display' => __( "Display Settings", "kaira-site-chat" ),
            'help'    => __( "Help", "kaira-site-chat" ),
        ),
            'links'    => array(
            'account' => __( "Account Page", "kaira-site-chat" ),
            'docs'    => __( "View Documentation", "kaira-site-chat" ),
        ),
            'settings' => array(
            'setoptions'      => __( "No Options Set!", "kaira-site-chat" ),
            'select'          => __( "Select...", "kaira-site-chat" ),
            'preview'         => __( "Turn on Site Chat", "kaira-site-chat" ),
            'contacts'        => array(
            'setting'     => __( "WhatsApp Contacts", "kaira-site-chat" ),
            'desc'        => __( "Enter a full phone number in international format, excluding any brackets, dashes and zeroes at the start of the phone number.", "kaira-site-chat" ),
            'uploadmedia' => __( "Upload Media", "kaira-site-chat" ),
            'name'        => __( "Name", "kaira-site-chat" ),
            'position'    => __( "Position / Reply Time", "kaira-site-chat" ),
            'number'      => __( "WhatsApp Number", "kaira-site-chat" ),
            'times'       => __( "Times Available", "kaira-site-chat" ),
            'from'        => __( "Available From", "kaira-site-chat" ),
            'to'          => __( "To", "kaira-site-chat" ),
            'add'         => __( "Add Another Contact", "kaira-site-chat" ),
            'uploadimage' => __( "Upload Image", "kaira-site-chat" ),
            'addimage'    => __( "Add Image", "kaira-site-chat" ),
        ),
            'enabletimes'     => array(
            'setting' => __( "Enable 'Times Available'", "kaira-site-chat" ),
            'tip'     => __( "Use this to set the times that agents are available to be contacted'", "kaira-site-chat" ),
        ),
            'onoffline'       => array(
            'setting' => __( "Online / Offline Display", "kaira-site-chat" ),
            'option1' => __( "None", "kaira-site-chat" ),
            'option2' => __( "Display Online Dot", "kaira-site-chat" ),
            'option3' => __( "Display Offline Greyed Out", "kaira-site-chat" ),
            'option4' => __( "Display Offline Greyed Out Plus Online Dot", "kaira-site-chat" ),
            'option5' => __( "Remove Offline Agents", "kaira-site-chat" ),
            'note'    => __( "Set the 'Times Available' on each contact to view the online/offline display", "kaira-site-chat" ),
        ),
            'disableoffline'  => array(
            'setting' => __( "Disable Offline Agents", "kaira-site-chat" ),
            'desc'    => __( "This will remove the click action that lets site visitors send a chat to agents that are displayed as offline", "kaira-site-chat" ),
        ),
            'intro'           => array(
            'setting'     => __( "Intro Text", "kaira-site-chat" ),
            'placeholder' => __( "Start a Conversation", "kaira-site-chat" ),
        ),
            'introdesc'       => array(
            'setting'     => __( "Intro Description", "kaira-site-chat" ),
            'placeholder' => __( "Hi! Please select one of our team members to chat with on WhatsApp.", "kaira-site-chat" ),
            'tip'         => __( "To remove this text, simply enter a space, and click 'Save'", "kaira-site-chat" ),
        ),
            'remicon'         => __( "Remove WhatsApp Icon", "kaira-site-chat" ),
            'replytime'       => array(
            'setting'     => __( "'Reply Time' Text", "kaira-site-chat" ),
            'placeholder' => __( "The team typically replies within an hour", "kaira-site-chat" ),
            'tip'         => __( "'Reply Time' Text", "kaira-site-chat" ),
        ),
            'message'         => array(
            'setting'     => __( "Message Placeholder Text", "kaira-site-chat" ),
            'placeholder' => __( "Hi! How can we help you?", "kaira-site-chat" ),
        ),
            'cta'             => array(
            'setting'     => __( "Call To Action", "kaira-site-chat" ),
            'placeholder' => __( "Send to WhatsApp", "kaira-site-chat" ),
        ),
            'enablelinks'     => array(
            'setting' => __( "Enable Links", "kaira-site-chat" ),
            'tip'     => __( "Add links to join your WhatsApp group(s) or any other external links you choose.", "kaira-site-chat" ),
        ),
            'extlinks'        => array(
            'url'   => __( "Link URL", "kaira-site-chat" ),
            'title' => __( "Link Title", "kaira-site-chat" ),
            'add'   => __( "Add Another Link", "kaira-site-chat" ),
        ),
            'addnotif'        => array(
            'setting' => __( "Add Notification & Message", "kaira-site-chat" ),
            'tip'     => __( "Add a notification to offer specials or pages to view", "kaira-site-chat" ),
        ),
            'notiftext'       => array(
            'setting'     => __( "Notification Text", "kaira-site-chat" ),
            'placeholder' => __( "Use the coupon 'KAIRA' to [*https://wpsitechat.com/ | get 10% off] your next purchase!", "kaira-site-chat" ),
            'note'        => __( "To add links use [*https://wpsitechat.com/ | link text]", "kaira-site-chat" ),
        ),
            'notifpos'        => array(
            'setting' => __( "Notification Position", "kaira-site-chat" ),
            'option1' => __( "Above the list of contacts", "kaira-site-chat" ),
            'option2' => __( "In all the contacts chats", "kaira-site-chat" ),
            'option3' => __( "Only on selected users", "kaira-site-chat" ),
        ),
            'notifusers'      => array(
            'setting' => __( "Users Names", "kaira-site-chat" ),
            'note'    => __( "Enter the names (separated by a comma) of the users that you want to add the note to. Add the names exactly as they appear in the 'Whatsapp Contacts' section.", "kaira-site-chat" ),
        ),
            'notifshow'       => array(
            'setting' => __( "Show Notification", "kaira-site-chat" ),
            'option1' => __( "Always, On each page load", "kaira-site-chat" ),
            'option2' => __( "Show only once until the chat is opened", "kaira-site-chat" ),
        ),
            'iconbtn'         => array(
            'setting' => __( "Display As", "kaira-site-chat" ),
            'option1' => __( "Icon", "kaira-site-chat" ),
            'option2' => __( "Icon With Popup Text", "kaira-site-chat" ),
            'option3' => __( "Button", "kaira-site-chat" ),
        ),
            'iconcolor'       => array(
            'setting'  => __( "Button Color", "kaira-site-chat" ),
            'setting2' => __( "Icon Color", "kaira-site-chat" ),
            'option1'  => __( "Plain Green", "kaira-site-chat" ),
            'option2'  => __( "Flat Green", "kaira-site-chat" ),
            'option3'  => __( "Plain Black", "kaira-site-chat" ),
            'option4'  => __( "Gradient Green", "kaira-site-chat" ),
        ),
            'iconsize'        => __( "Size", "kaira-site-chat" ),
            'iconstyle'       => array(
            'setting' => __( "Style", "kaira-site-chat" ),
            'option1' => __( "Default", "kaira-site-chat" ),
            'option2' => __( "Round", "kaira-site-chat" ),
            'option3' => __( "Square", "kaira-site-chat" ),
        ),
            'icontext'        => array(
            'setting'     => __( "Text", "kaira-site-chat" ),
            'placeholder' => __( "WhatsApp Chat", "kaira-site-chat" ),
        ),
            'remheader'       => __( "Remove Header", "kaira-site-chat" ),
            'selectcolor'     => __( "Select Color", "kaira-site-chat" ),
            'bgcolor'         => __( "Background Color", "kaira-site-chat" ),
            'fcolor'          => __( "Font Color", "kaira-site-chat" ),
            'chatboxdesc'     => __( "Click on a team member to view the chatbox area", "kaira-site-chat" ),
            'boxheight'       => array(
            'setting' => __( "Box Height", "kaira-site-chat" ),
            'tip'     => __( "Enter the height in pixels that you want the chat members and message area to be.", "kaira-site-chat" ),
        ),
            'px'              => __( "pixels", "kaira-site-chat" ),
            'chatdesign'      => array(
            'setting' => __( "Chatbox Design", "kaira-site-chat" ),
            'option1' => __( "Plain Design", "kaira-site-chat" ),
            'option2' => __( "WhatsApp Design", "kaira-site-chat" ),
            'option3' => __( "WhatsApp Design with Light Pattern", "kaira-site-chat" ),
            'option4' => __( "WhatsApp Design with Dark Pattern", "kaira-site-chat" ),
        ),
            'chatbubblecolor' => __( "Chat Bubble Color", "kaira-site-chat" ),
            'buttoncolor'     => __( "Button Color", "kaira-site-chat" ),
            'displayon'       => array(
            'setting' => __( "Display On", "kaira-site-chat" ),
            'option1' => __( "All Pages", "kaira-site-chat" ),
            'option2' => __( "Only On WooCommerce Pages", "kaira-site-chat" ),
            'option3' => __( "WooCommerce Product Pages Only", "kaira-site-chat" ),
            'option4' => __( "Selected Pages/Posts", "kaira-site-chat" ),
        ),
            'displaypos'      => array(
            'setting' => __( "Position", "kaira-site-chat" ),
            'option1' => __( "Right", "kaira-site-chat" ),
            'option2' => __( "Left", "kaira-site-chat" ),
        ),
            'selectpages'     => __( "Select Pages", "kaira-site-chat" ),
            'overrideppages'  => __( "Override Product Pages Text", "kaira-site-chat" ),
            'exclcontacts'    => array(
            'setting' => __( "Exclude Contacts", "kaira-site-chat" ),
            'tip'     => __( "Enter the names (separated by a comma) of the users that you want to exclude from the store product pages. Add the names exactly as they appear in the 'Whatsapp Contacts' section.", "kaira-site-chat" ),
        ),
            'chatincl'        => array(
            'setting' => __( "Include Product Title & URL", "kaira-site-chat" ),
            'tip'     => __( "This will add the product Title and URL to the message when a user messages from a product page.", "kaira-site-chat" ),
        ),
            'buttonpos'       => __( "Change Button Position", "kaira-site-chat" ),
            'wchook'          => array(
            'setting' => __( "WooCommerce Hook", "kaira-site-chat" ),
            'option1' => __( "Default", "kaira-site-chat" ),
            'option2' => __( "WooCommerce before 'Add to Cart' form", "kaira-site-chat" ),
            'option3' => __( "WooCommerce after 'Add to Cart' form", "kaira-site-chat" ),
            'option4' => __( "WooCommerce Product Meta Start", "kaira-site-chat" ),
            'option5' => __( "WooCommerce Product Meta End", "kaira-site-chat" ),
            'option6' => __( "WooCommerce Share", "kaira-site-chat" ),
        ),
            'editpp'          => array(
            'setting' => __( "Enable Per Page Settings", "kaira-site-chat" ),
            'tip'     => __( "Go to Dashboard -> Pages and edit the page you want to customize the text for on that page. In the right sidebar, edit the settings there.", "kaira-site-chat" ),
        ),
            'remdesktop'      => array(
            'setting' => __( "Remove on Desktop", "kaira-site-chat" ),
            'tip'     => __( "This will remove the WhatsApp button on screen sizes larger than 980 pixels wide (desktop).", "kaira-site-chat" ),
        ),
            'remtablet'       => array(
            'setting' => __( "Remove on Tablet", "kaira-site-chat" ),
            'tip'     => __( "This will remove the WhatsApp button on screen sizes smaller than 980 pixels and larger than 782 pixels (tablet / iPad).", "kaira-site-chat" ),
        ),
            'remmobile'       => array(
            'setting' => __( "Remove on Mobile", "kaira-site-chat" ),
            'tip'     => __( "This will remove the WhatsApp button on screen sizes smaller than 782 pixels wide (mobile).", "kaira-site-chat" ),
        ),
            'exclusers'       => __( "Exclude Users", "kaira-site-chat" ),
            'remfrompage'     => __( "Remove Site Chat from this page", "kaira-site-chat" ),
        ),
            'footer'   => array(
            'save'          => __( "Save Settings", "kaira-site-chat" ),
            'delete'        => __( "Reset/Delete all settings", "kaira-site-chat" ),
            'confirmdelete' => __( "Confirm... Delete all Options!", "kaira-site-chat" ),
            'confirmalert'  => __( "Are you sure you want to delete all settings?", "kaira-site-chat" ),
        ),
            'headings' => array(
            'times'       => array(
            'head' => __( "Contact Available Times", "kaira-site-chat" ),
            'desc' => __( "This will let you set the available times for the contacts created above, and select how they display as online or offline, or to remove them if their time is not set.", "kaira-site-chat" ),
        ),
            'chatboxcopy' => __( "Chat Box Copy", "kaira-site-chat" ),
            'links'       => array(
            'head' => __( "Group & External Links", "kaira-site-chat" ),
            'desc' => __( "Here you can add links to join your WhatsApp group(s) or any external links you may need to add.", "kaira-site-chat" ),
        ),
            'notif'       => array(
            'head' => __( "Chat Notification", "kaira-site-chat" ),
            'desc' => __( "Add a notification to display a custom message in the chat box, such as purchase deals or upcoming specials.", "kaira-site-chat" ),
        ),
            'iconbtn'     => array(
            'head' => __( "WhatsApp Icon / Button", "kaira-site-chat" ),
            'desc' => __( "Select the Icon or Button display you want", "kaira-site-chat" ),
        ),
            'header'      => __( "Header", "kaira-site-chat" ),
            'chatbox'     => __( "Chat Box", "kaira-site-chat" ),
            'footer'      => __( "Footer", "kaira-site-chat" ),
            'wcpages'     => array(
            'head' => __( "WooCommerce Product Pages", "kaira-site-chat" ),
            'desc' => __( "This is for if you want to customize the text on the product single pages and use the available shortcodes.", "kaira-site-chat" ),
        ),
            'perpage'     => array(
            'head' => __( "Edit text Per Page", "kaira-site-chat" ),
            'desc' => __( "This will let you override the box settings on a per page level.", "kaira-site-chat" ),
        ),
            'responsive'  => array(
            'head' => __( "Responsive Settings", "kaira-site-chat" ),
            'desc' => __( "Choose which devices the Site Chat will display on. Responsive visibility will take effect only on the site front-end.", "kaira-site-chat" ),
        ),
        ),
            'upgrade'  => array(
            'heading'    => __( 'Premium Feature', 'kaira-site-chat' ),
            'availtimes' => array(
            'title' => __( 'Enable \'Times Available\'', 'kaira-site-chat' ),
            'desc'  => __( 'Set available times that your contacts/agents are online and able to chat, and choose between different online status displays.', 'kaira-site-chat' ),
        ),
            'grouplinks' => array(
            'title' => __( 'Add Group or External Links', 'kaira-site-chat' ),
            'desc'  => __( 'If you have a WhatsApp group for anyone to be able to join, we give you the option to set up to 3 group chat links or any external links of your choice.', 'kaira-site-chat' ),
        ),
            'chatnotif'  => array(
            'title' => __( 'Add Chat Notification', 'kaira-site-chat' ),
            'desc'  => __( 'Increase sales! With the notification option you can offer specials or discount coupons for users to purchase on your store, or simply read a note you\'d like them to see when viewing your website.', 'kaira-site-chat' ),
        ),
            'displayon'  => array(
            'title' => __( 'Display On', 'kaira-site-chat' ),
            'desc'  => __( 'Select between different page display options to only display the chat on WooCommerce pages, WC product pages, or specific pages you choose to display the chat on.', 'kaira-site-chat' ),
        ),
            'wcpages'    => array(
            'title' => __( 'Customize WooCommerce Pages', 'kaira-site-chat' ),
            'desc'  => __( 'Site Chat Pro gives you extra WooCommerce settings like including the product title and url in the chat, change the chat button position to site within the page content, choose which users are available to chat for products and more.', 'kaira-site-chat' ),
        ),
            'perpage'    => array(
            'title' => __( 'Enable Per Page Editing', 'kaira-site-chat' ),
            'desc'  => __( 'Easily remove certain users or completely remove the chat box on a per page level, as well as the options to edit the text per pages to further customize the site chat.', 'kaira-site-chat' ),
        ),
            'buttontext' => __( 'View Pro Features', 'kaira-site-chat' ),
            'demotext'   => __( 'Try out Site Chat Pro', 'kaira-site-chat' ),
        ),
            'infotab'  => array(
            'title'    => __( "Site Chat by Kaira", "kaira-site-chat" ),
            'desc'     => __( "All the settings are pretty self-explanatory or they will have hover hints explaining how they work... Or watch our video below on setting up Site Chat on your website.", "kaira-site-chat" ),
            'subtitle' => __( "Needing more help?", "kaira-site-chat" ),
            'subdesc'  => sprintf( wp_kses( __( 'Read through our documentation or frequently asked questions on our website, or <a href="%s" target="_blank">contact us for support</a>.', 'kaira-site-chat' ), array(
            'a' => array(
            'href'   => array(),
            'target' => array(),
        ),
        ) ), esc_url( 'https://kairaweb.com/' ) ),
            'doclink'  => __( "Documentation", "kaira-site-chat" ),
            'faqlink'  => __( "FAQ's", "kaira-site-chat" ),
            'protitle' => __( "Site Chat Pro", "kaira-site-chat" ),
            'prodesc'  => __( "Site Chat Pro offers a bunch of extra features you'll enjoy... Currently on a launch special!", "kaira-site-chat" ),
            'prolink'  => __( "Purchase Site Chat Pro", "kaira-site-chat" ),
        ),
        ),
            'frontend' => array(
            'intro'     => __( "Start a Conversation", "kaira-site-chat" ),
            'introdesc' => __( "Hi! Please select one of our team members to chat with on WhatsApp.", "kaira-site-chat" ),
            'replytime' => __( "The team typically replies within an hour", "kaira-site-chat" ),
            'message'   => __( "Hi! How can we help you?", "kaira-site-chat" ),
            'cta'       => __( "Send to WhatsApp", "kaira-site-chat" ),
            'icontext'  => __( "WhatsApp Chat", "kaira-site-chat" ),
            'prodtitle' => __( "Product Title", "kaira-site-chat" ),
            'produrl'   => __( "Product URL", "kaira-site-chat" ),
        ),
        );
        return $translations;
    }
    
    /**
     * Main KairaSiteChat Instance
     *
     * Ensures only one instance of KairaSiteChat is loaded or can be loaded.
     */
    public static function instance( $file = '', $version = KAIRA_SCP_PLUGIN_VERSION )
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self( $file, $version );
        }
        return self::$_instance;
    }

}