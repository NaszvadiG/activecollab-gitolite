<?php

  /**
   * on_admin_panel event handler
   * 
   * @package activeCollab.modules.merger
   * @subpackage handlers
   */

  /**
   * Handle on_admin_panel event
   * 
   * @param AdminPanel $admin_panel
   */
  function ac_gitolite_handle_on_admin_panel(AdminPanel &$admin_panel) {
      //$admin_panel->addToOther('clean_notebooks', lang('Cleanup Notebooks'), Router::assemble('clean_notebooks'), AngieApplication::getImageUrl('module.png', PROJECT_MERGER_MODULE));
      $admin_panel->addToProjects("gitolite_admin","Gitolite Admin", Router::assemble('gitolite_admin'), AngieApplication::getImageUrl('module.png', AC_GITOLITE_MODULE), array(
        'onclick' => new FlyoutFormCallback('gitolite_admin_data')
          ));
       /*$admin_panel->addToOther('clean_notebooks', lang('Cleanup Notebooks'), Router::assemble('merger_admin'), AngieApplication::getImageUrl('module.png', CLEANUP_MODULE),
            array('onclick' => new FlyoutFormCallback(array('width' => 400, 'title' => 'Clean Empty Notebooks'))));*/
  } // merger_handle_on_admin_panel