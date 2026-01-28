<?php
/**
 * Plugin Name: Simple Elementor Featured Image Widget
 * Description: Adds a simple Elementor widget to display the current post's featured image.
 * Version: 1.0.0
 * Author: Sakib Hasan
 * Author URI: https://profiles.wordpress.org/sakibhasan/
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

final class SEFIW_One_File_Plugin {
    const MIN_ELEMENTOR_VERSION = '3.0.0';

    public function __construct() {
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function init() {
        // Elementor loaded?
        if ( ! did_action('elementor/loaded') ) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        if ( defined('ELEMENTOR_VERSION') && ! version_compare( ELEMENTOR_VERSION, self::MIN_ELEMENTOR_VERSION, '>=' ) ) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Elementor widget register hook (newer versions)
        add_action('elementor/widgets/register', [$this, 'register_widget']);
        // Backward compatibility (older versions)
        add_action('elementor/widgets/widgets_registered', [$this, 'register_widget_legacy']);
    }

    public function admin_notice_missing_elementor() {
        if ( ! current_user_can('activate_plugins') ) return;
        echo '<div class="notice notice-warning"><p><strong>Simple Elementor Featured Image Widget</strong> requires Elementor to be installed and activated.</p></div>';
    }

    public function admin_notice_minimum_elementor_version() {
        if ( ! current_user_can('activate_plugins') ) return;
        echo '<div class="notice notice-warning"><p><strong>Simple Elementor Featured Image Widget</strong> requires Elementor version '
            . esc_html(self::MIN_ELEMENTOR_VERSION) . ' or greater.</p></div>';
    }

    public function register_widget( $widgets_manager ) {
        if ( ! sefiw_define_widget_class() ) {
            return;
        }

        if ( method_exists($widgets_manager, 'register') ) {
            $widgets_manager->register( new \SEFIW_Featured_Image_Widget() );
        } elseif ( method_exists($widgets_manager, 'register_widget_type') ) {
            // Very old Elementor
            $widgets_manager->register_widget_type( new \SEFIW_Featured_Image_Widget() );
        }
    }

    public function register_widget_legacy() {
        // Legacy hook doesn't pass manager, so fetch it
        if ( ! class_exists('\Elementor\Plugin') ) return;
        $plugin = \Elementor\Plugin::instance();
        if ( ! $plugin || ! isset($plugin->widgets_manager) ) return;

        $this->register_widget( $plugin->widgets_manager );
    }
}

function sefiw_define_widget_class() {
    if ( class_exists('SEFIW_Featured_Image_Widget') ) {
        return true;
    }
    if ( ! class_exists('\Elementor\Widget_Base') ) {
        return false;
    }

    class SEFIW_Featured_Image_Widget extends \Elementor\Widget_Base {

        public function get_name() { return 'sefiw_featured_image'; }
        public function get_title() { return __('Featured Image', 'sefiw'); }
        public function get_icon() { return 'eicon-featured-image'; }
        public function get_categories() { return ['basic']; }
        public function get_keywords() { return ['featured','image','thumbnail','post']; }

        protected function register_controls() {

            $this->start_controls_section(
                'section_content',
                [
                    'label' => __('Content', 'sefiw'),
                    'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $this->add_control(
                'image_size',
                [
                    'label'   => __('Image Size', 'sefiw'),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'default' => 'large',
                    'options' => [
                        'thumbnail'    => __('Thumbnail', 'sefiw'),
                        'medium'       => __('Medium', 'sefiw'),
                        'medium_large' => __('Medium Large', 'sefiw'),
                        'large'        => __('Large', 'sefiw'),
                        'full'         => __('Full', 'sefiw'),
                    ],
                ]
            );

            $this->add_control(
                'link_to_post',
                [
                    'label'        => __('Link to Image', 'sefiw'),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => __('Yes', 'sefiw'),
                    'label_off'    => __('No', 'sefiw'),
                    'return_value' => 'yes',
                    'default'      => '',
                ]
            );

            $this->add_control(
                'show_caption',
                [
                    'label'        => __('Show Caption', 'sefiw'),
                    'type'         => \Elementor\Controls_Manager::SWITCHER,
                    'label_on'     => __('Yes', 'sefiw'),
                    'label_off'    => __('No', 'sefiw'),
                    'return_value' => 'yes',
                    'default'      => '',
                ]
            );

            $this->add_control(
                'html_tag',
                [
                    'label'   => __('Wrapper Tag', 'sefiw'),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'default' => 'figure',
                    'options' => [
                        'div'     => 'div',
                        'figure'  => 'figure',
                        'section' => 'section',
                        'span'    => 'span',
                    ],
                ]
            );

            $this->end_controls_section();

            $this->start_controls_section(
                'section_style',
                [
                    'label' => __('Style', 'sefiw'),
                    'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_responsive_control(
                'align',
                [
                    'label' => __('Alignment', 'sefiw'),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => ['title' => __('Left', 'sefiw'), 'icon' => 'eicon-text-align-left'],
                        'center' => ['title' => __('Center', 'sefiw'), 'icon' => 'eicon-text-align-center'],
                        'right' => ['title' => __('Right', 'sefiw'), 'icon' => 'eicon-text-align-right'],
                    ],
                    'default' => 'center',
                    'selectors' => [
                        '{{WRAPPER}} .sefiw-featured-image' => 'text-align: {{VALUE}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'max_width',
                [
                    'label' => __('Max Width', 'sefiw'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px', '%', 'vw'],
                    'range' => [
                        'px' => ['min' => 50, 'max' => 2000],
                        '%'  => ['min' => 5,  'max' => 100],
                        'vw' => ['min' => 5,  'max' => 100],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .sefiw-featured-image img' => 'max-width: {{SIZE}}{{UNIT}}; height: auto;',
                    ],
                ]
            );

            $this->add_control(
                'border_radius',
                [
                    'label' => __('Border Radius', 'sefiw'),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => ['px', '%'],
                    'range' => [
                        'px' => ['min' => 0, 'max' => 200],
                        '%'  => ['min' => 0, 'max' => 50],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .sefiw-featured-image img' => 'border-radius: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->end_controls_section();
        }

        protected function render() {
            $settings = $this->get_settings_for_display();

            // Try to get the current post ID safely
            $post_id = get_the_ID();
            if ( ! $post_id && function_exists('get_queried_object_id') ) {
                $post_id = get_queried_object_id();
            }

            $tag = ! empty($settings['html_tag']) ? tag_escape($settings['html_tag']) : 'div';

            echo '<' . $tag . ' class="sefiw-featured-image">';

            if ( ! $post_id || ! has_post_thumbnail($post_id) ) {
                echo '<div class="sefiw-no-image">' . esc_html__('No featured image found.', 'sefiw') . '</div>';
                echo '</' . $tag . '>';
                return;
            }

            $thumb_id = get_post_thumbnail_id($post_id);
            $size = ! empty($settings['image_size']) ? $settings['image_size'] : 'large';

            $img_html = wp_get_attachment_image(
                $thumb_id,
                $size,
                false,
                [
                    'class' => 'sefiw-img',
                    'loading' => 'lazy',
                    'decoding' => 'async',
                ]
            );

            if ( 'yes' === ($settings['link_to_post'] ?? '') ) {
                $image_url = wp_get_attachment_url($thumb_id);
                if ( $image_url ) {
                    $img_html = '<a href="' . esc_url($image_url) . '">' . $img_html . '</a>';
                }
            }

            echo $img_html;

            if ( 'yes' === ($settings['show_caption'] ?? '') ) {
                $caption = wp_get_attachment_caption($thumb_id);
                if ( $caption ) {
                    echo '<div class="sefiw-caption">' . esc_html($caption) . '</div>';
                }
            }

            echo '</' . $tag . '>';
        }
    }

    return true;
}

new SEFIW_One_File_Plugin();
