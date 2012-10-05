<?php

  /**
   * Merger
   *
   * @package activeCollab.modules.ac_gitolite
   */

  define('AC_GITOLITE_MODULE', 'ac_gitolite');
  define('AC_GITOLITE_MODULE_PATH', CUSTOM_PATH . '/modules/ac_gitolite');
  
 //define('AC_GITOLITE_GIT_ADMIN_PATH', '/opt/lampp/htdocs/gitadmin/gitolite-admin/');
  
  //define('AC_GITOLITE_GIT_ADMIN_PATH', '/var/www/gitolite/gitolite-admin/');
  
  //define('AC_GITOLITE_GIT_REPO_PATH', GIT_FILES_PATH.DIRECTORY_SEPARATOR."repositories");
  //define('AC_GITOLITE_GIT_REPO_PATH',"/var/www/gitolite");
  
  //define('GIT_SERVER', "git@192.168.0.137");
  
  AngieApplication::setForAutoload(array(
	'GitoliteAc' => AC_GITOLITE_MODULE_PATH.'/models/GitoliteAc.class.php',
        'GitoliteAdmin' => AC_GITOLITE_MODULE_PATH.'/models/GitoliteAdmin.class.php',
        'ProjectGitolite' => AC_GITOLITE_MODULE_PATH.'/models/ProjectGitolite.class.php')
  );
   
   
  /** Gitolite access leveles */
  define('GITOLITE_NOACCESS', '1');
  define('GITOLITE_READACCESS', '2');
  define('GITOLITE_MANAGEACCESS', '3');
  
  
  
  
  
  
?>
