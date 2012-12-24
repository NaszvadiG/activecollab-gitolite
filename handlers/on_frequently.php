<?php

  /**
   * Ac Gitolite module on_frequently event handler
   *    
   * @package activeCollab.modules.ac_gitolite
   * @subpackage handlers
   */
  
 
/**
 * Frequently update remote repositories
 */
function ac_gitolite_handle_on_frequently()
{
    // pull repos with update type frequently.
    
    $admin_obj = new GitoliteAdmin();
    $admin_obj->call_events("1");
    source_handle_on_frequently();
}
?>
