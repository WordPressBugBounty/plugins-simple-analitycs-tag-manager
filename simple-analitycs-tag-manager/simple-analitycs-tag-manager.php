<?php
/**
 * Plugin Name: Simple Google Analytics Tag Manager
 * Plugin URI: https://kodewp.com
 * Description: Adds Google Analytics 4 and Google Tag Manager snippets with simple privacy-friendly controls.
 * Version: 2.0.0
 * Requires at least: 5.2
 * Requires PHP: 7.0
 * Author: Miguel Fuentes
 * Author URI: https://kodewp.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-analitycs-tag-manager
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KWP_SIMPLE_GA_GTM_OPTION', 'kwp_simple_ga_gtm_settings' );
define( 'KWP_SIMPLE_GA_GTM_PAGE', 'simple-ga-gtm' );
define( 'KWP_SIMPLE_GA_GTM_TEXT_DOMAIN', 'simple-analitycs-tag-manager' );

add_action( 'admin_menu', 'kwp_simple_ga_gtm_add_admin_menu' );
add_action( 'admin_init', 'kwp_simple_ga_gtm_settings_init' );
add_action( 'wp_head', 'kwp_simple_ga_gtm_render_head_snippets', 20 );
add_action( 'wp_body_open', 'kwp_simple_ga_gtm_render_body_snippets', 20 );
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'kwp_simple_ga_gtm_plugin_page_settings_link' );

function kwp_simple_ga_gtm_add_admin_menu() {
	add_options_page(
		__( 'Simple GA4 / GTM', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
		__( 'Simple GA4 / GTM', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
		'manage_options',
		KWP_SIMPLE_GA_GTM_PAGE,
		'kwp_simple_ga_gtm_options_page'
	);
}

function kwp_simple_ga_gtm_plugin_page_settings_link( $links ) {
	$settings_link = sprintf(
		'<a href="%s">%s</a>',
		esc_url( admin_url( 'options-general.php?page=' . KWP_SIMPLE_GA_GTM_PAGE ) ),
		esc_html__( 'Settings', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN )
	);

	$links[] = $settings_link;

	return $links;
}

function kwp_simple_ga_gtm_settings_init() {
	register_setting(
		'kwp_simple_ga_gtm_plugin',
		KWP_SIMPLE_GA_GTM_OPTION,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'kwp_simple_ga_gtm_sanitize_settings',
			'default'           => kwp_simple_ga_gtm_default_settings(),
		)
	);

	add_settings_section(
		'kwp_simple_ga_gtm_section_tracking',
		__( 'Tracking', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
		'kwp_simple_ga_gtm_tracking_section_callback',
		'kwp_simple_ga_gtm_plugin'
	);

	add_settings_field(
		'google_tag_id',
		__( 'Google Analytics GA4', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
		'kwp_simple_ga_gtm_google_tag_field',
		'kwp_simple_ga_gtm_plugin',
		'kwp_simple_ga_gtm_section_tracking'
	);

	add_settings_field(
		'gtm_container_id',
		__( 'Google Tag Manager', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
		'kwp_simple_ga_gtm_container_field',
		'kwp_simple_ga_gtm_plugin',
		'kwp_simple_ga_gtm_section_tracking'
	);

	add_settings_section(
		'kwp_simple_ga_gtm_section_privacy',
		__( 'Privacy and exclusions', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
		'kwp_simple_ga_gtm_privacy_section_callback',
		'kwp_simple_ga_gtm_plugin'
	);

	add_settings_field(
		'exclusions',
		__( 'Exclude tracking', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
		'kwp_simple_ga_gtm_exclusions_field',
		'kwp_simple_ga_gtm_plugin',
		'kwp_simple_ga_gtm_section_privacy'
	);

	add_settings_field(
		'consent_mode',
		__( 'Consent Mode v2', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
		'kwp_simple_ga_gtm_consent_mode_field',
		'kwp_simple_ga_gtm_plugin',
		'kwp_simple_ga_gtm_section_privacy'
	);
}

function kwp_simple_ga_gtm_default_settings() {
	return array(
		'google_tag_id'            => '',
		'disable_auto_page_view'   => 0,
		'gtm_container_id'         => '',
		'exclude_logged_in'        => 1,
		'excluded_roles'           => array( 'administrator' ),
		'consent_mode_enabled'     => 0,
		'analytics_storage'        => 'denied',
		'ad_storage'               => 'denied',
		'ad_user_data'             => 'denied',
		'ad_personalization'       => 'denied',
		'consent_wait_for_update'  => 500,
	);
}

function kwp_simple_ga_gtm_get_settings() {
	$settings = get_option( KWP_SIMPLE_GA_GTM_OPTION, array() );

	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$settings = kwp_simple_ga_gtm_migrate_legacy_settings( $settings );

	return wp_parse_args( $settings, kwp_simple_ga_gtm_default_settings() );
}

function kwp_simple_ga_gtm_migrate_legacy_settings( $settings ) {
	if ( empty( $settings['google_tag_id'] ) && ! empty( $settings['kwp_simple_text_field_ga'] ) ) {
		$legacy_google_tag_id = kwp_simple_ga_gtm_sanitize_google_tag_id( $settings['kwp_simple_text_field_ga'] );

		if ( $legacy_google_tag_id ) {
			$settings['google_tag_id'] = $legacy_google_tag_id;
		}
	}

	if ( empty( $settings['gtm_container_id'] ) && ! empty( $settings['kwp_simple_text_field_gtm'] ) ) {
		$legacy_gtm_container_id = kwp_simple_ga_gtm_sanitize_gtm_container_id( $settings['kwp_simple_text_field_gtm'] );

		if ( $legacy_gtm_container_id ) {
			$settings['gtm_container_id'] = $legacy_gtm_container_id;
		}
	}

	return $settings;
}

function kwp_simple_ga_gtm_sanitize_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = kwp_simple_ga_gtm_default_settings();
	$output   = array();

	$google_tag_id_input              = isset( $input['google_tag_id'] ) ? $input['google_tag_id'] : '';
	$gtm_container_id_input           = isset( $input['gtm_container_id'] ) ? $input['gtm_container_id'] : '';
	$output['google_tag_id']          = kwp_simple_ga_gtm_sanitize_google_tag_id( $google_tag_id_input );
	$output['disable_auto_page_view'] = empty( $input['disable_auto_page_view'] ) ? 0 : 1;
	$output['gtm_container_id']       = kwp_simple_ga_gtm_sanitize_gtm_container_id( $gtm_container_id_input );
	$output['exclude_logged_in']      = empty( $input['exclude_logged_in'] ) ? 0 : 1;
	$output['excluded_roles']         = kwp_simple_ga_gtm_sanitize_roles( isset( $input['excluded_roles'] ) ? $input['excluded_roles'] : array() );
	$output['consent_mode_enabled']   = empty( $input['consent_mode_enabled'] ) ? 0 : 1;
	$output['analytics_storage']      = kwp_simple_ga_gtm_sanitize_consent_state( isset( $input['analytics_storage'] ) ? $input['analytics_storage'] : $defaults['analytics_storage'] );
	$output['ad_storage']             = kwp_simple_ga_gtm_sanitize_consent_state( isset( $input['ad_storage'] ) ? $input['ad_storage'] : $defaults['ad_storage'] );
	$output['ad_user_data']           = kwp_simple_ga_gtm_sanitize_consent_state( isset( $input['ad_user_data'] ) ? $input['ad_user_data'] : $defaults['ad_user_data'] );
	$output['ad_personalization']     = kwp_simple_ga_gtm_sanitize_consent_state( isset( $input['ad_personalization'] ) ? $input['ad_personalization'] : $defaults['ad_personalization'] );
	$output['consent_wait_for_update'] = kwp_simple_ga_gtm_sanitize_wait_time( isset( $input['consent_wait_for_update'] ) ? $input['consent_wait_for_update'] : $defaults['consent_wait_for_update'] );

	if ( '' !== trim( (string) $google_tag_id_input ) && ! $output['google_tag_id'] ) {
		kwp_simple_ga_gtm_add_settings_error(
			'invalid_google_tag_id',
			__( 'The GA4 Measurement ID is invalid. Use a value like G-XXXXXXXXXX, GT-XXXXXXXXX, or AW-XXXXXXXXX.', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN )
		);
	}

	if ( '' !== trim( (string) $gtm_container_id_input ) && ! $output['gtm_container_id'] ) {
		kwp_simple_ga_gtm_add_settings_error(
			'invalid_gtm_container_id',
			__( 'The Google Tag Manager Container ID is invalid. Use a value like GTM-XXXXXXX.', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN )
		);
	}

	return $output;
}

function kwp_simple_ga_gtm_add_settings_error( $code, $message ) {
	if ( function_exists( 'add_settings_error' ) ) {
		add_settings_error(
			KWP_SIMPLE_GA_GTM_OPTION,
			$code,
			$message,
			'error'
		);
	}
}

function kwp_simple_ga_gtm_sanitize_google_tag_id( $value ) {
	$value = strtoupper( sanitize_text_field( wp_unslash( $value ) ) );
	$value = preg_replace( '/\s+/', '', $value );

	return preg_match( '/^(G|GT|AW)-[A-Z0-9_-]+$/', $value ) ? $value : '';
}

function kwp_simple_ga_gtm_sanitize_gtm_container_id( $value ) {
	$value = strtoupper( sanitize_text_field( wp_unslash( $value ) ) );
	$value = preg_replace( '/\s+/', '', $value );

	return preg_match( '/^GTM-[A-Z0-9]+$/', $value ) ? $value : '';
}

function kwp_simple_ga_gtm_sanitize_roles( $roles ) {
	$roles           = is_array( $roles ) ? $roles : array();
	$available_roles = wp_roles()->get_names();
	$clean_roles      = array();

	foreach ( $roles as $role ) {
		$role = sanitize_key( $role );

		if ( isset( $available_roles[ $role ] ) ) {
			$clean_roles[] = $role;
		}
	}

	return array_values( array_unique( $clean_roles ) );
}

function kwp_simple_ga_gtm_sanitize_consent_state( $value ) {
	return 'granted' === $value ? 'granted' : 'denied';
}

function kwp_simple_ga_gtm_sanitize_wait_time( $value ) {
	$value = absint( $value );

	if ( $value > 2000 ) {
		return 2000;
	}

	return $value;
}

function kwp_simple_ga_gtm_tracking_section_callback() {
	echo '<p>' . esc_html__( 'Add your GA4 Measurement ID, your Google Tag Manager container, or both. A valid ID activates the matching snippet automatically.', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ) . '</p>';
}

function kwp_simple_ga_gtm_privacy_section_callback() {
	echo '<p>' . esc_html__( 'Control when tags are printed and define default consent values before Google tags load.', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ) . '</p>';
}

function kwp_simple_ga_gtm_google_tag_field() {
	$options = kwp_simple_ga_gtm_get_settings();
	?>
	<p>
		<input type="text" class="regular-text" name="<?php echo esc_attr( KWP_SIMPLE_GA_GTM_OPTION ); ?>[google_tag_id]" value="<?php echo esc_attr( $options['google_tag_id'] ); ?>" placeholder="G-XXXXXXXXXX" autocomplete="off">
	</p>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( KWP_SIMPLE_GA_GTM_OPTION ); ?>[disable_auto_page_view]" value="1" <?php checked( $options['disable_auto_page_view'], 1 ); ?>>
		<?php esc_html_e( 'Disable automatic page_view event', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'Enter your GA4 Measurement ID, usually G-XXXXXXXXXX. Some Google tag IDs may start with GT- or AW-. Universal Analytics IDs that start with UA- are no longer supported here.', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?></p>
	<?php
}

function kwp_simple_ga_gtm_container_field() {
	$options = kwp_simple_ga_gtm_get_settings();
	?>
	<p>
		<input type="text" class="regular-text" name="<?php echo esc_attr( KWP_SIMPLE_GA_GTM_OPTION ); ?>[gtm_container_id]" value="<?php echo esc_attr( $options['gtm_container_id'] ); ?>" placeholder="GTM-XXXXXXX" autocomplete="off">
	</p>
	<p class="description"><?php esc_html_e( 'If GA4 is configured inside Tag Manager, use only the Tag Manager container ID to avoid duplicate page views.', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?></p>
	<?php
}

function kwp_simple_ga_gtm_exclusions_field() {
	$options = kwp_simple_ga_gtm_get_settings();
	$roles   = wp_roles()->get_names();
	?>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( KWP_SIMPLE_GA_GTM_OPTION ); ?>[exclude_logged_in]" value="1" <?php checked( $options['exclude_logged_in'], 1 ); ?>>
		<?php esc_html_e( 'Do not track logged-in users', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?>
	</label>
	<p class="description"><?php esc_html_e( 'When enabled, all logged-in users are excluded from tracking.', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?></p>

	<fieldset>
		<legend class="screen-reader-text"><?php esc_html_e( 'Excluded roles', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?></legend>
		<p><?php esc_html_e( 'Excluded roles when logged-in users are not globally excluded:', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?></p>
		<?php foreach ( $roles as $role_key => $role_name ) : ?>
			<label style="display:block;margin:4px 0;">
				<input type="checkbox" name="<?php echo esc_attr( KWP_SIMPLE_GA_GTM_OPTION ); ?>[excluded_roles][]" value="<?php echo esc_attr( $role_key ); ?>" <?php checked( in_array( $role_key, $options['excluded_roles'], true ) ); ?>>
				<?php echo esc_html( translate_user_role( $role_name ) ); ?>
			</label>
		<?php endforeach; ?>
	</fieldset>
	<?php
}

function kwp_simple_ga_gtm_consent_mode_field() {
	$options = kwp_simple_ga_gtm_get_settings();
	$states  = array(
		'denied'  => __( 'Denied', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
		'granted' => __( 'Granted', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ),
	);
	?>
	<label>
		<input type="checkbox" name="<?php echo esc_attr( KWP_SIMPLE_GA_GTM_OPTION ); ?>[consent_mode_enabled]" value="1" <?php checked( $options['consent_mode_enabled'], 1 ); ?>>
		<?php esc_html_e( 'Print default Consent Mode v2 settings before Google tags load', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?>
	</label>

	<table class="form-table" role="presentation" style="margin-top:10px;">
		<tbody>
			<?php
			kwp_simple_ga_gtm_render_consent_select( 'analytics_storage', __( 'Analytics storage', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ), $options, $states );
			kwp_simple_ga_gtm_render_consent_select( 'ad_storage', __( 'Ad storage', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ), $options, $states );
			kwp_simple_ga_gtm_render_consent_select( 'ad_user_data', __( 'Ad user data', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ), $options, $states );
			kwp_simple_ga_gtm_render_consent_select( 'ad_personalization', __( 'Ad personalization', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ), $options, $states );
			?>
			<tr>
				<th scope="row"><label for="kwp-simple-ga-gtm-consent-wait"><?php esc_html_e( 'Wait for update', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?></label></th>
				<td>
					<input type="number" id="kwp-simple-ga-gtm-consent-wait" min="0" max="2000" step="100" name="<?php echo esc_attr( KWP_SIMPLE_GA_GTM_OPTION ); ?>[consent_wait_for_update]" value="<?php echo esc_attr( $options['consent_wait_for_update'] ); ?>">
					<span class="description"><?php esc_html_e( 'Milliseconds, maximum 2000.', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?></span>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="description"><?php esc_html_e( 'This sets defaults only. A cookie banner or CMP should update consent after the visitor makes a choice.', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?></p>
	<?php
}

function kwp_simple_ga_gtm_render_consent_select( $key, $label, $options, $states ) {
	?>
	<tr>
		<th scope="row"><label for="<?php echo esc_attr( 'kwp-simple-ga-gtm-' . $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
		<td>
			<select id="<?php echo esc_attr( 'kwp-simple-ga-gtm-' . $key ); ?>" name="<?php echo esc_attr( KWP_SIMPLE_GA_GTM_OPTION . '[' . $key . ']' ); ?>">
				<?php foreach ( $states as $state => $state_label ) : ?>
					<option value="<?php echo esc_attr( $state ); ?>" <?php selected( $options[ $key ], $state ); ?>><?php echo esc_html( $state_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<?php
}

function kwp_simple_ga_gtm_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Simple Google Analytics Tag Manager', KWP_SIMPLE_GA_GTM_TEXT_DOMAIN ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'kwp_simple_ga_gtm_plugin' );
			do_settings_sections( 'kwp_simple_ga_gtm_plugin' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

function kwp_simple_ga_gtm_should_render_tags() {
	$options = kwp_simple_ga_gtm_get_settings();

	if ( empty( $options['google_tag_id'] ) && empty( $options['gtm_container_id'] ) ) {
		return false;
	}

	if ( is_user_logged_in() ) {
		if ( ! empty( $options['exclude_logged_in'] ) ) {
			return false;
		}

		$user_roles = (array) wp_get_current_user()->roles;

		if ( array_intersect( $user_roles, (array) $options['excluded_roles'] ) ) {
			return false;
		}
	}

	return true;
}

function kwp_simple_ga_gtm_render_head_snippets() {
	if ( ! kwp_simple_ga_gtm_should_render_tags() ) {
		return;
	}

	$options = kwp_simple_ga_gtm_get_settings();

	if ( ! empty( $options['consent_mode_enabled'] ) ) {
		kwp_simple_ga_gtm_render_consent_mode_script( $options );
	}

	if ( ! empty( $options['google_tag_id'] ) ) {
		kwp_simple_ga_gtm_render_google_tag_script( $options );
	}

	if ( ! empty( $options['gtm_container_id'] ) ) {
		kwp_simple_ga_gtm_render_gtm_head_script( $options['gtm_container_id'] );
	}
}

function kwp_simple_ga_gtm_render_body_snippets() {
	if ( ! kwp_simple_ga_gtm_should_render_tags() ) {
		return;
	}

	$options = kwp_simple_ga_gtm_get_settings();

	if ( empty( $options['gtm_container_id'] ) ) {
		return;
	}
	?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="<?php echo esc_url( 'https://www.googletagmanager.com/ns.html?id=' . rawurlencode( $options['gtm_container_id'] ) ); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php
}

function kwp_simple_ga_gtm_render_consent_mode_script( $options ) {
	$consent = array(
		'analytics_storage'  => $options['analytics_storage'],
		'ad_storage'         => $options['ad_storage'],
		'ad_user_data'       => $options['ad_user_data'],
		'ad_personalization' => $options['ad_personalization'],
		'wait_for_update'    => absint( $options['consent_wait_for_update'] ),
	);
	?>
	<!-- Google Consent Mode v2 -->
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('consent', 'default', <?php echo wp_json_encode( $consent ); ?>);
	</script>
	<!-- End Google Consent Mode v2 -->
	<?php
}

function kwp_simple_ga_gtm_render_google_tag_script( $options ) {
	$google_tag_id = $options['google_tag_id'];
	$config        = array();

	if ( ! empty( $options['disable_auto_page_view'] ) ) {
		$config['send_page_view'] = false;
	}
	?>
	<!-- Google tag (gtag.js) -->
	<script async src="<?php echo esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $google_tag_id ) ); ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo esc_js( $google_tag_id ); ?>'<?php echo $config ? ', ' . wp_json_encode( $config ) : ''; ?>);
	</script>
	<!-- End Google tag (gtag.js) -->
	<?php
}

function kwp_simple_ga_gtm_render_gtm_head_script( $gtm_container_id ) {
	?>
	<!-- Google Tag Manager -->
	<script>
		(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?php echo esc_js( $gtm_container_id ); ?>');
	</script>
	<!-- End Google Tag Manager -->
	<?php
}
