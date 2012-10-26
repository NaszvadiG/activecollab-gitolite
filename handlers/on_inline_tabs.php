<?php

  /**
   * Ac Gitolite module on_inline_tabs event handler
   *
   * @package activeCollab.modules.ac_gitolite
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
   
    if ($object instanceof User) {
        
        if ($object->getId() == $logged_user->getId() || $logged_user->isAdministrator() || $logged_user->isPeopleManager()) {   
            $tabs->add('view_keys', array(
             'title' => lang('Public Keys'),
            'url' => Router::assemble('get_public_keys', array('company_id' => $object->getCompanyId(),'user_id' => $object->getId())), 
            ));        
        }
    } // if User
    
  } // ac_gitolite_handle_on_inline_tabs