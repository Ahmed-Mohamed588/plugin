<?php
if (!defined('ABSPATH')) exit;

class Service_Areas_Elementor_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'service_areas';
    }

    public function get_title() {
        return __('Service Areas', 'service-areas-manager');
    }

    public function get_icon() {
        return 'eicon-map-pin';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content Settings', 'service-areas-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Title', 'service-areas-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => get_option('service_areas_title', __('Our Service Areas', 'service-areas-manager')),
            ]
        );

        $this->add_control(
            'subtitle',
            [
                'label' => __('Subtitle', 'service-areas-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => get_option('service_areas_subtitle', __('We cover all Emirates', 'service-areas-manager')),
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'service-areas-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'service-areas-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2d3748',
                'selectors' => [
                    '{{WRAPPER}} .header h1' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'card_bg_color',
            [
                'label' => __('Card Background', 'service-areas-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .area-card' => 'background-color: {{VALUE}}',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $areas = new WP_Query([
            'post_type' => 'service_area',
            'posts_per_page' => -1,
            'orderby' => 'meta_value_num',
            'meta_key' => '_area_order',
            'order' => 'ASC'
        ]);
        ?>
        <div class="service-areas-wrapper">
            <div class="container">
                <div class="header">
                    <h1><?php echo esc_html($settings['title']); ?></h1>
                    <p><?php echo esc_html($settings['subtitle']); ?></p>
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
                        <p><?php _e('No service areas found.', 'service-areas-manager'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}