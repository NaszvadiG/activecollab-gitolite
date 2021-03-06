<?php

// We need admin controller
AngieApplication::useController('admin', ENVIRONMENT_FRAMEWORK_INJECT_INTO);

/**
 * Ac Gitolite Admin Controller 
 * @package activeCollab.modules.ac_gitolite
 * @subpackage controllers
 * @author rtCamp Software Solutions Pvt Ltd <admin@rtcamp.com>
 * @author Rahul Bansal <rahul.bansal@rtcamp.com>
 * @author Kasim Badami <kasim.badami@rtcamp.com>
 * @author Mitesh Shah <mitesh.shah@rtcamp.com> 
 * @author strik3r <faishal.saiyed@rtcamp.com>
 */
class AcGitoliteAdminController extends AdminController {

    public static $conf_parsed;
    public static $ac_users;

    /**
     * Prepare controller
     */
    function __before() {
        parent::__before();
        $this->wireframe->breadcrumbs->add('ac_gitolite_admin', lang('Gitolite'), Router::assemble('gitolite_admin'));
    }

    /**
     * 
     * Display gitolite admin page
     * @return void
     */
    function index() {


        $this->wireframe->actions->add('need_help', lang('Need Help?'), Router::assemble('need_help_path'), array(
            'onclick' => new FlyoutFormCallback('repository_created'),
            'icon' => AngieApplication::getPreferedInterface() == AngieApplication::INTERFACE_DEFAULT ? AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE) : AngieApplication::getImageUrl('icons/16X16-git.png', AC_GITOLITE_MODULE, AngieApplication::INTERFACE_PHONE))
        );
        $gitoliteadminpath = GitoliteAdmin :: get_admin_path();

        $settings = GitoliteAdmin :: get_admin_settings();

        
        $gitoliteadminpath = ($settings['gitoliteadminpath'] == "") ? "" : $settings['gitoliteadminpath'] . "gitolite-admin/";
        



        $domain_name = GitoliteAdmin :: get_server_name();
        $server_name = ($settings['gitoliteserveradd'] == "") ? $domain_name : $settings['gitoliteserveradd'];

        $git_server_location = ($settings['git_server_location'] == "") ? "local" : $settings['git_server_location'];
        $git_ssh_port = (!isset($settings['git_ssh_port']) || $settings['git_ssh_port'] == "") ? 22 : $settings['git_ssh_port'];

        //$is_auto = ($settings['initialize_repo'] == "") ? "No" : $settings['initialize_repo'];

        if ($settings['gitoliteuser'] == "") {
            $gitoliteuser = "";
            $is_enable = FALSE;
        } else {
            $gitoliteuser = $settings['gitoliteuser'];
            $is_enable = TRUE;
        }
        $setup_script = GitoliteAdmin :: get_setup_path();
        $empty_repositories = GitoliteAdmin :: get_empty_repositories();
        if (is_array($empty_repositories) && count($empty_repositories) > 0) {
            $i = 0;
            foreach ($empty_repositories as $key => $value) {
                $srcobj = new ProjectSourceRepository($value['obj_id']);
                $empty_repositories[$i]["view_url"] = $srcobj->getViewUrl();
                $i++;
            }
        }

