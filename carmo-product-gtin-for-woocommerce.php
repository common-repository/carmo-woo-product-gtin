<?php
/*
Plugin Name: Carmo Product GTIN for WooCommerce
Plugin URI: https://www.carmo.pt/project/woo-product-gtin/
Description: Adds a numeric GTIN field on Simple Products and Variation if they exist. This field can be used via shortcode on product pages and for product feeds.
Version: 1.0.0
Author: carmo
Author URI: https://carmo.pt
License: GPL2
*/

if (!defined('ABSPATH')) {
  exit;
}

// Enqueue necessary WooCommerce scripts and styles
function carmo_woo_product_gtin_enqueue_scripts() {
  if (is_product()) {
    wp_enqueue_script('wc-admin-meta-boxes');
    wp_enqueue_style('woocommerce_admin_styles');
  }
}
add_action('wp_enqueue_scripts', 'carmo_woo_product_gtin_enqueue_scripts');

function carmo_woo_product_gtin_load_textdomain() {
  load_plugin_textdomain('carmo-product-gtin-for-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'carmo_woo_product_gtin_load_textdomain');

//check if woocommerce is active
function carmo_woo_product_gtin_check_woocommerce_plugin() {
  if (!is_plugin_active('woocommerce/woocommerce.php')) {
    $install_url = esc_url(admin_url('/plugin-install.php?s=woocommerce&tab=search&type=term'));
    $notice = sprintf(
      __('Carmo Woo Product GTIN is active but WooCommerce is not. Please <a href="%s">install and activate WooCommerce by Automattic</a> plugin to use this feature.', 'textdomain'),
      $install_url
    );
    echo '<div class="notice notice-error"><p>' . wp_kses_post($notice) . '</p></div>';
  }
}
add_action('admin_notices', 'carmo_woo_product_gtin_check_woocommerce_plugin');

// Add field to simple product inventory tab
function carmo_woo_product_gtin_add_field_simple_product() {
  global $woocommerce, $post;
  echo '<div class="options_group">';
  woocommerce_wp_text_input(
    array(
      'id' => 'carmogtin',
      'label' => 'GTIN',
      'placeholder' => __('Enter gtin (ean/upc) value', 'carmo-product-gtin-for-woocommerce'),
      'desc_tip' => true,
      'description' => __('The GTIN, including EAN (European Article Number) and UPC (Universal Product Code), is a standardized numerical identifier and barcode used to uniquely identify products in the global marketplace, facilitating efficient inventory management and supply chain operations.', 'carmo-product-gtin-for-woocommerce'),
    )
  );
  echo '</div>';
}
add_action('woocommerce_product_options_sku', 'carmo_woo_product_gtin_add_field_simple_product');

// Save the field value for simple products
function carmo_woo_product_ean_save_field_simple_product($product_id) {
  $carmogtin = isset($_POST['carmogtin']) ? sanitize_text_field($_POST['carmogtin']) : '';
  update_post_meta($product_id, 'carmogtin', $carmogtin);
}
add_action('woocommerce_process_product_meta', 'carmo_woo_product_ean_save_field_simple_product');

// Add field to variation options
function carmo_woo_product_gtin_add_field_variation($loop, $variation_data, $variation) {
  woocommerce_wp_text_input(
    array(
      'id' => 'carmogtin[' . $variation->ID . ']',
      'class' => 'short',
      'label' => 'GTIN',
      'placeholder' => __('Enter gtin (ean/upc) value', 'carmo-product-gtin-for-woocommerce'),
      'value' => get_post_meta($variation->ID, 'carmogtin', true),
      'desc_tip' => true,
      'description' => __('The GTIN, including EAN (European Article Number) and UPC (Universal Product Code), is a standardized numerical identifier and barcode used to uniquely identify products in the global marketplace, facilitating efficient inventory management and supply chain operations.', 'carmo-product-gtin-for-woocommerce'),
    )
  );
}
add_action('woocommerce_variation_options', 'carmo_woo_product_gtin_add_field_variation', 10, 3);

// Save the field value for variations
function carmo_woo_product_gtin_save_field_variation($variation_id, $i) {
  $carmogtin = isset($_POST['carmogtin'][$variation_id]) ? sanitize_text_field($_POST['carmogtin'][$variation_id]) : '';
  update_post_meta($variation_id, 'carmogtin', $carmogtin);
}
add_action('woocommerce_save_product_variation', 'carmo_woo_product_gtin_save_field_variation', 10, 2);

// Shortcode to display the carmogtin field value
function carmo_woo_product_gtin_shortcode($atts) {
  $atts = shortcode_atts(
    array(
      'product_id' => null,
    ),
    $atts,
    'carmogtin'
  );

  if (isset($atts['product_id']) && !empty($atts['product_id'])) {
    // Retrieve the carmogtin field value for the specified product ID
    $carmogtin = get_post_meta($atts['product_id'], 'carmogtin', true);

    // Check if the carmogtin field exists and has a value
    if ($carmogtin) {
      // Return the carmogtin value
      return $carmogtin;
    }
  } else {
    // Get the current product ID if no specific ID is provided
    $product_id = get_the_ID();

    // Check if the current product has the carmogtin field
    if (metadata_exists('post', $product_id, 'carmogtin')) {
      // Retrieve the carmogtin field value
      $carmogtin = get_post_meta($product_id, 'carmogtin', true);

      // Check if the carmogtin field has a value
      if ($carmogtin) {
        // Return the carmogtin value
        return $carmogtin;
      }
    }
  }

  // Return an empty string if the carmogtin field does not exist or has no value
  return '';
}
add_shortcode('carmogtin', 'carmo_woo_product_gtin_shortcode');

function carmo_gtin_delete_data() {
?> <script>
    console.log('testeteste')
  </script> <?php
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = 'carmogtin'");
          }

          add_action('wp_ajax_delete_all_carmo_gtin', 'carmo_gtin_delete_data');

          function carmo_gtin_render_settings_page() {
            if (is_plugin_active('woocommerce/woocommerce.php')) {
              global $wpdb;
              $query = "SELECT count(*) FROM {$wpdb->postmeta} WHERE post_id in (SELECT id FROM `{$wpdb->posts}` WHERE post_type in ('product', 'product_variation')) and meta_key = 'carmogtin' and meta_value <> '';";
              $count = $wpdb->get_var($query);
            } else {
            ?>
    <div class="notice notice-info">
      <p>
        <?php esc_attr_e('WooCommerce must be installed and active to see how many products have GTIN', 'carmo-product-gtin-for-woocommerce'); ?>
      </p>
    </div>
  <?php
            }

  ?>
  <div class="wrap">
    <h1>Carmo GTIN Settings</h1>
  </div>

  <div class="wrap">
    <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-2">
        <!-- main content -->
        <div id="post-body-content">
          <div class="meta-box-sortables ui-sortable">
            <div class="postbox">
              <h2><span><?php esc_attr_e('Statistics', 'carmo-product-gtin-for-woocommerce'); ?></span></h2>
              <div class="inside">
                <p>
                  <?php printf(__('You have used the GTIN field: %s time(s).', 'carmo-product-gtin-for-woocommerce'), $count); ?>
                </p>
              </div>
              <h2><span><?php esc_attr_e('Options', 'carmo-product-gtin-for-woocommerce'); ?></span></h2>
              <div class="inside">
                <p><?php esc_attr_e('Warning: This will delete all product data associated with Carmo GTIN. Are you sure you want to proceed?', 'carmo-product-gtin-for-woocommerce') ?></p>
                <p><?php esc_attr_e('If you delete the data don\'t forget to flush your object cache if present.', 'carmo-product-gtin-for-woocommerce') ?></p>
                <a class="button-secondary thickbox" href="#TB_inline?&width=400&height=240&inlineId=my-content-id" title="<?php echo _e('Delete all carmo GTIN data', 'carmo-product-gtin-for-woocommerce'); ?>"><?php esc_attr_e('Delete all carmo GTIN data', 'carmo-product-gtin-for-woocommerce'); ?></a>
              </div>
            </div>
          </div>
        </div>
        <!-- sidebar -->
        <div id="postbox-container-1" class="postbox-container">
          <div class="meta-box-sortables">
            <div class="postbox">
              <h2><span><?php esc_attr_e('About', 'carmo-product-gtin-for-woocommerce'); ?></span></h2>
              <div class="inside">
                <p><?php $transl = __(
                          "This plugin was created with love. If you have any question or suggestion, please contact using <a href='mailto:%s'>%s</a>.",
                          'carmo-product-gtin-for-woocommerce'
                      );
                      $email = 'mail@carmo.pt';
                      echo wp_kses_post(sprintf($transl, $email, $email));
                      ?></p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <br class="clear">
    </div>
  </div>

  <?php add_thickbox(); ?>

  <script type="text/javascript">
    jQuery.noConflict();

    function deleteAllCarmoGtin() {
      jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'delete_all_carmo_gtin'
        },
        success: function(response) {
                  alert('<?php echo esc_attr_e("All data has been deleted.", "carmo-product-gtin-for-woocommerce"); ?>');
                  location.reload();
                },
        error: function(xhr, status, error) {
          console.log(error);
          alert('<?php echo esc_attr_e("An error occurred while trying to delete data.", "carmo-product-gtin-for-woocommerce"); ?>');
        }
      });
    }
  </script>

  <div id="my-content-id" style="display:none;">
    <p><?php esc_attr_e('Are you sure you want to delete all product data associated with Carmo GTIN?', 'carmo-product-gtin-for-woocommerce'); ?></p>
    <p><?php esc_attr_e('This action cannot be undone.', 'carmo-product-gtin-for-woocommerce'); ?></p>
    <p><?php esc_attr_e('It will delete all meta_key = \'carmogtin\' from wp_postmeta table.', 'carmo-product-gtin-for-woocommerce'); ?></p>
    <p><button class="button button-primary" onclick="deleteAllCarmoGtin()"><?php esc_attr_e('Yes I\'m sure', 'carmo-product-gtin-for-woocommerce'); ?></button></p>
  </div>

<?php
  }

  function carmo_gtin_add_settings_page() {
    add_options_page('Carmo GTIN Settings', 'Carmo GTIN', 'manage_options', 'carmo-gtin', 'carmo_gtin_render_settings_page');
  }
  add_action('admin_menu', 'carmo_gtin_add_settings_page');

  function carmo_gtin_add_settings_link($links) {
    // Add settings link to plugin entry in admin dashboard plugins list
    $settings_link = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=carmo-gtin')) . '">' . __('Settings', 'carmo-gtin') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
  }
  add_filter('plugin_action_links_carmo-product-gtin-for-woocommerce/carmo-product-gtin-for-woocommerce.php', 'carmo_gtin_add_settings_link');
