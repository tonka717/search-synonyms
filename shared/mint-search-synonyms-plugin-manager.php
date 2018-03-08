<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that shared attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.usmint.gov
 * @since      1.0.0
 *
 * @package    Mint_Search_Synonyms
 * @subpackage Mint_Search_Synonyms/shared
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Mint_Search_Synonyms
 * @subpackage Mint_Search_Synonyms/shared
 * @author     Leidos for US Mint
 */
class MintSearchSynonymsPluginManager {
  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $pluginName    The string used to uniquely identify this plugin.
   */
  protected $pluginName;

  /**
   * The current version of the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $version    The current version of the plugin.
   */
  protected $version;
  
  /**
  *  mint_search_synonyms WP_List_Table object
  */
  public $mintSearchSynonymsObj;
  
  /**
   * Define the core functionality of the plugin.
   *
   * Set the plugin name and the plugin version that can be used throughout the plugin.
   *
   * @since    1.0.0
   */
  public function __construct() {
    
    if ( defined( 'plugin_name_VERSION' ) ) {
      $this->version = plugin_name_VERSION;
    } else {
      $this->version = '1.0.0';
    }
    $this->pluginName = 'mint-search-synonyms';
    $this->loadDependencies();

  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   *
   * @since    1.0.0
   * @access   private
   */
  private function loadDependencies() {
    
    /**
     * Ensure the support for wp list control
     */
    if(!class_exists('WP_List_Table')){
      require_once( ABSPATH . 'wp-admin/includes/screen.php' );
      require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
    }
    
    require_once(plugin_dir_path(__FILE__) . 'constants.php');               //Include constants
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mint-search-synonyms-admin-db.php';   //Include db class
    require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-mint-search-synonyms-admin-list.php';  //synonym view

  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function getPluginName() {
    return $this->pluginName;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function getVersion() {
    return $this->version;
  }
    
  /**
   * Activator.
   *
   * This function is called on activation and creates the mint_search_synonyms table with data
   *
   * @since    1.0.0
   */
  public static function activate() {

    global $wpdb;
    global $mintSearchSynonymsDbVersion;

    $tableName = $wpdb->prefix . "mint_search_synonyms"; 
    $charsetCollate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $tableName (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          term VARCHAR(255) NOT NULL,
          synonyms VARCHAR(255) NOT NULL,
          PRIMARY KEY(id)
    ) $charsetCollate;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    
    $lastDbVersion = get_option( 'mint_search_synonyms_db_version' );

  }

  /**
   * Deactivator.
   *
   * No actions on deactivation
   *
   * @since    1.0.0
   */
  public static function deactivate() {

  }
  
  
  public static function setScreen( $status, $option, $value ) {

    if ( 'synonyms_per_page' == $option ) {return $value;}
    
  }

  /**
   * Plugin menu settings
   */
  public function pluginMenu() {
        
    $hook = add_menu_page('US Mint Search Synonyms Administration',  'Search', 'manage_options', MintSearchSynonymsConstants::PAGESLUG_SYNONYM_LIST,  array( $this, 'pluginSettingsPage' ));
    add_action( "load-$hook", array( $this, 'screenOption' ) );
    /* sub menus */  
    //add_submenu_page(PAGESLUG_SYNONYM_LIST, 'Edit Term Synonym', 'Edit Synonym', 'manage_options', PAGESLUG_SYNONYM_EDIT, array($this, 'pluginEditPage' ));  //<-- use this one for full submenus 
    add_submenu_page(null, 'Edit Term Synonym', 'Edit Synonym', 'manage_options',MintSearchSynonymsConstants::PAGESLUG_SYNONYM_EDIT, array($this, 'pluginEditPage')); //<-- hidden
    
  }

  /**
   * Plugin settings page
   */
  public function pluginSettingsPage() {
    
    require_once(plugin_dir_path(__DIR__).'admin/partials/mint-search-synonyms-admin-display.php');
    
  }

  /**
   * Plugin edit page
   */
  public function pluginEditPage() {
    
    require_once(plugin_dir_path(__DIR__).'admin/class-mint-search-synonyms-admin-edit.php');
    
  }

  /**
   * Screen options - calls display screen
   */
  public function screenOption() {
    
    $option = 'per_page';
    $args   = array(
      'label'   => 'Synonyms',
      'default' => MintSearchSynonymsConstants::WP_LIST_TABLE_SCREENOPTION_DEFAULT_ROWS,
      'option'  => 'synonyms_per_page'
    );

    add_screen_option( $option, $args );

    $this->mintSearchSynonymsObj = new MintSearchSynonymsAdminList();

  }
  
  /**
  * Mint Search filter tag builder
  */
  private function mintGetSearchSynonymsTags($term)
  {
    $orTerms ="";
    $db = new MintSearchSynonymsAdminDb();
    //$result = $db->get_synonym_by_term($term);
    $result = $db->getTermBySynonym($term);
    //print_r($result);
    if($result !== false){
      //if ok then 
      $values =  explode(",",$result);
      foreach($values as $synonymTerm){
        if($synonymTerm !== ""){
          $orTerms .= " OR (t.name LIKE '%".addslashes($synonymTerm)."%')";
        }
      }
      return $orTerms;
    } else{
      //error
      return "";
    }
  } 
  
  /**
  * Mint Search filter Synonym content builder
  */
  private function mintGetSearchSynonyms($term)
  {
    
    $orTerms ="";
    $db = new MintSearchSynonymsAdminDb();
    //$result = $db->get_synonym_by_term($term);
    $result = $db->getTermBySynonym($term);
    //print_r($result);
    if($result !== false){
      //if ok then 
      $values =  explode(",",$result);
      foreach($values as $synonymTerm){
        if($synonymTerm !== ""){
      $orTerms .= " ( (wp_posts.post_title LIKE '%".addslashes($synonymTerm)."%') OR (wp_posts.post_excerpt LIKE '%".addslashes($synonymTerm)."%') OR (wp_posts.post_content LIKE '%".addslashes($synonymTerm)."%') )";
        }
      }
      return $orTerms;
    } else{
      //error
      return "";
    }
  } 

  /**
  * Mint Search post_where filter handler
  *
  * example output: if query value is 'geld aaaa' with synonyms found  of 'gold' and 'aaa'
  *AND ( 
  *  ( 
  *    (((wp_posts.post_title LIKE '{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}geld{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}') 
  *    OR (wp_posts.post_excerpt LIKE '{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}geld{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}') 
  *    OR (wp_posts.post_content LIKE '{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}geld{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}')) 
  *    AND ((wp_posts.post_title LIKE '{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}aaaa{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}') 
  *    OR (wp_posts.post_excerpt LIKE '{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}aaaa{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}') 
  *    OR (wp_posts.post_content LIKE '{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}aaaa{99d3a07829f3fd28cd3a8c93ee04fb2041c21e7842c15707fafe67eeb518c4dc}'))) 
  *    AND wp_posts.post_type IN ('post', 'page', 'attachment') AND (wp_posts.post_status = 'publish' OR wp_posts.post_author = 1 AND wp_posts.post_status = 'private') 
  *  ) 
  *  OR ( 
  *    ( 
  *      wp_posts.post_type IN ('post', 'page', 'attachment') 
  *      AND (wp_posts.post_status = 'publish') 
  *      AND ( (wp_posts.post_title LIKE '%Gold%') OR (wp_posts.post_excerpt LIKE '%Gold%') OR (wp_posts.post_content LIKE '%Gold%') ) 
  *    ) 
  *    OR ( 
  *      wp_posts.post_type IN ('post', 'page', 'attachment') 
  *      AND (wp_posts.post_status = 'publish') 
  *      AND ( (wp_posts.post_title LIKE '%aaa%') OR (wp_posts.post_excerpt LIKE '%aaa%') OR (wp_posts.post_content LIKE '%aaa%') ) 
  *    ) 
  *  ) 
  *) 
  */
  public function mintSearchWhere($where, \WP_Query $qry)
  { 
  
    global $wpdb;
    //if( ! is_admin() && $qry->is_main_query() && $qry->is_search()) 
    if( ! is_admin() && $qry->is_search ) 
    {
      // get search terms as array from query
      $searchTerms = $qry->query_vars['search_terms'];
      //get the post type which is usually 'any'
      $qry_type = $qry->query_vars['post_type'];
      if(is_array($searchTerms)) 
      {
        $hasSynonyms = false;
        // It's a search request so we will rewrite the default query to wrap it so we can append an OR clause
        $where = " AND ( ( " . substr($where, 4, strlen($where)-4) . " ) ";
        // generate post like conditions
        $i = 0;
        //enumerate each word in search terms 
        foreach ($searchTerms as $searchTerm) 
        {
          $i++;
          // lookup mint synonyms for each term and append "OR LIKE..s" if one is found
          $whereSyn = $this->mintGetSearchSynonyms($searchTerm);
          if ($whereSyn !== "") 
          {
            $hasSynonyms = true;  
            //$where .= ($i === 1) ?  " OR ( " : " OR ";  // prepare synonym outer OR clause wrapper if 1st synonym else inner OR
			$where .= ($i === 1) ?  " OR ( " : " AND ";  // prepare synonym outer OR clause wrapper if 1st synonym else inner AND
            $where .= $this->mintSearchSynonymPostTypeClause( $qry_type );
            $where .= " AND (wp_posts.post_status = 'publish') ";
            $where .= " AND $whereSyn ) ";
          }
        }
	    $where .= ($hasSynonyms == true) ?  " ) ": "";  // close synonym OR
        $where .= " )";  // close primary wrapper AND
      }
    }
	//echo $where;
    return $where;
  }

  /**
  * Mint Search search_groupby filter default handler
  */
  public function mintSearchGroupBy($groupby)
  {
    
    global $wpdb;

    // we need to group on post ID
    $groupbyId = "{$wpdb->posts}.ID";
    if(!is_search() || strpos($groupby, $groupbyId) !== false) return $groupby;

    // groupby was empty, use ours
    if(!strlen(trim($groupby))) return $groupbyId;

    // wasn't empty, append ours
    return $groupby.", ".$groupbyId;
  }
  
  
  /**
  * Mint Search build Post Type Clause
  */
  private function mintSearchSynonymPostTypeClause( $post_type )
  {
    global $wpdb;
    $where = " ( ";

    if ( 'any' == $post_type ) {
      $in_search_post_types = get_post_types( array('exclude_from_search' => false) );
      if ( empty( $in_search_post_types ) ) {
        $where .= ' 1=0 ';
      } else {
        $where .= " {$wpdb->posts}.post_type IN ('" . join( "', '", array_map( 'esc_sql', $in_search_post_types ) ) . "')";
      }
    } elseif ( !empty( $post_type ) && is_array( $post_type ) ) {
      $where .= " {$wpdb->posts}.post_type IN ('" . join("', '", esc_sql( $post_type ) ) . "')";
    } elseif ( ! empty( $post_type ) ) {
      $where .= $wpdb->prepare( " {$wpdb->posts}.post_type = %s", $post_type );
      //$post_type_object = get_post_type_object ( $post_type );
    }
  
    return $where;
  }

}