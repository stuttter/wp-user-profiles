<?php
/**
 * Database test double for user status changes.
 *
 * @package WP_User_Profiles
 */

/**
 * Record status changes without writing to a database.
 */
class WPUP_Test_Status_DB {
	/**
	 * User table name.
	 *
	 * @var string
	 */
	public $users = 'users';

	/**
	 * Recorded updates.
	 *
	 * @var array
	 */
	public $updates = array();

	/**
	 * Record a user-table update.
	 *
	 * @param string $table Table name.
	 * @param array  $data  New values.
	 * @param array  $where Row selector.
	 * @return int
	 */
	public function update( $table, $data, $where ) {
		$this->updates[] = array( $table, $data, $where );
		return 1;
	}
}
