<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also shared all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.usmint.gov
 * @since             1.0.0
 * @package           Mint_Search_Synonyms
 *
 * @wordpress-plugin
 * Plugin Name:       US Mint Search Synonyms
 * Plugin URI:        www.usmint.gov
 * Description:       This plugin allows us to add synonyms to wordpress search
 * Version:           1.0.0
 * Author:            Leidos for US Mint
 * Author URI:        www.usmint.gov
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mint-search-synonyms
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
  die;
}

/* DEFS */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/* Globals */
global $wpdb;
global $mintSearchSynonymsDbVersion;
$mint_search_synonyms_db_version = '1.0';
global $tableName; 
$tableName = $wpdb->prefix . "mint_search_synonyms"; 

/* requires */
require_once plugin_dir_path( __FILE__ ) . 'shared/mint-search-synonyms-plugin-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function runMintSearchSynonyms() {

  $plugin = new MintSearchSynonymsPluginManager();
  
  /************
  * Add admin hooks and actions
  ************/

  register_activation_hook(__FILE__,    'MintSearchSynonymsPluginManager::activate');
  register_deactivation_hook(__FILE__,    'MintSearchSynonymsPluginManager::deactivate');
  
  /* menu */
  add_action( 'admin_menu', array($plugin, 'pluginMenu'));
  
  /* screen options for list page*/
  add_filter( 'set-screen-option',  array($plugin,'setScreen'), 10, 3 );

  /* search hooks */
  add_filter('posts_where', array($plugin,'mintSearchWhere' ), 10, 2);
  add_filter('posts_groupby', array($plugin,'mintSearchGroupBy' ));


  /* allow redirection, even if my theme starts to send output to the browser */
  //add_action('init', 'MintSearchSynonymsPluginManager::doOutputBuffer');

  /************
  * Add public action and shortcode
  ************/
  //none.

}
runMintSearchSynonyms();






