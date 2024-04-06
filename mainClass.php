<?php
defined( 'ABSPATH' ) || exit;

class Expuls {
    /**
	 * The single instance of the class
	 */
	protected static $_instance = null;

    /**
	 * Main Instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

    /**
	 * Expuls Constructor
	 */
    public function __construct() {
        add_action('admin_menu', array($this, 'register_page'));
        add_action('admin_enqueue_scripts', array($this, 'scripts'));
        add_action('wp_ajax_expulsPhotoSearch', array($this, 'photo_search'));
        add_action('wp_ajax_expulsPhotoImport', array($this, 'import_image'));
        add_action('wp_ajax_expulsVideoSearch', array($this, 'video_search'));
        add_action('wp_ajax_expulsVideoImport', array($this, 'import_video'));
    }

    /* Admin Scripts */
    public function scripts($hook){
        if ('toplevel_page_expuls' == $hook) {
            wp_enqueue_style('fancybox', EXPULS_PLUGIN_URL . 'css/fancybox.css', false, '1.0');
            wp_enqueue_style('expuls', EXPULS_PLUGIN_URL . 'css/style.css', false, '1.0');
            if (is_rtl()) {
                wp_enqueue_style('expuls-rtl', EXPULS_PLUGIN_URL . 'css/rtl.css', false, '1.0');
            }
            wp_enqueue_script('imagesloaded'); 
            wp_enqueue_script('masonry');
            wp_enqueue_script('fancybox', EXPULS_PLUGIN_URL . 'js/fancybox.js', array( 'jquery' ), '4.0', true);
            wp_enqueue_script('expuls', EXPULS_PLUGIN_URL . 'js/custom.js', array( 'jquery' ), '1.0', true);
            wp_localize_script(
                'expuls',
                'expulsParams',
                [
                    'baseURL' => EXPULS_PLUGIN_URL,
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce('expuls-nonce'),
                    'loading' => esc_html__('Loading...', 'expuls'),
                    'loadmore' => esc_html__('Load More', 'expuls'),
                    'wrong' => esc_html__('Something went wrong.', 'expuls'),
                    'nothing' => esc_html__('Nothing Found.', 'expuls'),
                ]
            );
        } else {
            return;
        }
    }

    /**
	 * Register Admin Page
	 */
    public function register_page(){
        add_menu_page( 
            esc_html__( 'Expuls', 'expuls' ),
            esc_html__( 'Expuls', 'expuls' ),
            'upload_files',
            'expuls',
            array($this, 'page_output'),
            'dashicons-admin-media',
            10
        ); 
    }

