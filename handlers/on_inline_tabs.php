<?php

  /**
   * System module on_inline_tabs event handler
   *
   * @package activeCollab.modules.system
   * @subpackage handlers
   */
  
  /**
   * Handle on inline tabs event
   *
   * @param NamedList $tabs
   * @param ApplicationObject $object
   * @param User $logged_user
   * @param string $interface
   * @return null
   */
  function ac_gitolite_handle_on_inline_tabs(&$tabs, &$object, &$logged_user, $interface) {
    // populate user inline tabs
    if ($object instanceof User) {
        
        $tabs->add('view_keys', array(
          'title' => lang('Public Keys'),
           'url' => Router::assemble('get_public_keys', array('company_id' => $object->getCompanyId(),'user_id' => $object->getId())), 
                
        ));        
      
      if ($object->canViewActivities($logged_user)) {
        $tabs->add('user_recent_activities', array(
          'title' => lang('Recent Activities'),
          'url' => $object->getRecentActivitiesUrl()
        ));
      } // if
    } // if User
    
    
    // populate milestone inline tabs
    
  } // system_handle_on_inline_tabs