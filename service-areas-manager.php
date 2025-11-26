<?php
/**
 * Plugin Name: Service Areas Manager
 * Description: Manage and display service areas with flags - WPML Compatible
 * Version: 2.0
 * Author: nossq
 * Text Domain: service-areas-manager
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit;

// Load text domain for translations
function service_areas_load_textdomain() {
    load_plugin_textdomain('service-areas-manager', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'service_areas_load_textdomain');

// Register Custom Post Type
function service_areas_post_type() {
    register_post_type('service_area', [
        'labels' => [
            'name' => __('Service Areas', 'service-areas-manager'),
            'singular_name' => __('Service Area', 'service-areas-manager'),
            'add_new' => __('Add New Area', 'service-areas-manager'),
            'add_new_item' => __('Add New Service Area', 'service-areas-manager'),
            'edit_item' => __('Edit Service Area', 'service-areas-manager'),
            'all_items' => __('All Areas', 'service-areas-manager')
        ],
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-location',
        'supports' => ['title', 'thumbnail'],
        'menu_position' => 30
    ]);
}
add_action('init', 'service_areas_post_type');

// Add Meta Boxes
function service_areas_meta_boxes() {
    add_meta_box(
        'area_details',
        __('Area Details', 'service-areas-manager'),
        'service_areas_details_callback',
        'service_area',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'service_areas_meta_boxes');

function service_areas_details_callback($post) {
    wp_nonce_field('service_area_details', 'service_area_details_nonce');
    
    $order = get_post_meta($post->ID, '_area_order', true);
    $status = get_post_meta($post->ID, '_area_status', true);
    $custom_image = get_post_meta($post->ID, '_area_custom_image', true);
    $link_url = get_post_meta($post->ID, '_area_link_url', true);
    
    if (empty($status)) $status = 'available';
    ?>
    
    <table class="form-table">
        <tr>
            <th><label for="area_order"><?php _e('Display Order', 'service-areas-manager'); ?></label></th>
            <td>
                <input type="number" id="area_order" name="area_order" value="<?php echo esc_attr($order); ?>" min="0" class="regular-text">
                <p class="description"><?php _e('Lower numbers appear first (e.g., 1, 2, 3...)', 'service-areas-manager'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="area_status"><?php _e('Availability Status', 'service-areas-manager'); ?></label></th>
            <td>
                <select id="area_status" name="area_status" class="regular-text">
                    <option value="available" <?php selected($status, 'available'); ?>><?php _e('Available Now', 'service-areas-manager'); ?></option>
                    <option value="unavailable" <?php selected($status, 'unavailable'); ?>><?php _e('Not Available', 'service-areas-manager'); ?></option>
                    <option value="coming_soon" <?php selected($status, 'coming_soon'); ?>><?php _e('Coming Soon', 'service-areas-manager'); ?></option>
                </select>
                <p class="description"><?php _e('Select the service availability status', 'service-areas-manager'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="area_link_url"><?php _e('Page Link URL', 'service-areas-manager'); ?></label></th>
            <td>
                <input type="url" id="area_link_url" name="area_link_url" value="<?php echo esc_url($link_url); ?>" class="regular-text" placeholder="https://example.com/page">
                <p class="description"><?php _e('Enter the full URL where users will be redirected when clicking this area (e.g., https://yoursite.com/dubai-services)', 'service-areas-manager'); ?></p>
            </td>
        </tr>
        
        <tr>
            <th><label for="area_custom_image"><?php _e('Custom Image (Optional)', 'service-areas-manager'); ?></label></th>
            <td>
                <div class="area-image-wrapper">
                    <input type="hidden" id="area_custom_image" name="area_custom_image" value="<?php echo esc_attr($custom_image); ?>">
                    <button type="button" class="button area-upload-image"><?php _e('Choose Image', 'service-areas-manager'); ?></button>
                    <button type="button" class="button area-remove-image" style="<?php echo $custom_image ? '' : 'display:none;'; ?>"><?php _e('Remove Image', 'service-areas-manager'); ?></button>
                    <div class="area-image-preview" style="margin-top: 10px;">
                        <?php if ($custom_image): 
                            echo wp_get_attachment_image($custom_image, 'thumbnail');
                        endif; ?>
                    </div>
                </div>
                <p class="description"><?php _e('Upload a custom image instead of the UAE flag (120x120px recommended)', 'service-areas-manager'); ?></p>
            </td>
        </tr>
    </table>
    
    <style>
        .area-image-preview img {
            max-width: 120px;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        var frame;
        
        $('.area-upload-image').on('click', function(e) {
            e.preventDefault();
            
            if (frame) {
                frame.open();
                return;
            }
            
            frame = wp.media({
                title: '<?php _e('Select Area Image', 'service-areas-manager'); ?>',
                button: {
                    text: '<?php _e('Use this image', 'service-areas-manager'); ?>'
                },
                multiple: false
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#area_custom_image').val(attachment.id);
                $('.area-image-preview').html('<img src="' + attachment.url + '" style="max-width:120px;height:auto;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">');
                $('.area-remove-image').show();
            });
            
            frame.open();
        });
        
        $('.area-remove-image').on('click', function(e) {
            e.preventDefault();
            $('#area_custom_image').val('');
            $('.area-image-preview').html('');
            $(this).hide();
        });
    });
    </script>
    <?php
}

// Save Meta Data
function service_areas_save_meta($post_id) {
    if (!isset($_POST['service_area_details_nonce'])) return;
    if (!wp_verify_nonce($_POST['service_area_details_nonce'], 'service_area_details')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    if (isset($_POST['area_order'])) {
        update_post_meta($post_id, '_area_order', sanitize_text_field($_POST['area_order']));
    }
    
    if (isset($_POST['area_status'])) {
        update_post_meta($post_id, '_area_status', sanitize_text_field($_POST['area_status']));
    }
    
    if (isset($_POST['area_custom_image'])) {
        update_post_meta($post_id, '_area_custom_image', sanitize_text_field($_POST['area_custom_image']));
    }
    
    if (isset($_POST['area_link_url'])) {
        update_post_meta($post_id, '_area_link_url', esc_url_raw($_POST['area_link_url']));
    }
}
add_action('save_post_service_area', 'service_areas_save_meta');

// Enqueue Media Uploader
function service_areas_admin_scripts($hook) {
    if ('post.php' == $hook || 'post-new.php' == $hook) {
        global $post;
        if ($post && $post->post_type === 'service_area') {
            wp_enqueue_media();
        }
    }
}
add_action('admin_enqueue_scripts', 'service_areas_admin_scripts');

// Register Settings
function service_areas_settings() {
    add_options_page(
        __('Service Areas Settings', 'service-areas-manager'),
        __('Service Areas', 'service-areas-manager'),
        'manage_options',
        'service-areas-settings',
        'service_areas_settings_page'
    );
}
add_action('admin_menu', 'service_areas_settings');

function service_areas_settings_page() {
    if (isset($_POST['service_areas_submit'])) {
        check_admin_referer('service_areas_settings');
        update_option('service_areas_title', sanitize_text_field($_POST['title']));
        update_option('service_areas_subtitle', sanitize_text_field($_POST['subtitle']));
        echo '<div class="updated"><p>' . __('Settings saved successfully!', 'service-areas-manager') . '</p></div>';
    }
    
    $title = get_option('service_areas_title', __('Our Service Areas', 'service-areas-manager'));
    $subtitle = get_option('service_areas_subtitle', __('We cover all Emirates with premium professional services', 'service-areas-manager'));
    ?>
    <div class="wrap">
        <h1><?php _e('Service Areas Settings', 'service-areas-manager'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('service_areas_settings'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="title"><?php _e('Main Title', 'service-areas-manager'); ?></label></th>
                    <td><input type="text" id="title" name="title" value="<?php echo esc_attr($title); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="subtitle"><?php _e('Subtitle', 'service-areas-manager'); ?></label></th>
                    <td><input type="text" id="subtitle" name="subtitle" value="<?php echo esc_attr($subtitle); ?>" class="regular-text"></td>
                </tr>
            </table>
            <p class="description"><?php _e('Go to Service Areas > Add New Area to manage locations', 'service-areas-manager'); ?></p>
            <?php submit_button(__('Save Settings', 'service-areas-manager'), 'primary', 'service_areas_submit'); ?>
        </form>
    </div>
    <?php
}

// Shortcode
function service_areas_shortcode() {
    $title = get_option('service_areas_title', __('Our Service Areas', 'service-areas-manager'));
    $subtitle = get_option('service_areas_subtitle', __('We cover all Emirates with premium professional services', 'service-areas-manager'));
    
    $areas = new WP_Query([
        'post_type' => 'service_area',
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'meta_key' => '_area_order',
        'order' => 'ASC'
    ]);
    
    ob_start();
    ?>
    <div class="service-areas-wrapper">
        <div class="container">
            <div class="header">
                <h1><?php echo esc_html($title); ?></h1>
                <p><?php echo esc_html($subtitle); ?></p>
            </div>

            <div class="areas-grid">
                <?php if ($areas->have_posts()): 
                    while ($areas->have_posts()): $areas->the_post(); 
                        $status = get_post_meta(get_the_ID(), '_area_status', true);
                        $custom_image = get_post_meta(get_the_ID(), '_area_custom_image', true);
                        $link_url = get_post_meta(get_the_ID(), '_area_link_url', true);
                        
                        if (empty($status)) $status = 'available';
                        
                        $status_texts = [
                            'available' => __('Available Now', 'service-areas-manager'),
                            'unavailable' => __('Not Available', 'service-areas-manager'),
                            'coming_soon' => __('Coming Soon', 'service-areas-manager')
                        ];
                        
                        $status_classes = [
                            'available' => 'status-available',
                            'unavailable' => 'status-unavailable',
                            'coming_soon' => 'status-coming'
                        ];
                        
                        $card_tag = $link_url ? 'a' : 'div';
                        $card_attrs = $link_url ? 'href="' . esc_url($link_url) . '"' : '';
                        ?>
                    <<?php echo $card_tag; ?> <?php echo $card_attrs; ?> class="area-card" <?php echo $link_url ? 'style="text-decoration:none;color:inherit;"' : ''; ?>>
                        <div class="flag-container">
                            <?php if ($custom_image): ?>
                                <?php echo wp_get_attachment_image($custom_image, 'thumbnail', false, ['class' => 'custom-area-image']); ?>
                            <?php else: ?>
                                <div class="flag">
                                    <div class="flag-stripe red"></div>
                                    <div style="flex: 1; display: flex; flex-direction: column;">
                                        <div class="flag-stripe green"></div>
                                        <div class="flag-stripe white"></div>
                                        <div class="flag-stripe black"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="area-name">
                            <h2><?php the_title(); ?></h2>
                        </div>
                        <div class="decorative-line"></div>
                        <div style="text-align: center;">
                            <span class="status-badge <?php echo esc_attr($status_classes[$status]); ?>">
                                <?php echo esc_html($status_texts[$status]); ?>
                            </span>
                        </div>
                    </<?php echo $card_tag; ?>>
                    <?php endwhile; 
                    wp_reset_postdata();
                else: ?>
                    <p><?php _e('No service areas found. Please add some areas first.', 'service-areas-manager'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('service_areas', 'service_areas_shortcode');

// Enqueue CSS & JS
function service_areas_enqueue() {
    wp_enqueue_style('service-areas-css', plugin_dir_url(__FILE__) . 'assets/style.css', [], '2.0');
    wp_enqueue_script('service-areas-js', plugin_dir_url(__FILE__) . 'assets/script.js', ['jquery'], '2.0', true);
}
add_action('wp_enqueue_scripts', 'service_areas_enqueue');

// Register Elementor Widget
function register_service_areas_elementor_widget($widgets_manager) {
    require_once(plugin_dir_path(__FILE__) . 'elementor-widget.php');
    $widgets_manager->register(new \Service_Areas_Elementor_Widget());
}
add_action('elementor/widgets/register', 'register_service_areas_elementor_widget');

// WPML Compatibility
function service_areas_wpml_register_strings() {
    if (function_exists('icl_register_string')) {
        icl_register_string('service-areas-manager', 'Main Title', get_option('service_areas_title', 'Our Service Areas'));
        icl_register_string('service-areas-manager', 'Subtitle', get_option('service_areas_subtitle', 'We cover all Emirates'));
    }
}
add_action('admin_init', 'service_areas_wpml_register_strings');