<?php

  /**
   * Ac Gitolite module on_hourly event handler
   *    
   * @package activeCollab.modules.ac_gitolite
   * @subpackage handlers
   */
  
 
/**
 * Hourly update remote repositories
 */
function ac_gitolite_handle_on_hourly()
{
    // pull repos with update type frequently.
    $admin_obj = new GitoliteAdmin();
    $admin_obj->call_events("2");
    source_handle_on_hourly();
}
?>
