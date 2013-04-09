<?php

/**
 * Ac Gitolite module on_inline_tabs event handler
 * on_admin_panel event handler
 * 
 * @package activeCollab.modules.ac_gitolite
 * @subpackage handlers
 */

/**
 * Handle on_admin_panel event
 * 
 * @param AdminPanel $admin_panel
 */
function ac_gitolite_handle_on_admin_panel(AdminPanel &$admin_panel) {
    $admin_panel->addToProjects("gitolite_admin_handler", lang("Gitolite Settings"), Router::assemble('gitolite_admin'), AngieApplication::getImageUrl('module.png', AC_GITOLITE_MODULE));
}

// ac_gitolite_handle_on_admin_panel