        $delete_url = Router::assemble('delele_repo_url');
        $this->response->assign(array(
            'settings' => $settings,
            'empty_repositories' => $empty_repositories,
            'setup_script' => $setup_script,
            'gitoliteuser' => $gitoliteuser,
            'gitoliteadminpath' => $gitoliteadminpath,
            'server_name' => $server_name,
            'git_server_location' => $git_server_location,
            'delete_url' => $delete_url,
            'git_ssh_port' => $git_ssh_port,
            'is_enable' => $is_enable
        ));
    }

    /**
     * Save gitolite admin settings
     * @return void
     */
    function gitolite_admin() {

        //fetch current data

        $settings = GitoliteAdmin :: get_admin_settings();


        $setup_script = GitoliteAdmin :: get_setup_path();

        $gitoliteadminpath = GitoliteAdmin :: get_admin_path();

        $domain_name = GitoliteAdmin :: get_server_name();

        $gitoliteadminpath = "$gitoliteadminpath/gitolite/";
        $gitoliteadminpath_show = $gitoliteadminpath . "gitolite-admin/";

        $web_user = GitoliteAdmin::get_web_user();
        $webuser_pub_key = GitoliteAdmin::get_web_user_key();

        if ($settings['gitoliteuser'] == "") {
            $gitoliteuser = "git";
            $is_enable = FALSE;
        } else {
            $gitoliteuser = $settings['gitoliteuser'];
            $is_enable = TRUE;
        }

        $server_name = ($settings['gitoliteserveradd'] == "") ? $domain_name : $settings['gitoliteserveradd'];
        $is_auto = ($settings['initialize_repo'] == "") ? "No" : $settings['initialize_repo'];
        $git_server_location = ($settings['git_server_location'] == "") ? "local" : $settings['git_server_location'];
        $this->response->assign(
                array('gitoliteuser' => $gitoliteuser,
                    'gitoliteserveradd' => $settings['gitoliteserveradd'],
                    'git_ssh_port' => isset($settings['git_ssh_port']) ? $settings['git_ssh_port'] : 22,
                    'git_server_location' => $git_server_location,
                    'webuser' => $web_user,
                    'gitoliteadminpath' => $gitoliteadminpath,
                    'gitoliteadminpath_show' => $gitoliteadminpath_show,
                    'gitolite_repo_test_connection_url' => Router::assemble('gitolite_test_connection'),
                    'save_admin_settings_url' => Router::assemble('save_admin_settings'),
                    'check_user_exists_url' => Router::assemble('check_user_exists'),
                    'setup_script' => $setup_script,
                    'web_user' => $web_user,
                    'server_name' => $server_name,
                    'is_enable' => $is_enable,
                    'is_auto' => $is_auto,
                    'initialize_repo' => $settings['initialize_repo'],
                    'ignore_files' => $settings['ignore_files'],
                    'webuser_pub_key' => $webuser_pub_key,
                    'map_users_url' => Router::assemble('map_users'),
                    'gitolite_admin_url' => Router::assemble('gitolite_admin')
                )
        );


        if ($this->request->isSubmitted()) { // check for form submission
            $errors = new ValidationErrors();
            $post_data = $this->request->post("gitoliteadmin");

            try {
                $git_server_location = (isset($post_data["git_server_location"]) && $post_data["git_server_location"] != "local") ? "remote" : "local";
                $server_location["git_server_location"] = $git_server_location;
                $post_data = array_merge($server_location, $post_data);

                //array_push($array, $post_data)

                DB::beginWork('Save admin settings @ ' . __CLASS__);
                $setting_exists = GitoliteAdmin :: setting_exists();
                if ($setting_exists['cnt_settings'] == 0) {
                    $settings_add = GitoliteAdmin :: insert_settings($post_data, $this->logged_user->getId());
                    if ($web_user == "") {
                        $errors->addError('PHP running user not detected.');
                        throw $errors;
                    }
                    if (!$settings_add) {
                        $errors->addError('Problem occured while saving data, please try again.');
                        throw $errors;
                    }
                } else {
                    $settings_update = GitoliteAdmin :: update_settings($post_data, $this->logged_user->getId());
                }
                DB::commit('Admin Settings Saved @ ' . __CLASS__);
                $gitoliteadminpath = $post_data["gitoliteadminpath"];
                // append gitolite-admin dir name
                $gitoliteadminpath.="gitolite-admin/";
                $array_path = array("gitoliteadminpath_admin" => $gitoliteadminpath);
                $post_data = array_merge($array_path, $post_data);

                $this->response->respondWithData($post_data, array('as' => 'settings'));
            } catch (Exception $e) {
                DB::rollback('Failed to create a repository @ ' . __CLASS__);
                $this->response->exception($e);
            }
            die();
        }
    }

    /*
     * Test connection with gitolite server
     * @return string message
     */

    function test_connection() {

        if (!(array_var($_REQUEST, 'dir')) || !(array_var($_REQUEST, 'user')) || !(array_var($_REQUEST, 'server'))) {
            die(lang('Please fill in all the connection parameters'));
        }
        if (!(array_var($_REQUEST, 'dir'))) {
            die('Gitolite admin path not found');
        } //if

        if (!self::exec_enabled()) {
            die("Please enable `exec` on this sever");
        }

        $cloneurl = array_var($_REQUEST, 'user') . "@" . array_var($_REQUEST, 'server') . ":";
        if (intval(array_var($_REQUEST, 'port')) != 22) {
            $cloneurl = "ssh://" . $cloneurl . array_var($_REQUEST, 'port') . "/";
        }
        switch (array_var($_REQUEST, 'case')) {
            case "connect":
                $comd = "ssh -p " . array_var($_REQUEST, 'port') . " -T " . array_var($_REQUEST, 'user') . "@" . array_var($_REQUEST, 'server') . " | grep gitolite-admin | grep 'R W'";
                exec($comd, $output);
                if (count($output) > 0 && preg_match("/R W/", $output[0]) && preg_match("/gitolite-admin/", $output[0])) {
                    die("ok");
                } else {
                    echo "Unable to connect to server";
                    if ($output) {
                        if (is_array($output)) {
                            foreach ($output as $ob) {
                                echo "<br/> " . $ob;
                            }
                        } else {
                            echo "<br/> " . $output;
                        }
                    }
                    die();
                }
                break;
            case "permission":
                if (is_dir(GIT_FILES_PATH)) {
                    if (is_writable(GIT_FILES_PATH)) {
                        die("ok");
                    } else {
                        die(GIT_FILES_PATH . " is not writable");
                    }
                } else {
                    if (mkdir(GIT_FILES_PATH)) {
                        die("ok");
                    } else {
                        die(GIT_FILES_PATH . " is not writable");
                    }
                }

                break;
            case "setup":
                if (!is_dir(array_var($_REQUEST, 'dir'))) {
                    if (mkdir(array_var($_REQUEST, 'dir'))) {
                        $comd = "cd " . array_var($_REQUEST, 'dir') . " &&  git clone " . $cloneurl . "gitolite-admin.git || pwd";
                        unset($output);
                        exec($comd, $output);
                        if (count($output)) {
                            $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git config user.name";
                            exec($cmd, $output);
                            if (empty($output)) {
                                $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git config user.name " . GitoliteAdmin::get_web_user();
                                exec($cmd, $output);
                                $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git config user.email" . GitoliteAdmin::get_web_user() . "@" . array_var($_REQUEST, 'server');
                                exec($cmd, $output);
                            }
                            $conf_path = GIT_FILES_PATH . "/gitolite/gitolite-admin/conf/gitolite.conf";
                            $admin_path = GIT_FILES_PATH . "/gitolite/gitolite-admin/conf/";
                            $fh = fopen($conf_path, "a+");
                            $webuser = exec("whoami");

                            fwrite($fh, "repo " . "ac_rt_demo" . "\n");
                            fwrite($fh, "RW+" . "\t" . "=" . "\t" . $webuser . "\n");

                            fwrite($fh, "repo " . "@all" . "\n");
                            fwrite($fh, "RW+" . "\t" . "=" . "\t" . $webuser . "\n");
                            fclose($fh);
                            //chdir($admin_path);
                            unset($output);
                            //set global 
                            exec("cd $admin_path && git commit -am 'add php user on all repos' && git push", $output);
                            die("ok");
                        } else {
                            die("Unable to connect server");
                        }
                    } else {
                        die("Unable to create folder " . array_var($_REQUEST, 'dir'));
                    }
                } else {
                    if (is_dir(array_var($_REQUEST, 'dir') . "gitolite-admin")) {

                        $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git remote -v";
                        unset($output);
                        exec($cmd, $output);
                        if (empty($output)) {
                            $comd = "mv  " . array_var($_REQUEST, 'dir') . "gitolite-admin " . array_var($_REQUEST, 'dir') . "gitolite-admin.$(date +%s) && cd " . array_var($_REQUEST, 'dir') . " &&  git clone " . $cloneurl . "gitolite-admin.git || pwd";
                            unset($output);
                            exec($comd, $output);
                            if (count($output)) {
                                
                            } else {
                                die("Unable to connect git server");
                            }
                        } else {
                            if (strpos(strtolower($output[0]), strtolower(array_var($_REQUEST, 'user') . "@" . array_var($_REQUEST, 'server'))) === false) {
                                $comd = "mv  " . array_var($_REQUEST, 'dir') . "gitolite-admin " . array_var($_REQUEST, 'dir') . "gitolite-admin.$(date +%s) && cd " . array_var($_REQUEST, 'dir') . " &&  git clone " . $cloneurl . "gitolite-admin.git || pwd";
                                unset($output);
                                exec($comd, $output);
                                if (count($output)) {
                                    
                                } else {
                                    die("Unable to connect git server");
                                }
                            }
                        }


                        $this->_create_demo_repo();
                        die("ok");
                    } else {
                        $comd = "cd " . array_var($_REQUEST, 'dir') . " &&  git clone " . $cloneurl . "gitolite-admin.git || pwd";
                        exec($comd, $output);
                        $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git config user.name";
                        unset($output);
                        exec($comd, $output);
                        if (empty($output)) {
                            $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git config user.name " . GitoliteAdmin::get_web_user();
                            exec($cmd, $output);
                            $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git config user.email" . GitoliteAdmin::get_web_user() . "@" . array_var($_REQUEST, 'server');
                            exec($cmd, $output);
                        }
                        $this->_create_demo_repo();
                        die("ok");
                    }
                }
                break;
        }
        die("Some error !!");
    }

    function _create_demo_repo() {
        $conf_path = GIT_FILES_PATH . "/gitolite/gitolite-admin/conf/gitolite.conf";
        $admin_path = GIT_FILES_PATH . "/gitolite/gitolite-admin/conf/";
        $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git config user.name";
        exec($cmd, $output);
        if (empty($output)) {
            $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git config user.name " . GitoliteAdmin::get_web_user();

            exec($cmd, $output);
            $cmd = "cd " . array_var($_REQUEST, 'dir') . "gitolite-admin && git config user.email" . GitoliteAdmin::get_web_user() . "@" . array_var($_REQUEST, 'server');

            exec($cmd, $output);
            unset($output);
        }

        $content = file_get_contents($conf_path);
        if (strpos($content, "ac_rt_demo") === false) {
            $fh = fopen($conf_path, "a+");
            $webuser = exec("whoami");
            fwrite($fh, "\nrepo  ac_rt_demo \n");
            fwrite($fh, "\tRW+ \t = \t" . $webuser . "\n");
            fclose($fh);
            exec("cd $admin_path && git commit -am 'add demo repo' && git push", $output);
        } else {
            exec("cd $admin_path && git commit -am 'retry add demo repo' --allow-empty && git push", $output);
        }
        if (isset($_REQUEST["type"]) && $_REQUEST["type"] == "local") {
            $comd = "echo ~" . array_var($_REQUEST, 'user');
            $output = array();
            exec($comd, $output);
            if (!empty($output) && is_dir($output[0]) && $output[0] != "~" . array_var($_REQUEST, 'user')) {
                if (is_readable($output[0] . "/repositories/ac_rt_demo.git")) {
                    die("ok");
                } else {
                    die($output[0] . "/repositories/ac_rt_demo.git is not readable");
                }
            } else {
                die("No Home directory Found for user " . array_var($_REQUEST, 'user'));
            }
        }
        else
            die("ok");
    }

    function check_user_exists() {
        if (!self::exec_enabled()) {
            die("Please enable `exec` on this sever");
        }
        if (!(array_var($_REQUEST, 'user'))) {
            die('username is empty');
        } //if
        if (file_exists(ROOT . "/../www-data.pub")) {
            $comd = "rm  " . ROOT . "/../www-data.pub";
            exec($comd);
        }
        if (isset($_REQUEST["type"]) && $_REQUEST["type"] == "remote") {
            if (file_exists(ROOT . "/../mypubkey.pub")) {
                $comd = "cp  " . ROOT . "/../mypubkey.pub " . ROOT . "/../www-data.pub";
                exec($comd);
            }
            die("ok");
        }

        $comd = "id " . array_var($_REQUEST, 'user');
        exec($comd, $output);



        if (!isset($_REQUEST["existing"]))
            $_REQUEST["existing"] = "false";

        if ($_REQUEST["existing"] == "true") {
            if (empty($output)) {
                die("User doesnot exists,Please check  username");
            } else {
                die("ok");
            }
        } else {
            if (empty($output)) {
                die("ok");
            } else {
                die("User already exists,Please choose a diffrent username");
            }
        }
    }

    /**
     * Save admin settings.
     * @return string message
     */
    function save_admin_settings() {

        if (!(array_var($_REQUEST, 'dir')) || !(array_var($_REQUEST, 'user')) || !(array_var($_REQUEST, 'server')) || !(array_var($_REQUEST, 'server_location'))) {
            die(lang('Please fill in all the connection parameters'));
        }
        try {

            $save_data["gitoliteuser"] = array_var($_REQUEST, 'user');
            $save_data["gitoliteserveradd"] = array_var($_REQUEST, 'server');
            $save_data["gitoliteadminpath"] = array_var($_REQUEST, 'dir');
            $save_data["git_server_location"] = array_var($_REQUEST, 'server_location');


            DB::beginWork('Save admin settings @ ' . __CLASS__);
            $setting_exists = GitoliteAdmin :: setting_exists();
            if ($setting_exists['cnt_settings'] == 0) {
                $settings_add = GitoliteAdmin :: insert_settings($save_data, $this->logged_user->getId());
                if (!$settings_add) {
                    die("Problem occured while saving data, please try again.");
                }
            } else {
                $settings_update = GitoliteAdmin :: update_settings($save_data, $this->logged_user->getId());
            }
            DB::commit('Admin Settings Saved @ ' . __CLASS__);

            $scan_pub_keys = $this->parse_user_keys();
            $scan_repos = $this->parse_repos();
            if (count($scan_pub_keys) == 0 && count($scan_repos) == 0) {
                die("nomap");
            } else {
                die("map");
            }
        } catch (Exception $e) {
            DB::rollback('Save admin settings @ ' . __CLASS__);
            die("Problem occured while saving data, please try again.");
        }
    }

    /**
     * Delete repository from system
     * @return string message
     */
    function delete_repo() {
        $repoid = array_var($_GET, 'repoid');
        if ($repoid != "") {
            $this->active_repository = SourceRepositories::findById($repoid);
            $this->active_repository->delete();
            $repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
            $repo_access_table_name = TABLE_PREFIX . 'rt_gitolite_access_master';
            DB::execute("DELETE repo_acc,repo_tb FROM $repo_table_name repo_tb
                        JOIN $repo_access_table_name repo_acc ON repo_acc.repo_id = repo_tb.repo_id
                        WHERE repo_tb.repo_fk = '" . $this->active_repository->getId() . "'");
            die("ok");
        } else {
            die("Problem occured while deleting repository");
        }
    }

    /**
     * Show help for gitolite settings.
     * @return void
     */
    function need_help() {
        $settings = GitoliteAdmin :: get_admin_settings();
        if (isset($settings["gitoliteuser"]) && $settings["gitoliteuser"] != "") {
            $settings["gitoliteuser"] = "git";
        }
        $setup_script = GitoliteAdmin :: get_setup_path($settings["gitoliteuser"], false);


        $this->response->assign(
                array('setup_script' => $setup_script)
        );
    }

    /*
     * check whether exec is enabled on server
     * @return void
     */

    function exec_enabled() {
        $disabled = explode(', ', ini_get('disable_functions'));
        return !in_array('exec', $disabled);
    }

    /**
     * Map remote conf users with activeclollab users
     */
    function map_conf_user() {
        if (isset($_GET["user_ids"])) {

            $user_ids = $_GET["user_ids"];
            $pub_keys = $_GET["pub_keys_str"];
            $pub_key_access = $_GET["pub_key_access"];

            // start mapping keys
            try {
                DB::beginWork('Mapping keys @ ' . __CLASS__);
                foreach ($user_ids as $key => $value) {

                    if (isset($value) && $value != "na") {
                        $key_dir_file_path = GIT_FILES_PATH . "/gitolite/gitolite-admin/keydir/" . $pub_keys[$key] . ".pub";
                        if (file_exists($key_dir_file_path)) {
                            $key_content = file_get_contents($key_dir_file_path);
                            // add keys to user
                            $key_id = GitoliteAc :: add_keys($value, $pub_keys[$key], array("key_name" => $pub_keys[$key], "public_keys" => $key_content));
                            if (!$key_id) {
                                throw new Exception('Cannot map keys, try again.', 0, $e);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                DB::rollback('Mapping keys @ ' . __CLASS__);
                die("Cannot map keys, try again.");
            }
            DB::commit('Repository created @ ' . __CLASS__);
            die("ok");
        }
        $array_pub_keys_access = array();
        $get_ac_users = new Users();
        $ac_users = $get_ac_users->getIdNameMap();
        self::$ac_users = $ac_users;
        $ac_users[""] = "Select a user";
        ksort($ac_users);

        $conf_file_path = GIT_FILES_PATH . "/gitolite/gitolite-admin/conf/gitolite.conf";

        $web_user = GitoliteAdmin::get_web_user();

        if (file_exists($conf_file_path)) {
            /** get keys list * */
            $conf_file_contents = file($conf_file_path);


            $array_pub_keys = self::parse_user_keys();



            $projects = new Projects();
            $ac_projects = $projects->getIdNameMap($this->logged_user);
            $ac_projects[""] = "Select Project";
            ksort($ac_projects);
            $array_repos = self::parse_repos();
        } else {
            
        }


        $this->response->assign(
                array('ac_users' => $ac_users,
                    'array_pub_keys' => $array_pub_keys,
                    'array_pub_keys_access' => $array_pub_keys_access,
                    'map_users_url' => Router::assemble('map_users'),
                    'ac_projects' => $ac_projects,
                    'array_repos' => $array_repos,
                    'map_projects_url' => Router::assemble('map_repos'),
                    'render_after_clone_url' => Router::assemble('render_after_clone'),
                    'admin_index_url' => Router::assemble('gitolite_admin'))
        );
    }

    /**
     * Map conf Repositories with activecollab projects
     * @return string message
     */
    function map_conf_repos() {

        $repo_array = array();
        if (count($repo_array) == 0) {

            $conf_file_path = GIT_FILES_PATH . "/gitolite/gitolite-admin/conf/gitolite.conf";
            if (file_exists($conf_file_path)) {

                $conf_file_contents = file($conf_file_path);
                foreach ($conf_file_contents as $key => $value) {

                    if (preg_match('/^repo\s+(.+)/', $value, $matches) && !preg_match('/^repo\s+(gitolite-admin)/', $value) && !preg_match('/^repo\s+(@all)/', $value)) {
                        $repo_name = trim($matches[1]);
                        if (!array_key_exists($repo_name, $conf_parsed)) {
                            $repo_array[$repo_name] = array();
                        }
                    } elseif (preg_match('/(.*)=\s(.*)/', $value, $matches)) {
                        $pub_key = trim($matches[2]);
                        $pub_key_access = trim($matches[1]);
                        $repo_array[$repo_name][] = array("key_name" => $pub_key, "pub_key_access" => $pub_key_access);
                    }
                }
            }
        }
        if (isset($_GET["prj_name"])) {

            $prj_id = $_GET["prj_name"];
            $repo_name = $_GET["repo_name"];
            $user_id = $this->logged_user->getId();
            $project_obj = new Project($prj_id);
            $users_details = $project_obj->users()->describe($this->logged_user, true, true, STATE_VISIBLE);
            $users_array = array();
            if (is_foreachable($users_details)) {
                foreach ($users_details as $key => $value) {
                    $users_array[] = $value['user']['id'];
                }
            }
            $access_array = array();


            try {
                DB::beginWork('Mapping Repositories @ ' . __CLASS__);
                $selected_prj = new Project($prj_id);
                $repository_data = array(
                    'name' => $repo_name,
                    'update_type' => 1,
                    'visibility' => 0
                );
                $settings = GitoliteAdmin :: get_admin_settings();
                $clone_url = $settings['gitoliteuser'] . "@" . $settings['gitoliteserveradd'] . ":" . $repo_name;
                $dup_cnt = ProjectGitolite::check_remote_duplication($prj_id, $repository_data, $clone_url);
                if ($dup_cnt[1]['dup_name_cnt'] > 0) {
                    die('Remote URL already cloned under this project.');
                }


                /**
                 * Create access data
                 * 
                 */
                /* define('GITOLITE_NOACCESS', '1');
                  define('GITOLITE_READACCESS', '2');
                  define('GITOLITE_MANAGEACCESS', '3'); */


                if (is_array($repo_array) && count($repo_array) > 0) {

                    if (array_key_exists($repo_name, $repo_array)) {
                        if (is_foreachable($repo_array[$repo_name])) {
                            foreach ($repo_array[$repo_name] as $key => $value) {
                                //echo $value["key_name"]."====".$value["pub_key_access"];
                                $key_details = GitoliteAc::get_key_details($value["key_name"]);
                                if (is_array($key_details) && in_array($key_details["user_id"], $users_array)) {

                                    $access_array[$key_details["user_id"]] =
                                            ($value["pub_key_access"] == "RW+") ? 3 : 2;
                                }
                            }
                        }
                    }
                }

                /**
                 * Add rrepositories
                 */
                $actual_git_repo_name = $repo_name;
                $chk_actual_name_exists_cnt = ProjectGitolite::check_actual_name_count_gitolite($actual_git_repo_name);
                if (is_array($chk_actual_name_exists_cnt) && isset($chk_actual_name_exists_cnt["actual_name_cnt"])) {

                    $cnt = ($chk_actual_name_exists_cnt["actual_name_cnt"] > 0) ? $chk_actual_name_exists_cnt["actual_name_cnt"] + 1 : "";
                    $folder_append = ($cnt != "") ? "-$cnt" : "";
                } else {
                    $folder_append = "-1";
                }

                // if git repsitory name is same , we need to change the folder name while cloning the repository
                $folder_name = $actual_git_repo_name . $folder_append;

                $work_git_path = GIT_FILES_PATH . "/" . $folder_name . "/";
                $repository_path_url = array('repository_path_url' => $work_git_path);
                $repository_data = array_merge($repository_data, $repository_path_url);

                $this->active_repository = new GitRepository();
                $this->active_repository->setAttributes($repository_data);
                $this->active_repository->setCreatedBy($this->logged_user);
                $this->active_repository->save();


                $repo_fk = $this->active_repository->getId();



                if ($repo_fk) {
                    $clone_url = $settings['gitoliteuser'] . "@" . $settings['gitoliteserveradd'] . ":" . $repo_name;
                    $body = $clone_url;

                    $prj_obj = new ProjectSourceRepository();
                    $prj_obj->setName($this->active_repository->getName());
                    $prj_obj->setBody($body);
                    $prj_obj->setVisibility($repository_data['visibility']);
                    $prj_obj->setProjectId($prj_id);
                    $prj_obj->setCreatedBy($this->logged_user);
                    $prj_obj->setType("ProjectSourceRepository");
                    $prj_obj->setModule("source");
                    $prj_obj->setState(STATE_VISIBLE);
                    $prj_obj->setParentId($this->active_repository->getId());

                    $prj_obj->save();

                    $repo_id = ProjectGitolite::add_repo_details($repo_fk, $prj_id, $user_id, $work_git_path, $repository_data, $clone_url);
                    if ($repo_id) {

                        $add_access = ProjectGitolite::add_access_levels($repo_id, serialize($access_array), $user_id, 1);
                        if ($add_access) {

                            DB::commit('Repository mapped @ ' . __CLASS__);
                            $git_server = $settings['git_clone_url'];
                            chdir(GIT_FILES_PATH);
                            //cd ".GIT_FILES_PATH." && 
                            $command = "git clone " . $git_server . $repo_name . " $folder_name";
                            //$command = "git clone ".$git_server.":".$repo_name;
                            exec($command, $output, $return_var);
                            $out = GitoliteAdmin::update_remote_repo($repo_fk);
                            ini_set('max_execution_time', 500);
                            $pull_all_branches = ProjectGitolite::pull_branches($actual_repo_path);
                            if (!$pull_all_branches) {
                                @ProjectGitolite::remove_directory($work_git_path);
                                die("Error while saving branches.");
                                throw $errors;
                            }
                            die("ok");
                        } else {
                            DB::rollback('Failed to map repository @ ' . __CLASS__);
                            echo "Error while saving access levels.";
                        }
                    } else {
                        DB::rollback('Failed to map repository @ ' . __CLASS__);
                        echo "Error while saving repository";
                    }
                }
            } catch (Exception $e) {
                DB::rollback('Mapping repositories @ ' . __CLASS__);

                die("Cannot map repository, try again.");
            }
            /**/
        }
    }

    /**
     * Render conf file once mapping is done
     * @return string message
     */
    function render_after_clone_conf() {
        $res = ProjectGitolite::render_conf_file();
        $settings = GitoliteAdmin :: get_admin_settings();
        $dir = $settings['gitoliteadminpath'] . "gitolite-admin";
        $command = "cd " . $dir . " && git add * && git commit -am 'render conf file' && git push  || echo 'Not found'";
        exec($command, $output, $return_var);
        //print_r($output);
        die("ok");
    }

    /**
     * Parse keydir folder and fetch all keys added on gitolite setup
     * @return array keys
     */
    function parse_user_keys() {

        $array_pub_keys = array();
        $web_user = GitoliteAdmin::get_web_user();
        $key_dir_path = GIT_FILES_PATH . "/gitolite/gitolite-admin/keydir/";
        chdir($key_dir_path);
        foreach (glob('*.pub') as $i => $key) {
            //echo $key."<br>";
            $key = preg_replace('/(|@[^.]*)\.pub$/', '', $key);
            $key_dir_file_path = $key_dir_path . $key . ".pub";

            if (file_exists($key_dir_file_path)) {
                $key_content = file_get_contents($key_dir_file_path);
                $fetch_actual_key = explode(" ", $key_content);
                $actual_key = (is_array($fetch_actual_key) && count($fetch_actual_key) > 0) ? $fetch_actual_key[1] : $fetch_actual_key[0];
                $key_exists_details = GitoliteAc::check_key_map_exists($actual_key);
            }
            if (!in_array($key, $array_pub_keys) && $key != "" && !is_array($key_exists_details) && $key != $web_user) {
                $array_pub_keys[] = $key;
            } elseif (is_array($key_exists_details) && count($key_exists_details) > 0) {
                $array_pub_keys[$key_exists_details["key_name"]] = self::$ac_users[$key_exists_details["user_id"]];
            }
        }
        return $array_pub_keys;
    }

    /**
     * Parse conf file and fetch all repositories to map with activecollab projects
     * @return array repositories
     */
    function parse_repos() {
        $conf_file_path = GIT_FILES_PATH . "/gitolite/gitolite-admin/conf/gitolite.conf";
        $conf_file_contents = file($conf_file_path);
        $array_repos = array();
        if (is_array($conf_file_contents) && count($conf_file_contents)) {
            foreach ($conf_file_contents as $key => $value) {
                if (preg_match('/^repo\s+(.+)/', $value, $matches) && !preg_match('/^repo\s+(gitolite-admin)/', $value) && !preg_match('/^repo\s+(@all)/', $value)) {
                    $repo_name = trim($matches[1]);
                    if (!in_array($repo_name, $array_repos) && $repo_name != "" && !is_array($if_exists)) {
                        $array_repos[] = $repo_name;
                    }
                    /* else
                      {
                      $array_repos[$if_exists["name"]] = $repo_name;
                      } */
                }
            }
        }
        return $array_repos;
    }

}
