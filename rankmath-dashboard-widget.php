<?php
/*
 * Plugin Name:       RankMath Dashboard Widget
 * Plugin URI:        https://github.com/itzmekhokan
 * Description:       A simple widget to display Graph data for Rank Math. 
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Khokan Sardar
 * Author URI:        https://itzmekhokan.github.io
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rankmath-dashboard-widget
 * Domain Path:       /languages
 */

 // Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define RMDW_PLUGIN_FILE.
if ( ! defined( 'RMDW_PLUGIN_FILE' ) ) {
	define( 'RMDW_PLUGIN_FILE', __FILE__ );
}

// Initialize the main RankMath_Dashboard_Widget class.
if ( ! class_exists( 'RankMath_Dashboard_Widget' ) ) {

    /**
	 * Main RankMath_Dashboard_Widget Class.
	 *
	 * @class RankMath_Dashboard_Widget
	 */
	final class RankMath_Dashboard_Widget {

		/**
		 * The single instance of the class.
		 */
		protected static $_instance = null;

		/**
		 * Main RankMath_Dashboard_Widget Instance.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * RankMath_Dashboard_Widget Constructor.
		 */
		public function __construct() {
			// Set up localisation.
			$this->load_plugin_textdomain();
			add_action( 'wp_dashboard_setup', array( $this, 'rm_add_wp_dashboard_widget' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'rm_admin_enqueue_scripts' ) );
            add_action( 'rest_api_init',  array( $this, 'rm_register_rest_api' ) );
		}

        /**
         * Add a widget to the dashboard.
         *
         * @return void
         */
        public function rm_add_wp_dashboard_widget() {
            wp_add_dashboard_widget(
                'rm_graph_dashboard_widget',
                esc_html__( 'RM Widget', 'rankmath-dashboard-widget' ),
                array( $this, 'rm_graph_dashboard_widget_render' ),
            );
        }

        /**
         * Output of Graph widget content.
         *
         * @return void
         */
        public function rm_graph_dashboard_widget_render() {
            echo wp_kses_post( '<div id="rm-graph-widget"></div>' ); // This is the wrapper to render via React.
        }

        /**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', RMDW_PLUGIN_FILE ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( RMDW_PLUGIN_FILE ) );
		}

        /**
         * Load scripts and style in admin end.
         *
         * @return void
         */
        public function rm_admin_enqueue_scripts() {
            $current_screen = get_current_screen();

            if ( 'dashboard' !== $current_screen->id ) { // Load only for dashboard screen.
                return;
            }

            $script_dependencies = array(
				'dependencies' => null,
				'version'      => null,
			);
			
			if ( file_exists( $this->plugin_path() . '/build/index.asset.php' ) ) {
				$script_dependencies = include $this->plugin_path() . '/build/index.asset.php';
			}

            $index_js  = '/build/index.js';
            $style_css = '/build/style-index.css';

            wp_enqueue_script(
                'rm-dashboard-widget-script',
                $this->plugin_url() . $index_js,
                $script_dependencies['dependencies'],
                $script_dependencies['version'],
                true
            );
            wp_enqueue_style( 'rm-dashboard-widget-style', $this->plugin_url() . $style_css, array(), '1.0.0' );
        }

        /**
         * Register rest API for graph data
         *
         * @return void
         */
        public function rm_register_rest_api() {
            register_rest_route( 'rmdash/v1', '/graphdata', [
                'method'              => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'rm_rest_graphdata_callback' ),
                'permission_callback' => '__return_true',
                'args'                => [
                    'days' => [
                        'required' => false,
                        'type'     => 'number',
                    ]
                ]
            ] );
        }

        /**
         * Get graph data API callback response
         * 
         * @param object $request Rest Request.
         *
         * @return array $response
         */
        public function rm_rest_graphdata_callback( $request ) {
            $days = $request->get_param( 'days' );
            if ( ! empty( $days ) ) {
                $response = $this->rm_get_graphdata( $days );
            } else {
                $response = $this->rm_get_graphdata( '7' );
            }
            return rest_ensure_response( $response );
        }

        /**
		 * Load Localisation files.
		 */
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'rankmath-dashboard-widget' );

			unload_textdomain( 'rankmath-dashboard-widget' );
			load_textdomain( 'rankmath-dashboard-widget', WP_LANG_DIR . '/rankmath-dashboard-widget/rankmath-dashboard-widget-' . $locale . '.mo' );
			load_plugin_textdomain( 'rankmath-dashboard-widget', false, plugin_basename( dirname ( RMDW_PLUGIN_FILE ) ) . '/languages' );
		}

        /**
         * Get graph data set. Currently dealing with static data.
         *
         * @param string $days Duration data.
         * @return array 
         */
        public function rm_get_graphdata( $days = '7' ) {
            $graph_data = array(
                '7'  => array(
                    array (
                        'name' => 'Page A',
                        'uv'   => 4000,
                        'pv'   => 2400,
                        'amt'  => 2400,
                    ),
                    array (
                        'name' => 'Page B',
                        'uv'   => 3000,
                        'pv'   => 1398,
                        'amt'  => 2210,
                    ),
                    array (
                        'name' => 'Page C',
                        'uv'   => 2000,
                        'pv'   => 9800,
                        'amt'  => 2290,
                    ),
                    array (
                        'name' => 'Page D',
                        'uv'   => 2780,
                        'pv'   => 3908,
                        'amt'  => 2000,
                    ),
                    array (
                        'name' => 'Page E',
                        'uv'   => 1890,
                        'pv'   => 4800,
                        'amt'  => 2181,
                    ),
                    array (
                        'name' => 'Page F',
                        'uv'   => 2390,
                        'pv'   => 3800,
                        'amt'  => 2500,
                    ),
                    array (
                        'name' => 'Page G',
                        'uv'   => 3490,
                        'pv'   => 4300,
                        'amt'  => 2100,
                    ),
                ),
                '15' => array(
                    array (
                        'name' => 'Page A',
                        'uv'   => 2000,
                        'pv'   => 4200,
                        'amt'  => 2400,
                    ),
                    array (
                        'name' => 'Page B',
                        'uv'   => 3600,
                        'pv'   => 3198,
                        'amt'  => 2210,
                    ),
                    array (
                        'name' => 'Page C',
                        'uv'   => 2000,
                        'pv'   => 7200,
                        'amt'  => 2290,
                    ),
                    array (
                        'name' => 'Page D',
                        'uv'   => 3280,
                        'pv'   => 3208,
                        'amt'  => 2000,
                    ),
                    array (
                        'name' => 'Page E',
                        'uv'   => 7290,
                        'pv'   => 4800,
                        'amt'  => 2781,
                    ),
                    array (
                        'name' => 'Page F',
                        'uv'   => 3290,
                        'pv'   => 3600,
                        'amt'  => 2500,
                    ),
                    array (
                        'name' => 'Page G',
                        'uv'   => 4390,
                        'pv'   => 3200,
                        'amt'  => 2100,
                    ),
                ),
                '30' => array(
                    array (
                        'name' => 'Page A',
                        'uv'   => 6000,
                        'pv'   => 4400,
                        'amt'  => 2300,
                    ),
                    array (
                        'name' => 'Page B',
                        'uv'   => 3500,
                        'pv'   => 3198,
                        'amt'  => 2410,
                    ),
                    array (
                        'name' => 'Page C',
                        'uv'   => 3000,
                        'pv'   => 7800,
                        'amt'  => 1890,
                    ),
                    array (
                        'name' => 'Page D',
                        'uv'   => 2780,
                        'pv'   => 3908,
                        'amt'  => 2400,
                    ),
                    array (
                        'name' => 'Page E',
                        'uv'   => 2190,
                        'pv'   => 4800,
                        'amt'  => 2281,
                    ),
                    array (
                        'name' => 'Page F',
                        'uv'   => 3290,
                        'pv'   => 3800,
                        'amt'  => 2700,
                    ),
                    array (
                        'name' => 'Page G',
                        'uv'   => 4390,
                        'pv'   => 3400,
                        'amt'  => 1200,
                    ),
                ),
            );

            return $graph_data[ $days ];
        }

    }

	RankMath_Dashboard_Widget::instance();
}
