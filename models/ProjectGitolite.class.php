<?php

/**
 * ProjectGitolite class
 *
 * @package custom.modules.ac_gitolite
 * @subpackage models
 */
class ProjectGitolite {

    /**
     * Check whether repository name key already exists for same project;
     * @param type $active_user
     * @param type $post_data
     * @return type
     */
    function check_duplication($active_project = 0, $post_data = array()) {
        if (count($post_data) == 0) {
            return array();
        }
        $objects_table_name = TABLE_PREFIX . 'project_objects';
        $source_table_name = TABLE_PREFIX . 'source_repositories';

        $result = DB::execute("SELECT a.*, COUNT(b.id) as dup_name_cnt ,b.id FROM " . $objects_table_name . " a 
                                  JOIN " . $source_table_name . " b ON a.integer_field_1 = b.id 
                                  where b.name = '" . $post_data['name'] . "'");
        if ($result) {
            $dup_repo_name[] = $result->getRowAt(0);
        }
        return $dup_repo_name;
    }

    function check_source_git_dup($post_data = array()) {
        $dup_repo_name = FALSE;
        if (count($post_data) == 0) {
            return array();
        }
        $rt_repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
        $result = DB::execute("SELECT repo_fk FROM " . $rt_repo_table_name . " where repo_name = '" . $post_data["name"] . "'");
        if ($result) {
            $dup_repo_name = $result->getRowAt(0);
        }
        return $dup_repo_name;
    }

    /**
     * Check whether repository is already mapped with project.
     * @param string $repo_name
     * @return array $dup_repo_name
     */
    function check_repo_map_exists($repo_name = "") {
        $dup_repo_name = false;
        if ($repo_name == "") {
            return false;
        }
        $rt_repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
        $result = DB::execute("SELECT repo_fk FROM " . $rt_repo_table_name . " where repo_name = '" . $repo_name . "'");
        if ($result) {
            $dup_repo_name = $result->getRowAt(0);

            $objects_table_name = TABLE_PREFIX . 'project_objects';
            $project_table_name = TABLE_PREFIX . 'acx_projects';

            $result = DB::execute("SELECT b.name ,b.id FROM " . $objects_table_name . " a 
                                  JOIN " . $project_table_name . " b ON a.project_id = b.id 
                                  where a.integer_field_1 = '" . $dup_repo_name['repo_fk'] . "'");
            if ($result) {
                return $prj_name = $result->getRowAt(0);
            }
        }
        return $dup_repo_name;
    }

    /**
     * Check remote repository duplication
     * @param integer $project_id
     * @param array $repository_data
     * @return boolean
     */
    function check_remote_duplication($project_id, $repository_data, $repo_url) {
        $dup_remote_repo = false;
        /*
          if(!is_numeric($project_id) || count($repository_data) == 0)
          {
          return FALSE;
          }
          $source_table_name = TABLE_PREFIX . 'source_repositories';
          $objects_table_name = TABLE_PREFIX . 'project_objects';
          $result = DB::execute("SELECT a.*, COUNT(b.id) as dup_url_cnt ,b.id FROM ".$objects_table_name." a
          JOIN ".$source_table_name." b ON a.integer_field_1 = b.id
          where b.name = '".$repository_data['name']."'
          and a.project_id = '".$project_id."'");
          if($result)
          {
          $dup_repo_name[] = $result->getRowAt(0);
          }
          return $dup_repo_name; */

        $source_table_name = TABLE_PREFIX . 'source_repositories';
        $objects_table_name = TABLE_PREFIX . 'project_objects';
        $result = DB::execute("SELECT COUNT(id) as dup_name_cnt from " . $source_table_name . "
                                where  name = '" . $repository_data['name'] . "' 
                                UNION
                                SELECT COUNT(b.id) as dup_url_cnt FROM " . $objects_table_name . " a 
                                JOIN " . $source_table_name . " b ON a.integer_field_1 = b.id 
                                where a.body = '" . $repo_url . "' 
                                and a.project_id = '" . $project_id . "'");

        if ($result) {
            $dup_remote_repo[] = $result->getRowAt(0);
            $dup_remote_repo[] = $result->getRowAt(1);
        }
        return $dup_remote_repo;
    }

    /**
     * Save repository details in database.
     * @param type $active_project
     * @param type $user_id
     * @param type $admin_path
     * @param type $post_data
     * @return boolean
     */
    function add_repo_details($repo_fk, $active_project = 0, $user_id = 0, $repo_path, $post_data = array(), $clone_url) {
        if (!is_numeric($repo_fk) || !is_numeric($active_project) || count($post_data) == 0 || !is_numeric($user_id) || $repo_path == "" || $clone_url == "") {
            return FALSE;
        }
        $repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';

        DB::execute("INSERT INTO $repo_table_name (repo_fk,project_id,repo_name,git_repo_path,repo_created_by,git_ssh_path,disable_notifications) VALUES (? ,?, ?, ?, ?, ?,?)", $repo_fk, $active_project, trim($post_data['name']), $repo_path, $user_id, $clone_url, $post_data['repo_notification_setting']
        );
        return DB::lastInsertId();
    }

    function add_remote_repo_details($repo_fk, $user_id = 0, $repo_path, $repo_name, $repo_url = "", $actual_git_repo_name) {

        if (!is_numeric($repo_fk) || $repo_name == "" || !is_numeric($user_id) || $repo_path == "" || $repo_url == "" || $actual_git_repo_name == "") {
            return FALSE;
        }
        $repo_table_name = TABLE_PREFIX . 'rt_remote_repos';

        DB::execute("INSERT INTO $repo_table_name (repo_fk,remote_repo_name,remote_repo_path,remote_repo_url,repo_created_by,actual_repo_name) VALUES (? ,?, ?, ?, ?, ?)", $repo_fk, trim($repo_name), trim($repo_path), trim($repo_url), $user_id, $actual_git_repo_name
        );

        return DB::lastInsertId();
    }

    /**
     * Add access levels of repositories for users
     * @param type $repo_id
     * @param type $permissions
     * @param type $user_id
     * @param type $group_id
     * @return boolean
     */
    function add_access_levels($repo_id = 0, $permissions, $user_id, $group_id = "") {
        if (!is_numeric($repo_id) || $permissions == "" || !is_numeric($user_id) || $group_id == "") {
            return FALSE;
        }
        $access_table_name = TABLE_PREFIX . 'rt_gitolite_access_master';
        DB::execute("INSERT INTO $access_table_name (repo_id,permissions,user_id,group_id) VALUES (?, ?, ?, ?)", $repo_id, $permissions, $user_id, $group_id
        );
        $last_id = DB::lastInsertId();
        ProjectGitolite::update_repo_conf_column($repo_id, $permissions);
        return $last_id;
    }

    function update_repo_conf_on_public_key($user) {

        $access_table_name = TABLE_PREFIX . 'rt_gitolite_access_master';
        $result = DB::execute("select  repo_id,permissions  from $access_table_name where permissions like '%i:$user;%'");
        if ($result) {
            while ($row = mysql_fetch_assoc($result->getResource())) {
                ProjectGitolite::update_repo_conf_column($row["repo_id"], unserialize($row["permissions"]));
            }
        }
        return false;
    }

    function get_deploy_key_config($repo_id) {
        $deploy_key_table_name = TABLE_PREFIX . 'rt_deploy_keys';
        $sql_get_deploy_keys = "SELECT `pub_file_name` from $deploy_key_table_name where repo_fk = '$repo_id'";
        $result = DB::execute($sql_get_deploy_keys);
        $str = "";
        if($result) {
            while($row = mysql_fetch_array($result->getResource())) {            
                $str.= "\t R \t=\t" . $row["pub_file_name"] . "\n";
            }
        }        
        return $str;
    }
    public function update_repo_conf_column($repo_id, $access_level = false) {
        $repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
        $public_key_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
        if ($access_level == false) {
            $access_level_row = ProjectGitolite::get_access_levels($repo_id);
            if ($access_level_row && isset($access_level_row["permissions"])) {
                $access_level = unserialize($access_level_row["permissions"]);
            }
        }
        $repo_conf_str = "";
        $access_array = array(GITOLITE_READACCESS => 'R', GITOLITE_MANAGEACCESS => 'RW+');
        $result = DB::execute("SELECT * FROM " . $repo_table_name . " where repo_id = " . $repo_id . " limit 1");
        if ($result) {
            if ($row = mysql_fetch_assoc($result->getResource())) {
                if (!array_key_exists('gitolite_config', $row)) {
                    mysql_query("ALTER TABLE $repo_table_name ADD column `gitolite_config` text NULL");
                }
                $repo_conf_str = ""; //repo " .  $row['repo_name'] . "\n";
                if (!is_array($access_level) && $access_level != "") {
                    $access_level = unserialize($access_level);
                }
                $prjobj = new Project($row['project_id']);
                $prjusers = $prjobj->users()->getIds();
                if (is_array($prjusers)) {
                    $str = "";
                    $sep = "";
                    foreach ($prjusers as $user) {
                        $str .= $sep . $user;
                        $sep = ",";
                    }
                    $sql = "select user_id,pub_file_name  from  $public_key_table_name where  is_deleted = '0' and user_id in ($str) order by user_id;";
                    $key_result = DB::execute($sql);
                    if ($key_result) {
                        while ($my_key_row = mysql_fetch_assoc($key_result->getResource())) {
                            $access = (isset($access_array[$access_level[$my_key_row["user_id"]]])) ? $access_array[$access_level[$my_key_row["user_id"]]] : false;
                            if ($access) {
                                $repo_conf_str .= "\t" . $access . "\t=\t" . $my_key_row["pub_file_name"] . "\n";
                            }
                        }
                    }
                    $deploy_key_config_str = ProjectGitolite::get_deploy_key_config($repo_id);
                    $repo_conf_str.= $deploy_key_config_str;
                    $sql = "update " . $repo_table_name . " set gitolite_config='" . $repo_conf_str . "' where repo_id =" . $repo_id;
                    DB::execute($sql);
                }
            }
        }
        return true;
    }

    /**
     * render_conf_file
     * Write repository and access levels in conf file
     * @return boolean|string
     */
    function render_conf_file() {

        $settings = GitoliteAdmin :: get_admin_settings();
        $conf_path = $settings['gitoliteadminpath'] . "/gitolite-admin/conf/gitolite.conf";
        $webuser = exec("whoami");


        $conf_file = $conf_path;

        // create access array
        $access_array = array(GITOLITE_READACCESS => 'R', GITOLITE_MANAGEACCESS => 'RW+');


        $fh = fopen($conf_file, 'w');

        if (file_exists($conf_path) && $fh) {
            $repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
            $access_table_name = TABLE_PREFIX . 'rt_gitolite_access_master';
            $public_key_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
            $source_table_name = TABLE_PREFIX . 'source_repositories';
            $admin_settings_table_name = TABLE_PREFIX . 'rt_config_settings';            
            
            /** Defalut access to gitolite admin * */
            $get_git_admins = DB::execute("SELECT * FROM " . $admin_settings_table_name);
            fwrite($fh, "repo " . "@all" . "\n");
            fwrite($fh, "RW+" . "\t" . "=" . "\t" . $webuser . "\n");
            fwrite($fh, "repo " . "gitolite-admin" . "\n");
            fwrite($fh, "RW+" . "\t" . "=" . "\t" . $webuser . "\n");




            if ($get_git_admins) {
                $admins_rec = $get_git_admins->getRowAt(0);
                if (is_array($admins_rec)) {
                    $admins = @unserialize($admins_rec['gitoliteadmins']);
                    if ($admins !== false || $admins === 'b:0;') {
                        $admins_array = $admins;
                    } else {
                        $admins_array = array();
                    }

                    if (is_foreachable($admins_array)) {
                        foreach ($admins_array as $keyadmin => $valadmin) {
                            $pubkeys = DB::execute("SELECT * FROM " . $public_key_table_name . " where user_id = '" . $valadmin . "' and is_deleted = '0'");
                            if ($pubkeys) {
                                while ($rowkeys = mysql_fetch_assoc($pubkeys->getResource())) {
                                    if ($rowkeys['pub_file_name'] != "") {
                                        fwrite($fh, $access_array[GITOLITE_MANAGEACCESS] . "\t" . "=" . "\t" . $rowkeys['pub_file_name'] . "\n");
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $sql = "SELECT a.* ,b.id FROM " . $repo_table_name . " a JOIN " . $source_table_name . " b ON a.repo_fk = b.id  where gitolite_config is null limit 1";
            $result_gitconfig_empty = DB::execute($sql);
            $file_name = "/home/strik3r/gitolite_" . microtime();
            $file_name = str_replace(" ", "-", $file_name);
            $file_name = str_replace(".", "_", $file_name);
            $mysqlACLFlag = true;
            if (!$result_gitconfig_empty) {
                $sql = "SELECT CONCAT('repo ', repo_name),gitolite_config INTO OUTFILE '$file_name'
                        FIELDS ESCAPED BY '' TERMINATED BY '\n' OPTIONALLY ENCLOSED BY ''
                        LINES TERMINATED BY '\n'  
                        FROM $repo_table_name ";
                try {
                    $result = DB::execute($sql);
                } catch (Exception $e) {
                    $mysqlACLFlag = false;
                }

                if ($mysqlACLFlag && file_exists($file_name)) {
                    $conf_content = file_get_contents($file_name);
                    fwrite($fh, $conf_content);
                    fclose($fh);
                    @unlink($file_name);
                    return true;
                } else {
                    $sql = "SELECT CONCAT('repo ', repo_name) as 'repo_name',gitolite_config FROM $repo_table_name";
                    $result = DB::execute($sql);
                    if ($result) {
                        while ($row = mysql_fetch_assoc($result->getResource())) {
                            $conf_content = "\n" . $row['repo_name'] . "\n" . $row['gitolite_config'];
                            fwrite($fh, $conf_content);
                        }
                    }
                    fclose($fh);
                    return true;
                }
            }

            $result = DB::execute("SELECT a.* ,b.id FROM " . $repo_table_name . " a JOIN " . $source_table_name . " b ON a.repo_fk = b.id");
            try {
                if ($result) {
                    //fetch all gitolite repositories
                    while ($row = mysql_fetch_assoc($result->getResource())) {


                        $prjobj = new Project($row['project_id']);
                        // get project users
                        $prjusers = $prjobj->users()->getIdNameMap();
                        // get permissions
                        $permissions = DB::execute("SELECT * FROM " . $access_table_name . " where repo_id = '" . $row['repo_id'] . "'");
                        if ($permissions) {   // get repository permissions
                            $perm_row = $permissions->getRowAt("0");
                            $permissions = @unserialize($perm_row['permissions']);
                            if ($permissions !== false || $permissions === 'b:0;') {
                                $permissions_array = $permissions;
                            } else {
                                $permissions_array = array();
                            }
                        } else {
                            $permissions_array = array();
                        }

                        // write repository name in conf file

                        fwrite($fh, "repo " . $row['repo_name'] . "\n");
                        $str_repo_conf = "";
                        if (is_foreachable($prjusers)) {
                            foreach ($prjusers as $keyusers => $valueusers) {
                                $pubkeys = DB::execute("SELECT * FROM " . $public_key_table_name . " where user_id = '" . $keyusers . "' and is_deleted = '0'");
                                if (is_object($pubkeys)) {

                                    while ($rowkeys = mysql_fetch_assoc($pubkeys->getResource())) {

                                        $access = (isset($access_array[$permissions_array[$keyusers]])) ? $access_array[$permissions_array[$keyusers]] : "";

                                        if ($access != "" && $rowkeys['pub_file_name'] != "") {
                                            fwrite($fh, $access . "\t" . "=" . "\t" . $rowkeys['pub_file_name'] . "\n");
                                            $str_repo_conf.= "\t" . $access . "\t" . "=" . "\t" . $rowkeys['pub_file_name'] . "\n";
                                        }
                                    } // while
                                }// if public keys added
                            } // foreach 
                        } // if project user exists
                        $sql = "update " . $repo_table_name . " set gitolite_config='" . $str_repo_conf . "' where repo_id =" . $row['repo_id'];
                        DB::execute($sql);
                    } // while
                } // repo exists
            } catch (Exception $e) {
                echo $e;
            }
            return true;
        }   // if file exists
        else {
            return "can't write file";
        }
    }

    /**
     * get_project_repo
     * Get all gitolite repositories under project
     * @param type $active_project
     * @return type
     */
    function get_project_repo($active_project = 0) {
        $repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
        $result = DB::execute("SELECT * from " . $repo_table_name . "
                                  where project_id = '" . $active_project . "'");


        if ($result) {
            while ($row = mysql_fetch_array($result->getResource())) {
                $reponames [] = $row['repo_name'];
            }
        }
        return $dup_repo_name;
    }

    /**
     * update_access_levels
     * Update access levels of repos
     * @param type $repo_id
     * @param type $permissions
     * @return boolean
     */
    function update_access_levels($repo_id = 0, $permissions = "") {
        $access_table_name = TABLE_PREFIX . 'rt_gitolite_access_master';
        if ($repo_id == 0 || $repo_id == "" || $permissions == "") {
            return FALSE;
        }
        /* echo "update  ".$access_table_name." set permissions = '$permissions' where repo_id = ".DB::escape($repo_id);
          die(); */
        $update_access = DB::execute("update  " . $access_table_name . " set permissions = '$permissions' where repo_id = " . DB::escape($repo_id));
        ProjectGitolite::update_repo_conf_column(DB::escape($repo_id), $permissions);
        return TRUE;
    }

    /**
     * is_gitolite_repo
     * Check whetger repository is gitolite repository and fetch permisisons of repository.
     * @param type $repo_fk
     * @return boolean
     */
    function is_gitolite_repo($repo_fk = 0) {

        if (!is_numeric($repo_fk) || $repo_fk == 0) {
            return false;
        }
        $repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
        $access_table_name = TABLE_PREFIX . 'rt_gitolite_access_master';
        $result = DB::execute("SELECT count(repo_fk) as chk_gitolite, b.permissions from $repo_table_name a , 
                                  $access_table_name b where a.repo_id = b.repo_id and
                                   repo_fk = '$repo_fk'");

        if ($result) {
            $cnt_array = $result->getRowAt("0");
            if (is_array($cnt_array) && count($cnt_array) > 0) {
                return $cnt_array;
            }
        } else {
            return false;
        }
    }

    /**
     * Get repository details
     * @param integer $repo_id
     * @return array repo details
     */
    function get_repo_details($repo_id = 0) {
        if (!is_numeric($repo_id) || $repo_id == 0) {

            return false;
        }

        $repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
        $objects_table_name = TABLE_PREFIX . 'project_objects';

        $result = DB::execute("SELECT a.repo_id,a.repo_name,a.git_repo_path,a.disable_notifications,b.name FROM $repo_table_name a, $objects_table_name b 
                                  where a.`repo_fk` = b.integer_field_1 and b.type = 'ProjectSourceRepository'
                                  and b.id = '" . $repo_id . "'");

        //print_r($result);

        if ($result) {

            $repo_details = $result->getRowAt("0");
            return $repo_details;
        } else {

            return array();
        }
    }

    /**
     * Get access levels of user
     * @param integer $repo_id
     * @return array access
     */
    function get_access_levels($repo_id = 0) {
        if (!is_numeric($repo_id) || $repo_id == 0) {
            return false;
        }

        $access_table_name = TABLE_PREFIX . 'rt_gitolite_access_master';
        $result = DB::execute("SELECT * from $access_table_name where repo_id = '" . $repo_id . "'");
        if ($result) {
            $access_array = $result->getRowAt("0");
            return $access_array;
        } else {
            return array();
        }
    }

    /**
     * Get branches under specific repository.
     * @param type $repo_path
     * @return array branches
     */
    function get_branches($repo_path = "") {
        if ($repo_path == "") {
            return FALSE;
        }
        exec("cd $repo_path && git branch --a", $output);

        if (is_array($output) && count($output) > 0) {
            return array_reverse($output);
        } else {
            return FALSE;
        }

        return FALSE;
    }

    /**
     * Get tags under specific repository.
     * @param type $repo_path
     * @return array tags
     */
    function get_tags($repo_path = "") {
        if ($repo_path == "") {
            return FALSE;
        }
        exec("cd $repo_path && git tag -l", $output);
        if (is_array($output) && count($output) > 0) {
            return array_reverse($output);
        } else {
            return FALSE;
        }

        return FALSE;
    }

    function delete_commits($repo_id) {

        $commits_table = TABLE_PREFIX . 'source_commits';
        $paths_table_name = TABLE_PREFIX . 'source_paths';
        $result = DB::execute("SELECT * from $commits_table where repository_id = '" . $repo_id . "'");
        if ($result) {
            while ($row = mysql_fetch_assoc($result->getResource())) {

                $result_delete = DB::execute("DELETE from $paths_table_name where commit_id = '" . $row['id'] . "'");
            }
        }
        $resultcommit = DB::execute("DELETE from $commits_table where repository_id = '" . $repo_id . "'");
        return true;
    }

    /**
     * Fetch actual repository name from remote URL path
     * @param string $repo_url
     * @return boolean
     */
    function get_actual_repo_name($repo_url = "") {
        if ($repo_url == "") {
            return false;
        }

        $index_colan = strpos(strrev($repo_url), ":");
        if ($index_colan) {
            $index_colan = strlen($string) - strlen($item) - $index_colan;
            $index_colan = (int) $index_colan;
        }
        $index_slash = strpos(strrev($repo_url), "/");
        if ($index_slash) {
            $index_slash = strlen($string) - strlen($item) - $index_slash;
            $index_slash = (int) $index_slash;
        }


        if ($index_colan > $index_slash) {
            $repo_array = array_reverse(explode(":", $repo_url));
        } elseif ($index_colan < $index_slash) {
            $repo_array = array_reverse(explode("/", $repo_url));
        }

        if (is_array($repo_array) && count($repo_array) > 0) {
            return $actual_git_repo_name = $repo_array[0];
        } else {
            return FALSE;
        }
    }

    /**
     * Removes directory created to clone repository, if cloning repository or adding repository gets falied
     * @param string $dir
     * @return boolean
     */
    function remove_directory($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir" && $object != "git" && $object != "gitolite")
                        self::remove_directory($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            //rename("old_$dir".time(),$dir);
            rename("$dir", $dir . "-" . time());
        }
        return true;
    }

    function chk_remote_repo($repo_fk = 0) {
        $cnt_array = false;
        if (!is_numeric($repo_fk) || $repo_fk == 0) {
            return false;
        }
        $remote_repo_table_name = TABLE_PREFIX . 'rt_remote_repos';

        $result = DB::execute("SELECT count(repo_fk) as chk_remote,remote_repo_name,repo_fk,remote_repo_id from  $remote_repo_table_name 
                               where repo_fk = '$repo_fk'");

        if ($result) {
            $cnt_array = $result->getRowAt("0");
            if (is_array($cnt_array) && count($cnt_array) > 0) {
                return $cnt_array;
            }
        } else {
            return false;
        }
    }

    function pull_branches($actual_repo_path = "") {
        if ($actual_repo_path == "") {
            return false;
        }
        $branches = $get_branches = exec("cd $actual_repo_path && git branch -a", $output);
        if (is_foreachable($output)) {
            $array_unique_banch = array();
            foreach ($output as $key => $value) {
                $branch_name = substr(strrchr($value, "/"), 1);

                if (!in_array($branch_name, $array_unique_banch)) {
                    exec("cd $actual_repo_path && git checkout -b $branch_name origin/$branch_name");
                    // && git pull
                }
                $array_unique_banch[] = $branch_name;
            }
        }
        return true;
    }

    function check_actual_name_count($actual_git_repo_name = "") {
        if ($actual_git_repo_name == "") {
            return 0;
        }
        $remote_repo_table_name = TABLE_PREFIX . 'rt_remote_repos';
        $result = DB::execute("SELECT count(repo_fk) as actual_name_cnt from  $remote_repo_table_name where actual_repo_name = '$actual_git_repo_name'");

        if ($result) {
            $cnt_array = $result->getRowAt("0");
            if (is_array($cnt_array) && count($cnt_array) > 0) {
                return $cnt_array;
            }
        }
    }

    function check_actual_name_count_gitolite($actual_git_repo_name = "") {
        if ($actual_git_repo_name == "") {
            return 0;
        }
        $remote_repo_table_name = TABLE_PREFIX . 'rt_gitolite_repomaster';
        $result = DB::execute("SELECT count(repo_fk) as actual_name_cnt from  $remote_repo_table_name where repo_name = '$actual_git_repo_name'");

        if ($result) {
            $cnt_array = $result->getRowAt("0");
            if (is_array($cnt_array) && count($cnt_array) > 0) {
                return $cnt_array;
            }
        }
    }

    function urls_exists($src_repo_id = 0) {
        if ($src_repo_id == "" || $src_repo_id == 0) {
            return false;
        }
        $web_hooks_table_name = TABLE_PREFIX . 'rt_web_hooks';
        $result = DB::execute("SELECT * from  $web_hooks_table_name where repo_fk = '$src_repo_id'");

        if ($result) {
            $web_hooks_array = $result->getRowAt("0");
            if (is_array($web_hooks_array) && count($web_hooks_array) > 0) {
                return $web_hooks_array;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Add web hooks for a specific repository
     * @param string $array_urls_str
     * @param integer $src_repo_id
     * @param integer $added_by
     * @return boolean
     */
    function insert_urls($array_urls_str = "", $src_repo_id = 0, $added_by = 0) {
        if (!is_numeric($src_repo_id) || $array_urls_str == "" || !is_numeric($added_by)) {
            return FALSE;
        }
        $web_hooks_table_name = TABLE_PREFIX . 'rt_web_hooks';
        DB::execute("INSERT INTO $web_hooks_table_name (repo_fk,webhook_urls,added_by) VALUES (? ,?, ?)", $src_repo_id, $array_urls_str, $added_by
        );
        return DB::lastInsertId();
    }
    
    /**
     * 
     * @param type $array_urls_str
     * @param type $src_repo_id
     * @param type $added_by
     * @return boolean
     */
    function update_web_hooks($array_urls_str = "", $src_repo_id = 0, $added_by = 0) {
        if (!is_numeric($src_repo_id) || $array_urls_str == "" || !is_numeric($added_by)) {
            return FALSE;
        }
        $web_hooks_table_name = TABLE_PREFIX . 'rt_web_hooks';
        $update_access = DB::execute("update  " . $web_hooks_table_name . " set  	webhook_urls = '$array_urls_str' where repo_fk = " . DB::escape($src_repo_id));
        return TRUE;
    }

    /**
     * 
     * @param type $src_repo_id
     * @return boolean
     */
    function ftp_connections_exists($src_repo_id) {

        if ($src_repo_id == "" || $src_repo_id == 0) {
            return false;
        }
        $ftp_table_name = TABLE_PREFIX . "rt_ftp_connections";
        $result = DB::execute("SELECT COUNT(repo_fk) as ftp_cnt from  $ftp_table_name where repo_fk = '$src_repo_id'");

        if ($result) {
            $ftp_details_array = $result->getRowAt("0");
            if (is_array($ftp_details_array) && count($ftp_details_array) > 0) {
                return $ftp_details_array;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Add ftp connection
     * @param type $ftp_array
     * @param type $src_repo_id
     * @param type $added_by
     * @return boolean
     */
    function add_ftp_details($ftp_array = array(), $src_repo_id = 0, $added_by = 0) {
        if (!is_numeric($src_repo_id) || count($ftp_array) == 0 || !is_numeric($added_by)) {
            return FALSE;
        }
        $ftp_table_name = TABLE_PREFIX . 'rt_ftp_connections';
        DB::execute("INSERT INTO $ftp_table_name (repo_fk,ftp_host_name,ftp_port_no,ftp_username,ftp_password,ftp_branches,ftp_dir,added_by) 
                    VALUES (? ,?, ?, ?, ?, ?, ?, ?)", $src_repo_id, $ftp_array["ftp_domain"], $ftp_array["ftp_port"], $ftp_array["ftp_username"], $ftp_array["ftp_password"], $ftp_array["branches"], $ftp_array["ftp_dir"], $added_by
        );
        return DB::lastInsertId();
    }
    
    /**
     * get ftp conection details
     * @param type $src_repo_id
     * @return boolean
     */
    function get_connection_details($src_repo_id) {
        if ($src_repo_id == "" || $src_repo_id == 0) {
            return false;
        }
        $ftp_table_name = TABLE_PREFIX . "rt_ftp_connections";
        $result = DB::execute("SELECT * from  $ftp_table_name where repo_fk = '$src_repo_id'");
        print_r($result);
        if ($result) {
            while ($row = mysql_fetch_array($result->getResource())) {
                $ftp_details_array[] = $row;
            }
            return $ftp_details_array;
        } else {
            return false;
        }
    }

    function deploy_key_exists($src_repo_id = 0) {
        if ($src_repo_id == "" || $src_repo_id == 0) {
            return false;
        }
        $deploy_key_table_name = TABLE_PREFIX . 'rt_deploy_keys';
        $result = DB::execute("SELECT * from  $deploy_key_table_name where repo_fk = '$src_repo_id'");
        $deploy_keys_array = array();
        
        if ($result) {
            while ($row = mysql_fetch_array($result->getResource())) {                
                $deploy_keys_array[] = $row;        
            }               
            if (is_array($deploy_keys_array) && count($deploy_keys_array) > 0) {                
                return $deploy_keys_array;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
     
    function add_deploy_keys($deploy_keys_name = "",$deploy_keys_key = "",$parent_key = "", $src_repo_id = 0, $added_by = 0, $pub_file_name = "") {
        if (!is_numeric($src_repo_id) || $deploy_keys_name == "" || $deploy_keys_key == "" || !is_numeric($added_by) ) {
            return FALSE;
        }        
        //echo $src_repo_id."</br>1".$deploy_keys_name."</br>2".$deploy_keys_key."<br/>3".$added_by."<br/>4".$parent_key;
        //exit;
        $add_deploy_key_table_name = TABLE_PREFIX . 'rt_deploy_keys';
        DB::execute("INSERT INTO $add_deploy_key_table_name (`repo_fk`, `name`, `keys`, `added_by`, `parent_key`, `pub_file_name`) VALUES (? ,?, ?, ?, ?, ?)", $src_repo_id, $deploy_keys_name, $deploy_keys_key, $added_by, $parent_key, $pub_file_name);        
        
        return DB::lastInsertId();
    }

    function get_parent_key($deploy_key = "") {
        if($deploy_key == "") {
            return FALSE;
        }        
        $check_deploy_kay_table_name = TABLE_PREFIX . 'rt_deploy_keys';        
        $result = DB::execute("SELECT id from $check_deploy_kay_table_name where `keys` = '$deploy_key' order by `id`");
        if($result) {
            $row = mysql_fetch_array($result->getResource());                        
            return $row['id'];
        }
        else {
            return FALSE;
        }
    }
    
    function is_parent_key($key_id ="") {
        if($key_id == "") {
            return FALSE;
        }
        $deploy_key_table_name = TABLE_PREFIX . 'rt_deploy_keys';
        $result = DB::execute("SELECT count(id) as 'count_id' from $deploy_key_table_name where `parent_key` = '$key_id'");
        if($result) {
            $row = mysql_fetch_array($result->getResource());                        
            return $row['count_id'];
        }
        else {
            return FALSE;
        }
    }
    
    function is_child_key( $key_id = "") {
        if($key_id == "") {
            return FALSE;
        }
        $deploy_key_table_name = TABLE_PREFIX . 'rt_deploy_keys';
        $result = DB::execute( " SELECT id from $deploy_key_table_name where parent_key <> '0' and id = '$key_id' " );        
        if($result) {            
            return TRUE;
        }
        else
            return FALSE;
    }
    
    function get_pub_file_name ($key_id = "") {
        if($key_id == "") {
            return FALSE;
        }        
        $deploy_key_table_name = TABLE_PREFIX . 'rt_deploy_keys';
        $result = DB::execute("SELECT pub_file_name from $deploy_key_table_name where `id` = '$key_id'");
        if($result) {
            $row = $result->getRowAt(0);
            return $row['pub_file_name'];
        }        
        return FALSE;
    }
    
    function update_child($key_id = "") {
        if($key_id == "") {
            return FALSE;            
        }
        $deploy_key_table_name = TABLE_PREFIX . 'rt_deploy_keys';
        $sql_get_first_child = DB::execute("SELECT `id` from $deploy_key_table_name where parent_key = '$key_id' order by `id` limit 0, 1");
        $row = $sql_get_first_child->getRowAt(0);
        $first_child = $row['id'];
        
        $update_all_child = DB::execute(" UPDATE $deploy_key_table_name set parent_key = '$first_child' where parent_key = '$key_id'");
        
        $update_first_child = DB::execute(" UPDATE $deploy_key_table_name set parent_key = '0' where id = '$first_child' ");
        
    }
    
    function remove_deploy_key_from_DB($key_id = "") {        
        if($key_id == "") {
            return FALSE;
        }
        $deploy_key_table_name = TABLE_PREFIX . 'rt_deploy_keys';
        $delete_parent = DB::execute(" DELETE from $deploy_key_table_name where id = '$key_id' ");
    }
    
    function get_pub_file_name_from_parent_key($parent_key = "") {
        if($parent_key == "") {
            return FALSE;
        }
        else {
            $check_deploy_kay_table_name = TABLE_PREFIX . 'rt_deploy_keys';        
            $sql_get_pub_file_name = "SELECT pub_file_name from $check_deploy_kay_table_name where id = '$parent_key'" ;
            $result = DB::execute($sql_get_pub_file_name);
            if ($result) {
                $pub_file_name = $result->getRowAt(0);
            }            
            return $pub_file_name['pub_file_name'];
        }
    }
    function check_same_repo($deploy_key = "", $repo_id = "") {
        if($deploy_key == "" || $repo_id == "") {
            return FALSE;
        }
        $check_same_repo_table_name = TABLE_PREFIX . 'rt_deploy_keys';
        $result = DB::execute("SELECT id from $check_same_repo_table_name where `keys` like '%".trim($deploy_key)."%' and `repo_fk` = '$repo_id'");
        if($result) {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
}

