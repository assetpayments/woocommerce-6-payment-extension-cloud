<?php
/*
Plugin Name: Payment Gateway for AssetPayments for Woocommerce
Description: Plugin for paying for products through the AssetPayments service. Works in conjunction with the Woocommerce plugin
Version: 2.0
Requires at least: 5.7.2
Requires PHP: 7.x
Author: AssetPayments
License: GPL v2 or later
Text Domain: wc-assetpayments
*/

if (!defined('ABSPATH')) exit;

add_action('plugins_loaded', 'assetpayments_payment_gateway_init', 0);

function assetpayments_payment_gateway_init() {

    /** dir path plugin */

    define("WC_ASSETPAYMENTS_DIR", plugin_dir_url( __FILE__ ));

    if (!class_exists('WC_Payment_Gateway')) return;

    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), function($links ){
        array_unshift( $links, '<a href="admin.php?page=wc-settings&tab=checkout&section=assetpayments">' . __( 'Settings', 'wc-assetpayments' ) . '</a>' );
        return $links;
    });

    add_action( 'admin_enqueue_scripts','kmnd_assetpayments_admin_enqueue_scripts');

    function kmnd_assetpayments_admin_enqueue_scripts(){
        wp_register_style('kmnd-assetpayments-style', plugins_url( '/assets/css/styles.css', __FILE__ ), false);
        wp_enqueue_style( 'kmnd-assetpayments-style');

        wp_register_script("kmnd-assetpayments-js", plugins_url( '/assets/js/main.js', __FILE__ ), '', '1.3',  true);
        wp_enqueue_script( "kmnd-assetpayments-js");
    }

    load_plugin_textdomain( 'wc-assetpayments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    add_action( 'muplugins_loaded', 'mu_kmnd_assetpayments_init' );

    function mu_kmnd_assetpayments_init() {

        load_muplugin_textdomain( 'wc-assetpayments', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

    }

    require_once plugin_dir_path(__FILE__) . 'includes/WC_Gateway_kmnd_Assetpayments.php';
    require_once plugin_dir_path(__FILE__) . 'includes/class-wc-assetpayments-page-redirect.php';

      /** redirect to error page assetpayments */
      $redirect_error = new Wc_Assetpayments_Page_Redirect();

    add_action('template_redirect', function() use ($redirect_error){

          $redirect_error->redirect_to_error();
    });

    function kmnd_assetpayments($methods) {

        $methods[] = 'WC_Gateway_kmnd_Assetpayments';

        return $methods;

    }

    add_filter('woocommerce_payment_gateways', 'kmnd_assetpayments');

}
