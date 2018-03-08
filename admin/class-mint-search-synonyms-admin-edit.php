<?php

/**
 * Provide a admin item view controller for the plugin
 *
 * This file is used to provide edit synonym record functions of the plugin.
 *
 * @link       www.usmint.gov
 * @since      1.0.0
 *
 * @package    Mint_Search_Synonyms
 * @subpackage Mint_Search_Synonyms/admin/partials
 */

require_once(plugin_dir_path(__FILE__).'/../shared/constants.php');               //Include constants
require_once(plugin_dir_path(__FILE__).'partials/mint-search-synonym-admin-item-edit.php');  //Load field class
require_once(plugin_dir_path(__FILE__).'class-mint-search-synonyms-admin-db.php');   //Include db class


class MintSearchSynonymsAdminEdit {
  
  public $lastId;
  private $db;
  public $item = array();
  public $errorMsg = ""; 
  public $successMsg = "";
      
  /** Class constructor **/
  public function __construct() {
      //instatiate empty result object
      $this->item = (object)array(
        'id' => 0,
        'term' => '',
        'synonyms' => '');
  }
  
  public function loadById($id) {
      global $wpdb;
      $result = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . MintSearchSynonymsConstants::TABLE_SYNONYMS." WHERE ID = '" . $id . "'");

      if (!is_null($result)) {
        $this->item = $result;
        //print_r($this->item);
        
      } else {
        if ($id != 0){
        $this->errorMsg("A Term with an id of '$id' does not exist<br/>" . $wpdb->last_error);
        }
      }
  }
  
  public function save(){
    
    //If our form has been submitted.
    if(isset($_POST['submit'])){
      //Get the values of our form fields.
      $id = isset($_POST['id']) ? $_POST['id'] : null;
      $term = isset($_POST['term']) ? $_POST['term'] : null;
      $synonyms = isset($_POST['synonym']) ? $_POST['synonym'] : null;
      
      /* simple validation */
      
      //Check the name and make sure that it isn't a blank/empty string.
      if(strlen(trim($term)) === 0){
        //Blank string, add error to $errors array.
        $this->errorMsg = "You must enter your term!";
      }
      //Check the name and make sure that it isn't a blank/empty string.
      if(strlen(trim($synonyms)) === 0){
        //Blank string, add error to $errors array.
        $this->errorMsg = "You must enter your term!";
      }
      
      /* save */
      $this->lastId = $id;
      $db = new MintSearchSynonymsAdminDb();
      $resultId =  $db->saveSynonym($id, $term, $synonyms);
      if($resultId != 0){
        //if ok then 
        $this->lastId = $resultId;
        $this->successMsg = $db->successMsg;
      } else{
        //error
        $this->errorMsg = $db->errorMsg;
      }
      return $this->lastId;
    } 
    
  }

}