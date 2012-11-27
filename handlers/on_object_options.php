<?php

  /**
   * Ac Gitolite module on_inline_tabs event handler
   *    
   * @package activeCollab.modules.project_exporter
   * @subpackage handlers
   */
  
  /**
   * Handle on project options event
   *
   * @param ApplicationObject $object
   * @param User $user
   * @param NamedList $options
   * @param string $interface
   */
  function ac_gitolite_handle_on_object_options(&$object, &$user, &$options, $interface) {
   if($object instanceof ProjectSourceRepository) {
       
   } // ac_gitolite_handle_on_object_options
 } 
 