<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       www.usmint.gov
 * @since      1.0.0
 *
 * @package    Mint_Search_Synonyms
 * @subpackage Mint_Search_Synonyms/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
    <div class="wrap">
      <h2>Mint Search Synonyms</h2>
      <div id="synonym-actions" style="margin-top:5px;" ><a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=mint-search-synonyms-admin-edit&action=edit&id=0');?>">Add New</a></div>
	  <div id="synonym-intro"> <p>Multiple synonyms can be associated to a term by seperating them with commas</p> </div>
	  <div id="synonym-body" class="metabox-holder columns-2">
          <div id="synonym-body-content">
            <div class="meta-box-sortables ui-sortable">
              <form method="post">
                <?php
                $this->mintSearchSynonymsObj->prepare_items();
                $this->mintSearchSynonymsObj->display(); ?>
              </form>
            </div>
          </div>
        </div>
        <br class="clear">
      </div>
    </div>
<?php