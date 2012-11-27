<?php

  /**
   * Ac Gitolite module on_daily event handler
   *    
   * @package activeCollab.modules.ac_gitolite
   * @subpackage handlers
   */
  
 
/**
 * Hourly update remote repositories
 */
function ac_gitolite_handle_on_daily()
{
    // pull repos with update type frequently.
    $admin_obj = new GitoliteAdmin();
    $admin_obj->call_events("3");
    
    source_handle_on_daily();
}
?>
