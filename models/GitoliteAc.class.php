<?php

/**
 * GitoliteAc class
 *
 * @package    custom.modules.ac_gitolite
 * @subpackage models
 * @author     rtCamp Software Solutions Pvt Ltd <admin@rtcamp.com>
 * @author     Rahul Bansal <rahul.bansal@rtcamp.com>
 * @author     Kasim Badami <kasim.badami@rtcamp.com>
 *
 */
class GitoliteAc {

	/**
	 * Used to fetch all public keys under a user.
	 *
	 * @param integer $active_user
	 *
	 * @return array
	 */
	function fetch_keys( $active_user = 0 ) {
		if ( ! is_numeric( $active_user ) ) {

			return array();
		}

		$keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
		$result          = DB::execute( "SELECT * from " . $keys_table_name . " where user_id = '" . $active_user . "' and is_deleted  = '0'" );

		if ( is_foreachable( $result ) ) {
			foreach ( $result as $payments ) {

				$results[ ] = array(
					'key_name' => $payments[ 'key_name' ], 'public_key' => $payments[ 'public_key' ], 'key_id' => $payments[ 'key_id' ]
				);
			} // foreach


		} // if
		else {
			$results = array();
		}

		return $results;

	}

	/**
	 * Check whether added key already exists
	 *
	 * @param integer $active_user
	 * @param array   $post_data
	 *
	 * @return array
	 */
	function check_duplication( $active_user = 0, $post_data = array(), $actual_key = "" ) {
		if ( ! is_numeric( $active_user ) || count( $post_data ) == 0 ) {
			return array();
		}
		$keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';


		$result = DB::execute( "SELECT COUNT(user_id) as dup_name_cnt from " . $keys_table_name . "
                                where  key_name = '" . $post_data[ 'key_name' ] . "' and is_deleted = '0'
                                UNION
                                SELECT COUNT(user_id) as dup_key_cnt from " . $keys_table_name . "
                                where  public_key LIKE '%" . trim( $actual_key ) . "%' and is_deleted = '0'" );
		if ( $result ) {
			$dup_key_name[ ] = $result[0];
			$dup_key_name[ ] = $result[1];

		}

		return $dup_key_name;

	}

	/**
	 * Check key already exists while mapping
	 *
	 * @param string $pub_key
	 *
	 * @return boolean
	 */

	function check_key_map_exists( $pub_key = "" ) {
		$key_cnt_res = false;
		if ( $pub_key == "" ) {
			return $key_cnt;
		}
		$keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';

		$result = DB::executeFirstRow( "SELECT user_id,key_name FROM " . $keys_table_name . "
                              WHERE  public_key LIKE '%" . $pub_key . "%' and is_deleted = '0'" );
		/*echo "SELECT COUNT(user_id) as dup_name_cnt FROM ".$keys_table_name."
					 WHERE  key_name = '".$pub_key."' and is_deleted = '0'";*/
		/*echo "SELECT user_id,key_name FROM ".$keys_table_name."
							  WHERE  public_key LIKE '%".$pub_key."%' and is_deleted = '0'";*/
		if ( $key_cnt_res ) {
			//print_r($key_cnt_res);
			return $key_cnt_res;
		}

		return $key_cnt_res;
	}


	/**
	 * Add public keys.
	 *
	 * @param integer $active_user
	 * @param string  $pub_file_name
	 * @param array   $post_data
	 *
	 * @return boolean
	 */
	function add_keys( $active_user = 0, $pub_file_name = "", $post_data = array() ) {
		if ( count( $post_data ) == 0 || ! is_numeric( $active_user ) || $pub_file_name == "" ) {
			return false;
		}

		$keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';


		DB::execute( "INSERT INTO $keys_table_name (user_id, key_name,pub_file_name, public_key) VALUES (?, ?, ?, ?)", $active_user, trim( $post_data[ 'key_name' ] ), $pub_file_name, trim( $post_data[ 'public_keys' ] ) );

		return DB::lastInsertId();
	}

	/**
	 * Remove keys of user.
	 *
	 * @param integer $key_id
	 *
	 * @return boolean
	 */
	function remove_keys( $key_id = 0 ) {
		$keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
		if ( $key_id == 0 || $key_id == "" ) {
			return false;
		} else {

			$remove = DB::execute( "update  " . $keys_table_name . " set is_deleted = '1' where key_id = " . DB::escape( $key_id ) );
			if ( $remove ) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * get_filename
	 * Get public file name
	 *
	 * @param integer $key_id
	 *
	 * @return string file name
	 */
	function get_filename( $key_id = 0 ) {
		$keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
		if ( $key_id == 0 || $key_id == "" ) {
			return false;
		}
		$get_keyname = DB::executeFirstRow( "SELECT pub_file_name FROM " . $keys_table_name . " WHERE key_id = '" . $key_id . "'" );
		if ( $get_keyname ) {
			return $get_keyname[ 'pub_file_name' ];
		} else {
			return array();
		}

	}

	/**
	 * check_keys_added
	 * Check whether keys are added for particular user
	 *
	 * @param integer $user_id
	 *
	 * @return boolean
	 */
	function check_keys_added( $user_id = 0 ) {
		if ( $user_id == 0 || $user_id == "" ) {
			return false;
		}
		$keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
		$get_key_count          = DB::executeFirstRow( "SELECT COUNT(user_id) as key_count from " . $keys_table_name . " where user_id = '" . $user_id . "' and is_deleted  = '0'" );
		if ( $get_key_count ) {
			return $get_key_count[ 'key_count' ];
		} else {
			return false;
		}
	}

	function get_key_details( $key_name = "" ) {

		if ( $key_name == "" ) {
			return false;
		}
		$keys_table_name = TABLE_PREFIX . 'rt_gitolite_user_public_keys';
		$get_key          = DB::executeFirstRow( "SELECT * from " . $keys_table_name . " where key_name = '" . $key_name . "' and is_deleted  = '0'" );
		if ( $get_key ) {
			return $get_key;

		} else {
			return false;
		}
	}
}
 