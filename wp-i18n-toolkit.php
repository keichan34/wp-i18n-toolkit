<?php
/*
 * Plugin Name: i18n Toolkit for WordPress
 * Plugin URI: https://github.com/keichan34/wp-i18n-toolkit
 * Description: A toolkit to help identifing, fixing, and debugging translatable sections of themes and plugins
 * Version: 0.1
 * Author: Keitaroh Kobayashi
 * Author URI: http://keita.flagship.cc/
 */

class I18nToolkitWp {
  private $string_ids = array();
  private $PLUGIN_URI = '';

  function __construct() {
    $this->PLUGIN_URI = plugins_url('/', __FILE__);

    if (!is_admin()) {
      add_filter('gettext', array($this, 'gettext'), 10, 3);
      // add_filter('gettext_with_context', array($this, 'gettext'), 10, 2);
      add_filter('ngettext', array($this, 'ngettext'), 10, 5);
      // add_filter('ngettext_with_context', array($this, 'ngettext'), 10, 4);
    }

    // Register our scripts and styles
    add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts') );
    // add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
    // add_action( 'login_enqueue_scripts', array($this, 'enqueue_scripts') );

    // Output the string ID data that we'll need in the browser.
    add_action( 'wp_after_admin_bar_render', array($this, 'output_string_data'), -1 );
    // add_action( 'admin_footer', array($this, 'output_string_data'), -1 );
  }

  function gettext($translated, $original, $domain = '') {
    $string_id = md5($translated . '-' . $domain);

    $this->string_ids[$string_id] = array(
      'domain' => $domain,
      'translated' => $translated,
      'is_plural' => false,
      'original' => $original
    );
    return $this->process_string($translated, $string_id);
  }

  function ngettext($translated, $single, $plural, $number, $domain = '') {
    $string_id = md5($translated . '-' . $domain);

    $this->string_ids[$string_id] = array(
      'domain' => $domain,
      'translated' => $translated,
      'is_plural' => true,
      'single' => $single,
      'plural' => $plural,
      'number' => $number
    );
    return $this->process_string($translated, $string_id);
  }

  function process_string($string, $string_id) {
    return '&zwnj;' . $string_id . '&zwnj;' . $string . '&zwnj;&zwnj;';
  }

  /**
   * Enqueue our scripts.
   */
  function enqueue_scripts() {
    $handlebars_runtime_src = (defined('WP_DEBUG') && WP_DEBUG) ? ($this->PLUGIN_URI . 'js/src/handlebars.runtime.js') : ($this->PLUGIN_URI . 'js/handlebars.min.js');
    wp_register_script('handlebars-runtime', $handlebars_runtime_src, array(), '1.1.2');

    $toolkit_popover_src = (defined('WP_DEBUG') && WP_DEBUG) ? ($this->PLUGIN_URI . 'js/src/i18n-tk-popover.js') : ($this->PLUGIN_URI . 'js/i18n-tk-popover.min.js');
    wp_register_script('wp-i18n-toolkit-popover', $toolkit_popover_src, array('handlebars-runtime'), '0.0.1');

    $js_src = (defined('WP_DEBUG') && WP_DEBUG) ? ($this->PLUGIN_URI . 'js/src/wp-i18n-toolkit.js') : ($this->PLUGIN_URI . 'js/wp-i18n-toolkit.min.js');
    wp_enqueue_script('wp-i18n-toolkit', $js_src, array('jquery', 'wp-i18n-toolkit-popover'), '0.0.1', true);

    $css_src = (defined('WP_DEBUG') && WP_DEBUG) ? ($this->PLUGIN_URI . 'css/wp-i18n-toolkit.css') : ($this->PLUGIN_URI . 'css/wp-i18n-toolkit.min.css');
    wp_enqueue_style('wp-i18n-toolkit', $css_src, array(), '0.0.1');
  }

  /**
   * Pass data to the script about the strings.
   */
  function output_string_data() {
    echo '<script>';
    echo 'document.WpI18nToolkit=document.WpI18nToolkit||{};jQuery.extend(document.WpI18nToolkit,' . json_encode($this->string_ids) . ');';
    echo '</script>';
  }
}

new I18nToolkitWp();
