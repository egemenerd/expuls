<?php
defined( 'ABSPATH' ) || exit;

class ExpulsSettings {
    /* The single instance of the class */
	protected static $_instance = null;

    /* Main Instance */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /* Constructor */
    public function __construct() {
        add_action( 'cmb2_admin_init', array($this, 'register_metabox') );
        add_action( 'admin_enqueue_scripts',array($this, 'colorpicker_labels'), 99 );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
    }

    /* Admin Scripts */
    public function admin_scripts($hook){
        if ('expuls_page_expuls_options' == $hook)  {
            wp_enqueue_style('expuls-admin', EXPULS_PLUGIN_URL . 'css/admin.css', false, '1.0');
            wp_enqueue_script('expuls-admin', EXPULS_PLUGIN_URL . 'js/admin.js', array( 'jquery' ), '1.0', true); 
        } else {
            return;
        }
    }

    /**
    * Hook in and register a metabox to handle a plugin options page and adds a menu item.
    */
    public function register_metabox() {
        // TAB
        $args = array(
            'id'           => 'expuls_options',
            'title'        => esc_html__('Settings', 'expuls'),
            'menu_title'   => esc_html__('Settings', 'expuls'),
            'object_types' => array( 'options-page' ),
            'option_key'   => 'expuls_options',
            'parent_slug'     => 'expuls',
            'capability'      => 'manage_options',
            'save_button'     => esc_html__( 'Save Settings', 'expuls' )
        );

        $options = new_cmb2_box( $args );

        $options->add_field( array(
            'name'    => esc_html__( 'API Key (Required)', 'expuls' ),
            'description' => esc_html__( 'You must get a free API key from Pexels to use this feature. For more information, please read the documentation.', 'expuls' ),
            'id'      => 'expuls_api',
            'type'    => 'text'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Language', 'expuls' ),
            'description' => esc_html__( 'The language of the search you are performing.', 'expuls' ),
            'id'   => 'expuls_lang',
            'type' => 'select',
            'options' => array(
                'en-US' => esc_html__( 'English', 'expuls' ),
                'pt-BR' => esc_html__( 'Portuguese (Brazil)', 'expuls' ),
                'es-ES' => esc_html__( 'Spanish', 'expuls' ),
                'ca-ES' => esc_html__( 'Catalan (Spanish)', 'expuls' ),
                'de-DE' => esc_html__( 'German', 'expuls' ),
                'it-IT' => esc_html__( 'Italian', 'expuls' ),
                'fr-FR' => esc_html__( 'French', 'expuls' ),
                'sv-SE' => esc_html__( 'Swedish', 'expuls' ),
                'pl-PL' => esc_html__( 'Polish', 'expuls' ),
                'nl-NL' => esc_html__( 'Dutch', 'expuls' ),
                'hu-HU' => esc_html__( 'Hungarian', 'expuls' ),
                'cs-CZ' => esc_html__( 'Czech', 'expuls' ),
                'da-DK' => esc_html__( 'Danish', 'expuls' ),
                'fi-FI' => esc_html__( 'Finnish', 'expuls' ),
                'nb-NO' => esc_html__( 'Norwegian', 'expuls' ),
                'uk-UA' => esc_html__( 'Ukrainian', 'expuls' ),
                'tr-TR' => esc_html__( 'Turkish', 'expuls' ),
                'el-GR' => esc_html__( 'Greek', 'expuls' ),
                'ro-RO' => esc_html__( 'Romanian', 'expuls' ),
                'sk-SK' => esc_html__( 'Slovak', 'expuls' ),
                'ru-RU' => esc_html__( 'Russian', 'expuls' ),
                'ja-JP' => esc_html__( 'Japanese', 'expuls' ),
                'zh-TW' => esc_html__( 'Chinese (T)', 'expuls' ),
                'zh-CN' => esc_html__( 'Chinese (S)', 'expuls' ),
                'ko-KR' => esc_html__( 'Korean', 'expuls' ),
                'th-TH' => esc_html__( 'Thai', 'expuls' ),
                'id-ID' => esc_html__( 'Indonesian', 'expuls' ),
                'vi-VN' => esc_html__( 'Vietnamese', 'expuls' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'en-US'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Menu', 'expuls' ),
            'id'   => 'expuls_menu_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Photos', 'expuls' ),
            'id'   => 'expuls_photos',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'expuls' ),
                'disable'   => esc_html__( 'Disable', 'expuls' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Videos', 'expuls' ),
            'id'   => 'expuls_videos',
            'type' => 'radio_inline',
            'options' => array(
                'enable' => esc_html__( 'Enable', 'expuls' ),
                'disable'   => esc_html__( 'Disable', 'expuls' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'enable'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Photos', 'expuls' ),
            'id'   => 'expuls_photos_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Default Image Size', 'expuls' ),
            'description' => esc_html__( 'The default image size for download. Original is not recommended. By default WordPress crops images bigger than 2560px.', 'expuls' ),
            'id'   => 'expuls_img_size',
            'type' => 'select',
            'options' => array(
                'large2x' => esc_html__( 'Large 2x', 'expuls' ),
                'large' => esc_html__( 'Large', 'expuls' ),
                'medium' => esc_html__( 'Medium', 'expuls' ),
                'portrait' => esc_html__( 'Portrait', 'expuls' ),
                'landscape' => esc_html__( 'Landscape', 'expuls' ),
                'tiny' => esc_html__( 'Tiny', 'expuls' ),
                'original' => esc_html__( 'Original', 'expuls' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'large2x'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Thumbnail Size', 'expuls' ),
            'id'   => 'expuls_thumb_size',
            'type' => 'select',
            'options' => array(
                'large2x' => esc_html__( 'Large 2x', 'expuls' ),
                'large' => esc_html__( 'Large', 'expuls' ),
                'medium' => esc_html__( 'Medium', 'expuls' ),
                'portrait' => esc_html__( 'Portrait', 'expuls' ),
                'landscape' => esc_html__( 'Landscape', 'expuls' ),
                'tiny' => esc_html__( 'Tiny', 'expuls' )
            ),
            'attributes' => array(
                'autocomplete' => 'off'
            ),
            'default' => 'medium'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Pagination', 'expuls' ),
            'description' => esc_html__( 'Max. number of images to show.', 'expuls' ),
            'id'   => 'expuls_pagination',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*'
            ),
            'default' => 20
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Videos', 'expuls' ),
            'id'   => 'expuls_videos_title',
            'type' => 'title'
        ) );

        $options->add_field( array(
            'name' => esc_html__( 'Pagination', 'expuls' ),
            'description' => esc_html__( 'Max. number of videos to show.', 'expuls' ),
            'id'   => 'expuls_video_pagination',
            'type' => 'text',
            'attributes' => array(
                'type' => 'number',
                'pattern' => '\d*'
            ),
            'default' => 20
        ) );
    }
    /**
    * Colorpicker Labels
    */
    public function colorpicker_labels( $hook ) {
        global $wp_version;
        if( version_compare( $wp_version, '5.4.2' , '>=' ) ) {
            wp_localize_script(
            'wp-color-picker',
            'wpColorPickerL10n',
            array(
                'clear'            => esc_html__( 'Clear', 'expuls' ),
                'clearAriaLabel'   => esc_html__( 'Clear color', 'expuls' ),
                'defaultString'    => esc_html__( 'Default', 'expuls' ),
                'defaultAriaLabel' => esc_html__( 'Select default color', 'expuls' ),
                'pick'             => esc_html__( 'Select Color', 'expuls' ),
                'defaultLabel'     => esc_html__( 'Color value', 'expuls' )
            )
            );
        }
    }

    /**
    * Expuls get option
    */
    static function get_option( $key = '', $default = false ) {
        if ( function_exists( 'cmb2_get_option' ) ) {
            return cmb2_get_option( 'expuls_options', $key, $default );
        }
        $opts = get_option( 'expuls_options', $default );
        $val = $default;
        if ( 'all' == $key ) {
            $val = $opts;
        } elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
            $val = $opts[ $key ];
        }
        return $val;
    }

}

/**
 * Returns the main instance of the class.
 */
function ExpulsSettings() {  
	return ExpulsSettings::instance();
}
// Global for backwards compatibility.
$GLOBALS['ExpulsSettings'] = ExpulsSettings();