<?php
/**
 * Provide a admin item edit view for the plugin
 *
 * This file is used to provide the front end edit html for the synonym records of the plugin.
 *
 * @link       www.usmint.gov
 * @since      1.0.0
 *
 * @package    Mint_Search_Synonyms
 * @subpackage Mint_Search_Synonyms/admin/partials
 */

  $synonym = new MintSearchSynonymsAdminEdit();
  $notice="";
  $message="";

  if(isset($_REQUEST['submit'])) {       //form was submitted
    $lastId = $synonym->save();
    if(!empty($lastId) && $lastId != 0 ) {    //successfully validated & saved, reload
      $synonym->loadById($lastId);
      $message = $synonym->successMsg;
    }
  } elseif(isset($_GET['id'])) {          //editing existing event
      $synonym->loadById($_GET['id']);
  }

  if(!empty($synonym->errorMsg)){
    //foreach($synonym->errorMsgs as $value){ 
      //$notice .= $value."< br />";
      $notice = $synonym->errorMsg;
    //}
    wp_die($msgs);
  }
 ?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
    <div class="wrap">
      <h2>Mint Search Synonym Add/Edit</h2>
      <?php if (!empty($notice)): ?>
      <div id="notice" class="error"><p><?php echo $notice ?></p></div>
      <?php endif;?>
      <?php if (!empty($message)): ?>
      <div id="message" class="updated"><p><?php echo $message ?></p></div>
      <?php endif;?>
      <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>
        <?php /* NOTICE: here we're storing id to determine will be item added or updated */ ?>
        <input type="hidden" name="id" value="<?php echo $synonym->item->id ?>"/>

        <div class="metabox-holder" id="poststuff">
          <div id="post-body">
            <div id="post-body-intro"><p>Multiple sysnonyms can be associated to a term by seperating words with commas</p></div>
            <div id="post-body-content">
              <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
                <tbody>
                <tr class="form-field">
                  <th valign="top" scope="row">
                    <label for="term"><?php _e('term', 'sp')?></label>
                  </th>
                  <td>
                    <input id="term" name="term" type="text" style="width: 95%" value="<?php echo esc_attr($synonym->item->term)?>"
                         size="50" class="code" placeholder="<?php _e('Term', 'sp')?>" required>
                  </td>
                </tr>
                <tr class="form-field">
                  <th valign="top" scope="row">
                    <label for="synonym"><?php _e('synonyms', 'sp')?></label>
                  </th>
                  <td>
                    <input id="synonym" name="synonym" type="text" style="width: 95%" value="<?php echo esc_attr($synonym->item->synonyms)?>"
                         size="50" class="code" placeholder="<?php _e('Synonym', 'sp')?>" required>
                  </td>
                </tr>
                </tbody>
              </table>
              <input type="submit" value="<?php _e('Save', 'sp')?>" id="submit" class="button-primary" name="submit">
            </div>
          </div>
        </div>
      </form>
    </div>
<?php