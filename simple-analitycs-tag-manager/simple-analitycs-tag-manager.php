<?php 
/*
    Plugin Name: Simple Analytics - Tag Manager
    Description: It allows you to very simply configure your code: Google Analytics and Google Tag Manager.
    Tags: Google Analytics, Google Tag Manager, Simple Google Analytics, Simple Google Tag Manager, Google, Goolge Analytics, Goolge Tag Manager.
    Author: Miguel Fuentes
    Author URI: https://kodewp.com
    Text Domain: simple-ga-gtm
    Domain Path: /languages/
    Version: 1.0
    Requires PHP: 5.2
    License: GPL v2 or later
*/

if (!defined('ABSPATH')) exit;

add_action( 'admin_menu', 'kwp_simple_ga_gtm_add_admin_menu' );
add_action( 'admin_init', 'kwp_simple_ga_gtm_settings_init' );

function kwp_simple_ga_gtm_add_admin_menu(  ) {
    add_options_page( 'Simple - GA / GTM', 'Simple - GA / GTM', 'manage_options', 'simple-ga-gtm', 'kwp_simple_ga_gtm_options_page' );
}

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'kwp_simple_ga_gtm_plugin_page_settings_link');
function kwp_simple_ga_gtm_plugin_page_settings_link( $links ) {
    $links[] = '<a href="' . admin_url( 'options-general.php?page=simple-ga-gtm' ) . '">' . __('Settings', 'simple-ga-gtm') . '</a>';
    return $links;
}

function kwp_simple_ga_gtm_settings_init(  ) {
    register_setting( 'kwp_SimpleGaGtm_Plugin', 'kwp_simple_ga_gtm_settings' );

    add_settings_section(
        'kwp_kwp_SimpleGaGtm_Plugin_section',
        __( 'Description:', 'simple-ga-gtm' ),
        'kwp_simple_ga_gtm_settings_section_callback',
        'kwp_SimpleGaGtm_Plugin'
    );

    add_settings_field(
        'kwp_simple_text_field_ga',
        __( 'Google Analitycs', 'simple-ga-gtm' ),
        'kwp_simple_text_field_googleAnlitycs',
        'kwp_SimpleGaGtm_Plugin',
        'kwp_kwp_SimpleGaGtm_Plugin_section'
    );

    add_settings_field(
        'kwp_simple_text_field_gtm',
        __( 'Google Tag Manager', 'simple-ga-gtm' ),
        'kwp_simple_text_field_goolgeTagManager',
        'kwp_SimpleGaGtm_Plugin',
        'kwp_kwp_SimpleGaGtm_Plugin_section'
    );

}

function kwp_simple_text_field_googleAnlitycs(  ) {
    $options = get_option( 'kwp_simple_ga_gtm_settings' ); ?>
    <input type='text' class="regular-text" name='kwp_simple_ga_gtm_settings[kwp_simple_text_field_ga]' value='<?php echo $options['kwp_simple_text_field_ga']; ?>' placeholder="UA-111100000-1">
<?php
}

function kwp_simple_text_field_goolgeTagManager(  ) {
    $options = get_option( 'kwp_simple_ga_gtm_settings' ); ?>
    <input type='text' class="regular-text" name='kwp_simple_ga_gtm_settings[kwp_simple_text_field_gtm]' value='<?php echo $options['kwp_simple_text_field_gtm']; ?>' placeholder="GTM-PMMFVT7K">
<?php
}

function kwp_simple_ga_gtm_settings_section_callback(  ) {
    _e( 'Settings: Google Analitycs - Tag Manager.', '');
    echo "<hr>";
}

function kwp_simple_ga_gtm_options_page(  ) {
    ?>
    <div class="wrap">
        <form action='options.php' method='post'>
            <h2><?php _e('Settings Google: Analytics / Tag Manager', 'simple-ga-gtm'); ?></h2>
            <?php
            settings_fields( 'kwp_SimpleGaGtm_Plugin' );
            do_settings_sections( 'kwp_SimpleGaGtm_Plugin' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
 
function kwp_simple_add_googleAnalitycsTagManager_header() {
    $kwp_simple_ga_gtm_options_plg_set = get_option( 'kwp_simple_ga_gtm_settings' );
    if( $kwp_simple_ga_gtm_options_plg_set[ 'kwp_simple_text_field_ga' ] ) { ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $kwp_simple_ga_gtm_options_plg_set['kwp_simple_text_field_ga']; ?>"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '<?php echo $kwp_simple_ga_gtm_options_plg_set['kwp_simple_text_field_ga']; ?>');
        </script>
    <?php }
    if( $kwp_simple_ga_gtm_options_plg_set[ 'kwp_simple_text_field_gtm' ] ) { ?>
    <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo $kwp_simple_ga_gtm_options_plg_set['kwp_simple_text_field_gtm']; ?>');</script>
        <!-- End Google Tag Manager -->
    <?php }
}

function kwp_simple_add_googleAnalitycsTagManager_body() {
    $kwp_simple_ga_gtm_options_plg_set = get_option( 'kwp_simple_ga_gtm_settings' );
    if( $kwp_simple_ga_gtm_options_plg_set[ 'kwp_simple_text_field_gtm' ] ) { ?>
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $kwp_simple_ga_gtm_options_plg_set['kwp_simple_text_field_gtm']; ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) --> 
    <?php }
}

function kwp_simple_add_googleAnalitycsTagManager_login(){
    if ( is_user_logged_in() ) {
       // No se necesita hacer seguimiento en la web, miestras los administradores estan actualizando informacion en la web.
    } else {
       add_action('wp_head','kwp_simple_add_googleAnalitycsTagManager_header', 20);
       add_action('wp_body_open','kwp_simple_add_googleAnalitycsTagManager_body', 20);
    }
}
add_action('init', 'kwp_simple_add_googleAnalitycsTagManager_login');