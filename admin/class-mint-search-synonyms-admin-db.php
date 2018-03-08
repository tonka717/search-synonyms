<?php
/**
 * Provide a admin item view model controller for the plugin
 *
 * This file is used to handle database aspects of the plugin.
 *
 * @link       www.usmint.gov
 * @since      1.0.0
 *
 * @package    Mint_Search_Synonyms
 * @subpackage Mint_Search_Synonyms/admin/
 */

require_once(plugin_dir_path(__FILE__).'/../shared/constants.php');               //Include constants


class MintSearchSynonymsAdminDb {

  public $result;
  public $errorMsg = "";
  public $successMsg = "";
  private $msg;
  private $data;
  private $sucessInsert = "Item Inserted";
  private $sucessUpdate = "Item Updated";
  
  public function __construct() {
    
  }
  
  public function getTermBySynonym($synonym){
  
    global $wpdb;
    
    $result = $wpdb->get_row("SELECT term FROM " . $wpdb->prefix . MintSearchSynonymsConstants::TABLE_SYNONYMS . " WHERE synonyms Like '%" . $synonym . "%' ORDER BY term");
    if ($result){
      return $result->term;
    } else {
      $this->setError($wpdb->last_error);
      return false;
    }
  }
  
  /**
  * Save the data
  **/
  public function saveSynonym($id, $term, $synonyms){

    $data = array(
      'id' => $id,
      'term' => $term,
      'synonyms' => $synonyms
    );

    if($data['id'] == 0){
      return $this->insertSynonym($data);
    } else {
      return $this->updateSynonym($data);
    }
  }
  
  /**
  * Insert the data
  **/
  public function insertSynonym($data){
    
    global $wpdb;
    
    $result = $wpdb->insert($wpdb->prefix . MintSearchSynonymsConstants::TABLE_SYNONYMS, $data);
    if ($result === false){
      $this->setError($wpdb->last_error);
      return 0;
    } else {
      $this->setSuccess($this->sucessInsert);
      $lastid = $wpdb->insert_id;
      return $lastid;
    }
      
  }
  
  /**
  * Update the data
  **/
  public function updateSynonym($data){

    global $wpdb;

    $result = $wpdb->update($wpdb->prefix . MintSearchSynonymsConstants::TABLE_SYNONYMS, $data, array('id'=>$data['id']));

    if ($result === false){
      $this->setError($wpdb->last_error);
      return 0;
    } else {
      $this->setSuccess($this->sucessUpdate);
          $lastid = $data['id'];
      return $lastid;
    }
    
  }
  
  function setError($msg) {
    
    $this->errorMsg = $msg;
    
  }

  public function setSuccess($msg) {
    
    $this->successMsg = $msg;
    
  }


}
?>