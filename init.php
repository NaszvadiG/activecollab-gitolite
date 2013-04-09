<?php

/**
 * Ac Gitolite
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package activeCollab.modules.ac_gitolite
 * @author strik3r <faishal.saiyed@rtcamp.com>
 */
/**
 * @const  AC_GITOLITE_MODULE
 * @const  AC_GITOLITE_MODULE_PATH
 */
define('AC_GITOLITE_MODULE', 'ac_gitolite');  // Define module name
define('AC_GITOLITE_MODULE_PATH', CUSTOM_PATH . '/modules/ac_gitolite');  // Define module path
//
// Autoload the following models
AngieApplication::setForAutoload(array(
    'GitoliteAc' => AC_GITOLITE_MODULE_PATH . '/models/GitoliteAc.class.php',
    'GitoliteAdmin' => AC_GITOLITE_MODULE_PATH . '/models/GitoliteAdmin.class.php',
    'ProjectGitolite' => AC_GITOLITE_MODULE_PATH . '/models/ProjectGitolite.class.php')
);


/**
 * @const  GITOLITE_NOACCESS
 * @const  GITOLITE_READACCESS
 * @const  GITOLITE_MANAGEACCESS
 */
define('GITOLITE_NOACCESS', '1');
define('GITOLITE_READACCESS', '2');
define('GITOLITE_MANAGEACCESS', '3');
?>
