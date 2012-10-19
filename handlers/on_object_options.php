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
       
        // Check whether repository is a gitolite repository
        $repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
        $objects_table_name = TABLE_PREFIX . 'project_objects';
        
        $result = DB::execute("SELECT * FROM $repo_table_name a, $objects_table_name b 
                                where a.`repo_fk` = b.integer_field_1 and b.type = 'ProjectSourceRepository'
                                and b.id = '".$object->getId()."'");
     
  } // ac_gitolite_handle_on_object_options
 } 
 