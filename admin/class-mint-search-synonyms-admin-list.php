<?php
/**
 * Provide a admin item view model controller for the plugin
 *
 * This file is used to handle WP_List_Table extensions of the plugin for the synonym list page
 * Any function with an '_' is and extention override and must maintain the functio name convention
 * of the WP_List_Table class.
 *
 * @link       www.usmint.gov
 * @since      1.0.0
 *
 * @package    Mint_Search_Synonyms
 * @subpackage Mint_Search_Synonyms/admin/
 *
 * 
 */
class MintSearchSynonymsAdminList extends WP_List_Table {
  
  /** Class constructor */
  public function __construct() {
    
    parent::__construct( array(
      'singular' => __( 'mint_search_synonym', 'sp' ), //singular name of the listed records
      'plural'   => __( 'mint_search_synonyms', 'sp' ), //plural name of the listed records
      'ajax'     => false //should this table support ajax?

    ) );

  }
  
  /**
   * Retrieve mint_search_synonyms data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  public static function getMintSearchSynonyms( $per_page = MintSearchSynonymsConstants::WP_LIST_TABLE_SCREENOPTION_DEFAULT_ROWS, $page_number = 1 ) {

    global $wpdb;

    $sql = "SELECT * FROM {$wpdb->prefix}mint_search_synonyms";

    if ( ! empty( $_REQUEST['orderby'] ) ) {
      $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
      $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
    }else{
		$sql .= ' ORDER BY term';
	}
    $sql .= " LIMIT $per_page";
    $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

    $result = $wpdb->get_results( $sql, 'ARRAY_A' );
    return $result;
  }
  
  /**
   * Delete a mint_search_synonyms record.
   *
   * @param int $id mint_search_synonyms ID
   */
  public static function deleteMintSearchSynonym( $id ) {
    global $wpdb;

    $wpdb->delete(
    "{$wpdb->prefix}mint_search_synonyms",
    array( 'ID' => $id ),
    array( '%d' )
    );
   
  }
  
  /**
   * Returns the count of records in the database.
   *
   * @return null|string
   */
  public static function record_count() {
    global $wpdb;

    $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}mint_search_synonyms";

    return $wpdb->get_var( $sql );
  }
  
  /** Text displayed when no synonym data is available */
  public function no_items() {
    _e( 'No Search Synonyms avaliable.', 'sp' );
  }
  
  /**
   * Method for name column must end in column name it overrides (ie term col)
   *
   * @param array $item an array of DB data
   *
   * @return string
   */
  function column_term($item) {
  
    // create a nonce
    $edit_nonce = wp_create_nonce( 'sp_edit_mint_search_synonyms' );
    $delete_nonce = wp_create_nonce( 'sp_delete_mint_search_synonyms' );

    $actions = array(
    'edit' => sprintf( '<a href="?page=%s&action=%s&id=%s&_wpnonce=%s">Edit</a>', MintSearchSynonymsConstants::PAGESLUG_SYNONYM_EDIT, 'edit', absint( $item['id'] ), $edit_nonce )
    );

    //*Return the title contents
    return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            $item['term'], /*$1%s*/ 
            $item['id'], /*$2%s*/ 
           $this->row_actions($actions)  /*$3%s*/ 
        );
    
  }
  
  /**
   * Render a column when no column specific method exists.
   *
   * @param array $item
   * @param string $column_name
   *
   * @return mixed
   */
  public function column_default( $item, $column_name ) {
    switch ( $column_name ) {
    case 'term':
    case 'synonyms':
      return $item[ $column_name ];
    default:
      error_log('column_default - unknown column');
      error_log(print_r( $item, true ));
        return false; 
    }
  }
  
  /**
   * Render the bulk edit checkbox
   *
   * @param array $item
   *
   * @return string
   */
  function column_cb( $item ) {
    return sprintf(
    '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
    );
  }
  
  /**
   *  Associative array of columns
   *
   * @return array
   */
  function get_columns() {
    $columns = array(
    'cb'      => '<input type="checkbox" />',
    'term'    => __( 'Term', 'sp' ),
    'synonyms' => __( 'Synonyms', 'sp' )
    );

    return $columns;
  }
  
  /**
   * Columns to make sortable.
   *
   * @return array
   */
  public function get_sortable_columns() {
    $sortable_columns = array(
    'term' => array( 'term', true ),
    'synonyms' => array( 'synonyms', false )
    );

    return $sortable_columns;
  }
  
  /**
   * Returns an associative array containing the bulk action
   *
   * @return array
   */
  public function get_bulk_actions() {
    $actions = array(
    'bulk-delete' => 'Delete'
    );

    return $actions;
  }
  
  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items() {

    $this->_column_headers = $this->get_column_info();

    /** Process bulk action */
    $this->process_bulk_action();

    $per_page     = $this->get_items_per_page( 'synonyms_per_page', MintSearchSynonymsConstants::WP_LIST_TABLE_SCREENOPTION_DEFAULT_ROWS );
    $current_page = $this->get_pagenum();
    $total_items  = self::record_count();

    $this->set_pagination_args( array(
    'total_items' => $total_items, //WE have to calculate the total number of items
    'per_page'    => $per_page //WE have to determine how many items to show on a page
    ) );


    $this->items = self::getMintSearchSynonyms( $per_page, $current_page );
	
  }
  
  public function process_bulk_action() {

    //Detect when a bulk action is being triggered...
    if ( 'delete' === $this->current_action() ) {

    // In our file that handles the request, verify the nonce.
    $nonce = esc_attr( $_REQUEST['_wpnonce'] );

    if ( ! wp_verify_nonce( $nonce, 'sp_deleteMintSearchSynonyms' ) ) {
      die();
    }
    else {
      self::deleteMintSearchSynonym( absint( $_GET['mint_search_synonyms'] ) );
    }

    }

    // If the delete bulk action is triggered
    if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
       || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
    ) {

    $delete_ids = esc_sql( $_POST['bulk-delete'] );

    // loop over the array of record IDs and delete them
    foreach ( $delete_ids as $id ) {
      self::deleteMintSearchSynonym( $id );
    }

    }
    
  }

}