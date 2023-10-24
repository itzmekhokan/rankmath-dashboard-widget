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

add_action( 'wp_dashboard_setup', 'rm_add_wp_dashboard_widget' );
add_action( 'admin_enqueue_scripts', 'rm_admin_enqueue_scripts' );
add_action( 'rest_api_init', 'rm_register_rest_api' );

/**
 * Add a widget to the dashboard.
 *
 * @return void
 */
function rm_add_wp_dashboard_widget() {
    wp_add_dashboard_widget(
        'rm_graph_dashboard_widget',
        esc_html__( 'RM Widget', 'rankmath-dashboard-widget' ),
        'rm_graph_dashboard_widget_render'
    );
}

/**
 * Output of Graph widget content.
 *
 * @return void
 */
function rm_graph_dashboard_widget_render() {
    echo '<div id="rm-graph-widget"></div>'; // This is the wrapper to render via React.
}

/**
 * Load scripts and style in admin end.
 *
 * @return void
 */
function rm_admin_enqueue_scripts() {
    $current_screen = get_current_screen();

    if ( 'dashboard' !== $current_screen->id ) { // Load only for dashboard screen.
        return;
    }

    $dir               = __DIR__;
    $script_asset_path = "$dir/build/index.asset.php";

    if ( ! file_exists( $script_asset_path ) ) {
        throw new Error( 'Please run npm start or npm build to generate assets file.' );
    }

    $index_js     = 'build/index.js';
    $style_css    = 'build/style-index.css';
    $script_asset = require $script_asset_path;

    wp_enqueue_script(
        'rm-dashboard-widget-script',
        plugins_url( $index_js, __FILE__ ),
        $script_asset['dependencies'],
        $script_asset['version'],
        true
    );
    wp_enqueue_style( 'rm-dashboard-widget-style', plugins_url( $style_css, __FILE__ ), array(), '1.0.0' );
}

/**
 * Register rest API for graph data
 *
 * @return void
 */
function rm_register_rest_api() {
    register_rest_route( 'rmdash/v1', '/graphdata', [
        'method'   => WP_REST_Server::READABLE,
        'callback' => 'rm_rest_graphdata_callback',
        'args'     => [
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
 * @return array $response
 */
function rm_rest_graphdata_callback( $request ) {
    $days = $request->get_param( 'days' );
    if ( ! empty( $days ) ) {
        $response = rm_get_graphdata( $days );
    } else {
        $response = rm_get_graphdata( '7' );
    }
    return rest_ensure_response( $response );
}

/**
 * Get graph data set. Currently dealing with static data.
 *
 * @param string $days
 * @return void
 */
function rm_get_graphdata( $days = '7' ) {
    $graph_data = array(
        '7'  => array(
            array (
                'name' => 'Page A',
                'uv' => 4000,
                'pv' => 2400,
                'amt' => 2400,
            ),
            array (
                'name' => 'Page B',
                'uv' => 3000,
                'pv' => 1398,
                'amt' => 2210,
            ),
            array (
                'name' => 'Page C',
                'uv' => 2000,
                'pv' => 9800,
                'amt' => 2290,
            ),
            array (
                'name' => 'Page D',
                'uv' => 2780,
                'pv' => 3908,
                'amt' => 2000,
            ),
            array (
                'name' => 'Page E',
                'uv' => 1890,
                'pv' => 4800,
                'amt' => 2181,
            ),
            array (
                'name' => 'Page F',
                'uv' => 2390,
                'pv' => 3800,
                'amt' => 2500,
            ),
            array (
                'name' => 'Page G',
                'uv' => 3490,
                'pv' => 4300,
                'amt' => 2100,
            ),
        ),
        '15' => array(
            array (
                'name' => 'Page A',
                'uv' => 2000,
                'pv' => 4200,
                'amt' => 2400,
            ),
            array (
                'name' => 'Page B',
                'uv' => 3600,
                'pv' => 3198,
                'amt' => 2210,
            ),
            array (
                'name' => 'Page C',
                'uv' => 2000,
                'pv' => 7200,
                'amt' => 2290,
            ),
            array (
                'name' => 'Page D',
                'uv' => 3280,
                'pv' => 3208,
                'amt' => 2000,
            ),
            array (
                'name' => 'Page E',
                'uv' => 7290,
                'pv' => 4800,
                'amt' => 2781,
            ),
            array (
                'name' => 'Page F',
                'uv' => 3290,
                'pv' => 3600,
                'amt' => 2500,
            ),
            array (
                'name' => 'Page G',
                'uv' => 4390,
                'pv' => 3200,
                'amt' => 2100,
            ),
        ),
        '30' => array(
            array (
                'name' => 'Page A',
                'uv' => 6000,
                'pv' => 4400,
                'amt' => 2300,
            ),
            array (
                'name' => 'Page B',
                'uv' => 3500,
                'pv' => 3198,
                'amt' => 2410,
            ),
            array (
                'name' => 'Page C',
                'uv' => 3000,
                'pv' => 7800,
                'amt' => 1890,
            ),
            array (
                'name' => 'Page D',
                'uv' => 2780,
                'pv' => 3908,
                'amt' => 2400,
            ),
            array (
                'name' => 'Page E',
                'uv' => 2190,
                'pv' => 4800,
                'amt' => 2281,
            ),
            array (
                'name' => 'Page F',
                'uv' => 3290,
                'pv' => 3800,
                'amt' => 2700,
            ),
            array (
                'name' => 'Page G',
                'uv' => 4390,
                'pv' => 3400,
                'amt' => 1200,
            ),
        ),
    );

    return $graph_data[ $days ];
}