    /**
	 * Page Output
	 */
    public function page_output() { 
        $getApiKey =  ExpulsSettings::get_option('expuls_api', '');
        $menuPhotos =  ExpulsSettings::get_option('expuls_photos', 'enable');  
        $menuVideos =  ExpulsSettings::get_option('expuls_videos', 'enable');
        if (empty($getApiKey)) { 
            echo '<div id="expuls-nokey" class="expuls-notice expuls-notice-danger">' . esc_html__('Please go to the settings and enter a valid API key.', 'expuls') . ' <a href="https://pexels-help.zendesk.com/hc/en-us/articles/900004904026-How-do-I-get-an-API-key-" target="_blank">' . esc_html__('How do I get an API key?', 'expuls') . '</a></div>';
        } else if ($menuPhotos == 'disable' && $menuVideos == 'disable') {
            echo '<div id="expuls-nokey" class="expuls-notice expuls-notice-warning">' . esc_html__('Please enable photos or videos from Settings->Menu.', 'expuls') . '</div>';
        } else {
        ?>
        <div id="expuls">
            <div id="expuls-header">
                <div class="expuls-logo-wrap">
                    <h1><?php echo esc_html__( 'Expuls', 'expuls' ); ?></h1>
                    <p><?php echo esc_html__( 'Photos and videos provided by', 'expuls' ); ?> <a href="https://www.pexels.com/" target="_blank"><?php echo esc_html__( 'Pexels', 'expuls' ); ?></a>.</p>
                </div>
                <div id="expuls-menu-wrap">
                    <div id="expuls-menu">
                        <?php if ($menuPhotos == 'enable') { ?>
                        <a href="#" data-target="expuls-photos"><span class="dashicons dashicons-format-image"></span><span><?php echo esc_attr__( 'Photos', 'expuls' ); ?></span></a>
                        <?php } ?>
                        <?php if ($menuVideos == 'enable') { ?>
                        <a href="#" data-target="expuls-videos"><span class="dashicons dashicons-format-video"></span><span><?php echo esc_attr__( 'Videos', 'expuls' ); ?></span></a>
                        <?php } ?>
                    </div>
                </div>
                <a id="expuls-mobile-btn" href="#"><span class="dashicons dashicons-menu-alt3"></span></a>
            </div>
            <div id="expuls-content">
                <?php if ($menuPhotos == 'enable') { ?>
                <div id="expuls-photos" class="expuls-page">
                    <div class="expuls-search-wrap">
                        <div class="expuls-search-filters">
                            <select id="expuls-photo-orientation" class="expuls-select" autocomplete="off" disabled>
                                <option value="" selected><?php echo esc_html__('All Orientations', 'expuls'); ?></option>
                                <option value="landscape"><?php echo esc_html__('Landscape', 'expuls'); ?></option>
                                <option value="portrait"><?php echo esc_html__('Portrait', 'expuls'); ?></option>
                                <option value="square"><?php echo esc_html__('Square', 'expuls'); ?></option>
                            </select>
                            <select id="expuls-photo-color" class="expuls-select" autocomplete="off" disabled>
                                <option value="" selected><?php echo esc_html__('All Colors', 'expuls'); ?></option>
                                <option value="white"><?php echo esc_html__('White', 'expuls'); ?></option>
                                <option value="black"><?php echo esc_html__('Black', 'expuls'); ?></option>
                                <option value="gray"><?php echo esc_html__('Gray', 'expuls'); ?></option>
                                <option value="brown"><?php echo esc_html__('Brown', 'expuls'); ?></option>
                                <option value="blue"><?php echo esc_html__('Blue', 'expuls'); ?></option>
                                <option value="turquoise"><?php echo esc_html__('Turquoise', 'expuls'); ?></option>
                                <option value="red"><?php echo esc_html__('Red', 'expuls'); ?></option>
                                <option value="violet"><?php echo esc_html__('Violet', 'expuls'); ?></option>
                                <option value="pink"><?php echo esc_html__('Pink', 'expuls'); ?></option>
                                <option value="orange"><?php echo esc_html__('Orange', 'expuls'); ?></option>
                                <option value="yellow"><?php echo esc_html__('Yellow', 'expuls'); ?></option>
                                <option value="green"><?php echo esc_html__('Green', 'expuls'); ?></option>
                            </select>
                        </div>
                        <div class="expuls-search-box">
                            <input id="expuls-photo-keyword" type="search" class="expuls-form-field" placeholder="<?php echo esc_html__('Enter a keyword...', 'expuls'); ?>" autocomplete="off" />
                            <button id="expuls-photo-search" type="button" class="expuls-btn primary"><span class="dashicons dashicons-search"></span></button>
                        </div>
                    </div>
                    <div id="expuls-photo-loader" class="expuls-loader-wrap"><div class="expuls-loader"></div></div>
                    <div id="expuls-photos-output">
                    <div id="expuls-photos-grid-notice" class="expuls-notice expuls-notice-warning"></div>
                        <div id="expuls-photos-grid" class="expuls-grid">
                            <?php $this->curated(1); ?>
                        </div>
                        <button id="expuls-photos-loadmore" type="button" class="expuls-btn expuls-lg-btn" autocomplete="off" data-page="1"><?php echo esc_html__('Load More', 'expuls'); ?></button>
                    </div>
                </div>
                <?php } ?>
                <?php if ($menuVideos == 'enable') { ?>
                <div id="expuls-videos" class="expuls-page">
                    <div class="expuls-search-wrap">
                        <div class="expuls-search-filters">
                            <select id="expuls-video-orientation" class="expuls-select" autocomplete="off" disabled>
                                <option value="" selected><?php echo esc_html__('All Orientations', 'expuls'); ?></option>
                                <option value="landscape"><?php echo esc_html__('Landscape', 'expuls'); ?></option>
                                <option value="portrait"><?php echo esc_html__('Portrait', 'expuls'); ?></option>
                                <option value="square"><?php echo esc_html__('Square', 'expuls'); ?></option>
                            </select>
                            <select id="expuls-video-size" class="expuls-select" autocomplete="off" disabled>
                                <option value="" selected><?php echo esc_html__('All Sizes', 'expuls'); ?></option>
                                <option value="large"><?php echo esc_html__('Large (4K)', 'expuls'); ?></option>
                                <option value="medium"><?php echo esc_html__('Medium (Full HD)', 'expuls'); ?></option>
                                <option value="small"><?php echo esc_html__('Small (HD)', 'expuls'); ?></option>
                            </select>
                        </div>
                        <div class="expuls-search-box">
                            <input id="expuls-video-keyword" type="search" class="expuls-form-field" placeholder="<?php echo esc_html__('Enter a keyword...', 'expuls'); ?>" autocomplete="off" />
                            <button id="expuls-video-search" type="button" class="expuls-btn primary"><span class="dashicons dashicons-search"></span></button>
                        </div>
                    </div>
                    <div id="expuls-video-loader" class="expuls-loader-wrap"><div class="expuls-loader"></div></div>
                    <div id="expuls-videos-output">
                    <div id="expuls-videos-grid-notice" class="expuls-notice expuls-notice-warning"></div>
                        <div id="expuls-videos-grid" class="expuls-grid">
                            <?php $this->popular(1); ?>
                        </div>
                        <button id="expuls-videos-loadmore" type="button" class="expuls-btn expuls-lg-btn" autocomplete="off" data-page="1"><?php echo esc_html__('Load More', 'expuls'); ?></button>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    <?php } 
    }

    /**
	 * Get Image Sizes
	 */
    public function get_img_sizes() {
        $sizes = array(
            'large2x' => esc_html__( 'Large 2x', 'expuls' ),
            'large' => esc_html__( 'Large', 'expuls' ),
            'medium' => esc_html__( 'Medium', 'expuls' ),
            'portrait' => esc_html__( 'Portrait', 'expuls' ),
            'landscape' => esc_html__( 'Landscape', 'expuls' ),
            'tiny' => esc_html__( 'Tiny', 'expuls' ),
            'original' => esc_html__( 'Original', 'expuls' ),
        );
        return $sizes;
    }

    /**
	 * Curated Photos
	 */
    public function curated($page) {
        if (wp_doing_ajax()) {
            if ( ! wp_verify_nonce( $_POST['nonce'], 'expuls-nonce' ) ) {
                wp_die(esc_html__('Security Error!', 'expuls'));
            }
        }
        // Get The Api Key
        $getApiKey =  ExpulsSettings::get_option('expuls_api', '');
        $apiKey = trim($getApiKey);
        $error = '';
        $defaultImgSize =  ExpulsSettings::get_option('expuls_img_size', 'large2x');
        $thumbSize =  ExpulsSettings::get_option('expuls_thumb_size', 'medium');
        $pagination =  ExpulsSettings::get_option('expuls_pagination', 20);
        $lang =  ExpulsSettings::get_option('expuls_lang', 'en-US');
        $curlURL = "https://api.pexels.com/v1/curated?locale=" . $lang . "&page=" . $page . "&per_page=" . $pagination;
        $transient_value = get_transient($curlURL);

        if (false !== $transient_value){
            $response =	get_transient($curlURL);
        } else {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $curlURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: {$apiKey}"
                )
            ));
            $response = curl_exec($ch);
            if (curl_errno($ch) > 0) { 
                $error = esc_html__( 'Error connecting to API: ', 'expuls' ) . curl_error($ch);
            }
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($responseCode === 429) {
                $error = esc_html__( 'Too many requests!', 'expuls' );
            }
            if ($responseCode !== 200) {
                $error = "HTTP {$responseCode}";
            }
            if (empty($error)) {
                set_transient( $curlURL, $response, 24 * HOUR_IN_SECONDS );
            }
        }

        $data = json_decode($response);
        if ($data === false && json_last_error() !== JSON_ERROR_NONE) {
            $error = esc_html__( 'Error parsing response', 'expuls' );
        }

        if (empty($error)) {
            $photos = $data->photos;

            foreach ( $photos as $photo ) {
                $id = $photo->id;
                $url = $photo->url;
                $src = $photo->src;
                $thumb = $src->$thumbSize;
                $alt = $photo->alt;
                $sizes = $this->get_img_sizes();
                $original = $src->original;
                $large2x = $src->large2x;
                $large = $src->large;
                $medium = $src->medium;
                $small = $src->small;
                $portrait = $src->portrait;
                $landscape = $src->landscape;
                $tiny = $src->tiny;
                ?>
                <div class="expuls-masonry-item" title="<?php echo esc_attr($alt); ?>">
                    <a href="<?php echo esc_url($original); ?>" class="expuls-preview" data-caption="<?php echo esc_attr($alt); ?>" data-fancybox><span class="dashicons dashicons-search"></span></a>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="expuls-info"><span class="dashicons dashicons-admin-links"></span></a>
                    <a href="#" class="expuls-cancel"><span class="dashicons dashicons-no-alt"></span></a>
                    <div class="expuls-masonry-item-inner">
                        <img src="<?php echo esc_url($thumb); ?>" />
                        <div class="expuls-item-info">
                            <select class="expuls-select" autocomplete="off">
                                <?php
                                foreach ( $sizes as $id => $name ) {
                                    $selected = '';
                                    if ($id == $defaultImgSize) {
                                        $selected = 'selected';
                                    }
                                    echo '<option value="' . $id . '" ' . $selected . '>' . $name . '</option>';
                                }
                                ?>
                            </select>
                            <button type="button" class="expuls-btn expuls-import expuls-import-img" autocomplete="off" data-large2x="<?php echo esc_url($large2x); ?>" data-large="<?php echo esc_url($large); ?>" data-medium="<?php echo esc_url($medium); ?>" data-small="<?php echo esc_url($small); ?>" data-portrait="<?php echo esc_url($portrait); ?>" data-landscape="<?php echo esc_url($landscape); ?>" data-tiny="<?php echo esc_url($tiny); ?>" data-original="<?php echo esc_url($original); ?>" data-id="<?php echo esc_attr($id); ?>"><span class="dashicons dashicons-download"></span></button>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="expuls-notice expuls-notice-danger">' . $error . '</div>';
        }
    }

    /**
	 * Photo Search
	 */
    public function photo_search() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'expuls-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'expuls'));
        }
        // Get The Api Key
        $getApiKey =  ExpulsSettings::get_option('expuls_api', '');
        $apiKey = trim($getApiKey);
        $error = '';
        $curlURL = '';
        $defaultImgSize =  ExpulsSettings::get_option('expuls_img_size', 'large2x');
        $thumbSize =  ExpulsSettings::get_option('expuls_thumb_size', 'medium');
        $pagination =  ExpulsSettings::get_option('expuls_pagination', 20);
        $lang =  ExpulsSettings::get_option('expuls_lang', 'en-US');
        $query = sanitize_text_field($_POST['keyword']);
        $orientation = sanitize_text_field($_POST['orientation']);
        $color = sanitize_text_field($_POST['color']);
        $page = sanitize_text_field($_POST['page']);

        if (empty($query) && empty($orientation) && empty($color)) {
            $this->curated($page);
        } else {
            $curlURL = "https://api.pexels.com/v1/search?";
            $curlURL .= 'locale=' . $lang . '&';
            if (!empty($query)) {
                $query = str_replace(' ', '%20', $query);
                $curlURL .= 'query=' . $query . '&';
            }
            if (!empty($orientation)) {
                $curlURL .= 'orientation=' . $orientation . '&';
            }
            if (!empty($color)) {
                $curlURL .= 'color=' . $color . '&';
            }
            $curlURL .= 'page=' . $page . '&per_page=' . $pagination;

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $curlURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: {$apiKey}"
                )
            ));
        
            $response = curl_exec($ch);
            if (curl_errno($ch) > 0) { 
                $error = esc_html__( 'Error connecting to API: ', 'expuls' ) . curl_error($ch);
            }
        
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($responseCode !== 200) {
                $error = "HTTP {$responseCode}";
            }
            if ($responseCode === 429) {
                $error = esc_html__( 'Too many requests!', 'palleon' );
            }
            $data = json_decode($response);
            if ($data === false && json_last_error() !== JSON_ERROR_NONE) {
                $error = esc_html__( 'Error parsing response', 'expuls' );
            }

            if (empty($error)) {
                $photos = $data->photos;

                if ($photos == array()) {
                    echo '404';
                } else {
                    $photos = $data->photos;

                    foreach ( $photos as $photo ) {
                        $id = $photo->id;
                        $url = $photo->url;
                        $src = $photo->src;
                        $thumb = $src->$thumbSize;
                        $original = $src->original;
                        $alt = $photo->alt;
                        $sizes = $this->get_img_sizes();
                        $original = $src->original;
                        $large2x = $src->large2x;
                        $large = $src->large;
                        $medium = $src->medium;
                        $small = $src->small;
                        $portrait = $src->portrait;
                        $landscape = $src->landscape;
                        $tiny = $src->tiny;
                        ?>
                        <div class="expuls-masonry-item" title="<?php echo esc_attr($alt); ?>">
                        <a href="<?php echo esc_url($original); ?>" class="expuls-preview" data-caption="<?php echo esc_attr($alt); ?>" data-fancybox><span class="dashicons dashicons-search"></span></a>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" class="expuls-info"><span class="dashicons dashicons-admin-links"></span></a>
                        <a href="#" class="expuls-cancel"><span class="dashicons dashicons-no-alt"></span></a>
                            <div class="expuls-masonry-item-inner">
                                <img src="<?php echo esc_url($thumb); ?>" />
                                <div class="expuls-item-info">
                                    <select class="expuls-select" autocomplete="off">
                                        <?php
                                        foreach ( $sizes as $id => $name ) {
                                            $selected = '';
                                            if ($id == $defaultImgSize) {
                                                $selected = 'selected';
                                            }
                                            echo '<option value="' . $id . '" ' . $selected . '>' . $name . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <button type="button" class="expuls-btn expuls-import expuls-import-img" autocomplete="off" data-large2x="<?php echo esc_url($large2x); ?>" data-large="<?php echo esc_url($large); ?>" data-medium="<?php echo esc_url($medium); ?>" data-small="<?php echo esc_url($small); ?>" data-portrait="<?php echo esc_url($portrait); ?>" data-landscape="<?php echo esc_url($landscape); ?>" data-tiny="<?php echo esc_url($tiny); ?>" data-original="<?php echo esc_url($original); ?>" data-id="<?php echo esc_attr($id); ?>"><span class="dashicons dashicons-download"></span></button>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            } else {
                echo '<div class="expuls-notice expuls-notice-danger">' . $error . '</div>';
            }
        }

        wp_die();
    }

    /**
	 * Import Image
	 */
    public function import_image(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'expuls-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'expuls'));
        }

        $url = esc_url_raw($_POST['url']);
        $original = esc_url_raw($_POST['original']);
        $filetype = wp_check_filetype($original);
        $filename = sanitize_text_field($_POST['filename']) . '.' . $filetype['ext'];

        $response = wp_remote_get($url, array( 'timeout' => 10 ) );
        if( !is_wp_error( $response ) ){
            $bits = wp_remote_retrieve_body( $response );
            $upload = wp_upload_bits( $filename, null, $bits );
            $attachment = array(
                'guid'           => $url, 
                'post_mime_type' => $filetype['type'],
                'post_title'     => $filename,
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
    
            $attachment_id = wp_insert_attachment( $attachment, $upload['file'], 0 );
            wp_update_attachment_metadata(
                $attachment_id,
                wp_generate_attachment_metadata( $attachment_id, $upload['file'], 0 )
            );
        }

        wp_die();
    }

    /**
	 * Popular Videos
	 */
    public function popular($page) {
        if (wp_doing_ajax()) {
            if ( ! wp_verify_nonce( $_POST['nonce'], 'expuls-nonce' ) ) {
                wp_die(esc_html__('Security Error!', 'expuls'));
            }
        }
        // Get The Api Key
        $getApiKey =  ExpulsSettings::get_option('expuls_api', '');
        $apiKey = trim($getApiKey);
        $error = '';
        $pagination =  ExpulsSettings::get_option('expuls_video_pagination', 20);
        $lang =  ExpulsSettings::get_option('expuls_lang', 'en-US');
        $curlURL = "https://api.pexels.com/videos/popular?locale=" . $lang . "&page=" . $page . "&per_page=" . $pagination;
        $transient_value = get_transient($curlURL);

        if (false !== $transient_value){
            $response =	get_transient($curlURL);
        } else {
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $curlURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: {$apiKey}"
                )
            ));
            $response = curl_exec($ch);
            if (curl_errno($ch) > 0) { 
                $error = esc_html__( 'Error connecting to API: ', 'expuls' ) . curl_error($ch);
            }
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($responseCode !== 200) {
                $error = "HTTP {$responseCode}";
            }
            if ($responseCode === 429) {
                $error = esc_html__( 'Too many requests!', 'palleon' );
            }
            if (empty($error)) {
                set_transient( $curlURL, $response, 24 * HOUR_IN_SECONDS );
            }
        }

        $data = json_decode($response);
        if ($data === false && json_last_error() !== JSON_ERROR_NONE) {
            $error = esc_html__( 'Error parsing response', 'expuls' );
        }

        if (empty($error)) {
            $videos = $data->videos;

            foreach ( $videos as $video ) {
                $id = $video->id;
                $url = $video->url;
                $thumb = $video->image;
                $duration = $video->duration;
                $video_files = $video->video_files;
                $preview = $video->video_files[0];
                ?>
                <div class="expuls-masonry-item">
                    <a href="<?php echo esc_url($preview->link); ?>" class="expuls-preview" data-fancybox><span class="dashicons dashicons-search"></span></a>
                    <a href="<?php echo esc_url($url); ?>" target="_blank" class="expuls-info"><span class="dashicons dashicons-admin-links"></span></a>
                    <a href="#" class="expuls-cancel"><span class="dashicons dashicons-no-alt"></span></a>
                    <div class="expuls-masonry-item-inner">
                        <img src="<?php echo esc_url($thumb); ?>" />
                        <div class="expuls-video-duration"><?php echo gmdate('i:s', $duration); ?></div>
                        <div class="expuls-item-info">
                            <select class="expuls-select" autocomplete="off">
                                <?php
                                foreach ( $video_files as $video ) {
                                    $label = $video->quality;
                                    if (!empty($video->width)) {
                                        $label = $video->width . 'x' . $video->height;
                                    }
                                    echo '<option data-type="' . esc_attr($video->file_type) . '" value="' . esc_url($video->link) . '">' . $label . '</option>';
                                }
                                ?>
                            </select>
                            <button type="button" class="expuls-btn expuls-import expuls-import-video" autocomplete="off"><span class="dashicons dashicons-download"></span></button>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="expuls-notice expuls-notice-danger">' . $error . '</div>';
        }
    }

    /**
	 * Video Search
	 */
    public function video_search() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'expuls-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'expuls'));
        }
        // Get The Api Key
        $getApiKey =  ExpulsSettings::get_option('expuls_api', '');
        $apiKey = trim($getApiKey);
        $error = '';
        $curlURL = '';
        $pagination =  ExpulsSettings::get_option('expuls_video_pagination', 20);
        $lang =  ExpulsSettings::get_option('expuls_lang', 'en-US');
        $query = sanitize_text_field($_POST['keyword']);
        $orientation = sanitize_text_field($_POST['orientation']);
        $size = sanitize_text_field($_POST['size']);
        $page = sanitize_text_field($_POST['page']);

        if (empty($query) && empty($orientation) && empty($size)) {
            $this->popular($page);
        } else {
            $curlURL = "https://api.pexels.com/videos/search?";
            $curlURL .= 'locale=' . $lang . '&';
            if (!empty($query)) {
                $query = str_replace(' ', '%20', $query);
                $curlURL .= 'query=' . $query . '&';
            }
            if (!empty($orientation)) {
                $curlURL .= 'orientation=' . $orientation . '&';
            }
            if (!empty($size)) {
                $curlURL .= 'size=' . $size . '&';
            }
            $curlURL .= 'page=' . $page . '&per_page=' . $pagination;

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $curlURL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => array(
                    "Authorization: {$apiKey}"
                )
            ));
        
            $response = curl_exec($ch);
            if (curl_errno($ch) > 0) { 
                $error = esc_html__( 'Error connecting to API: ', 'expuls' ) . curl_error($ch);
            }
        
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($responseCode !== 200) {
                $error = "HTTP {$responseCode}";
            }
            if ($responseCode === 429) {
                $error = esc_html__( 'Too many requests!', 'palleon' );
            }

            $data = json_decode($response);
            if ($data === false && json_last_error() !== JSON_ERROR_NONE) {
                $error = esc_html__( 'Error parsing response', 'expuls' );
            }

            if (empty($error)) {
                $videos = $data->videos;

                if ($videos == array()) {
                    echo '404';
                } else {
                    $videos = $data->videos;

                    foreach ( $videos as $video ) {
                        $id = $video->id;
                        $url = $video->url;
                        $thumb = $video->image;
                        $duration = $video->duration;
                        $video_files = $video->video_files;
                        $preview = $video->video_files[0];
                        ?>
                        <div class="expuls-masonry-item">
                            <a href="<?php echo esc_url($preview->link); ?>" class="expuls-preview" data-fancybox><span class="dashicons dashicons-search"></span></a>
                            <a href="<?php echo esc_url($url); ?>" target="_blank" class="expuls-info"><span class="dashicons dashicons-admin-links"></span></a>
                            <a href="#" class="expuls-cancel"><span class="dashicons dashicons-no-alt"></span></a>
                            <div class="expuls-masonry-item-inner">
                                <img src="<?php echo esc_url($thumb); ?>" />
                                <div class="expuls-video-duration"><?php echo gmdate('i:s', $duration); ?></div>
                                <div class="expuls-item-info">
                                    <select class="expuls-select" autocomplete="off">
                                        <?php
                                        foreach ( $video_files as $video ) {
                                            $label = $video->quality;
                                            if (!empty($video->width)) {
                                                $label = $video->width . 'x' . $video->height;
                                            }
                                            echo '<option data-type="' . esc_attr($video->file_type) . '" value="' . esc_url($video->link) . '">' . $label . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <button type="button" class="expuls-btn expuls-import expuls-import-video" autocomplete="off"><span class="dashicons dashicons-download"></span></button>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
            } else {
                echo '<div class="expuls-notice expuls-notice-danger">' . $error . '</div>';
            }
        }

        wp_die();
    }

    /**
	 * Import Video
	 */
    public function import_video(){
        if ( ! wp_verify_nonce( $_POST['nonce'], 'expuls-nonce' ) ) {
            wp_die(esc_html__('Security Error!', 'expuls'));
        }

        $url = esc_url_raw($_POST['url']);
        $type = sanitize_mime_type($_POST['type']);
        $ext = $id = substr($type, strrpos($type, '/') + 1);
        $filename = sanitize_text_field($_POST['filename']) . '.' . $ext;

        $response = wp_remote_get($url, array( 'timeout' => 10 ) );
        if( !is_wp_error( $response ) ){
            $bits = wp_remote_retrieve_body( $response );
            $upload = wp_upload_bits( $filename, null, $bits );
            $attachment = array(
                'guid'           => $url, 
                'post_mime_type' => $type,
                'post_title'     => $filename,
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
    
            $attachment_id = wp_insert_attachment( $attachment, $upload['file'], 0 );
            wp_update_attachment_metadata(
                $attachment_id,
                wp_generate_attachment_metadata( $attachment_id, $upload['file'], 0 )
            );
        }

        wp_die();
    }
}

/**
 * Returns the main instance of the class
 */
function Expuls() {  
	return Expuls::instance();
}
// Global for backwards compatibility
$GLOBALS['Expuls'] = Expuls();