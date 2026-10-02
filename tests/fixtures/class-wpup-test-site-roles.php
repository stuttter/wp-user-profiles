<?php
/**
 * Role registry for Sites save tests.
 *
 * @package WP_User_Profiles
 */

/**
 * Report registered roles for the current test site.
 */
class WPUP_Test_Site_Roles {
	/**
	 * Check whether a role is registered.
	 *
	 * @param string $role Role slug.
	 * @return bool Whether the role exists.
	 */
	public function is_role( $role ): bool {
		return isset( $GLOBALS['wpup_test']['all_roles'][ $role ] )
			|| isset( $GLOBALS['wpup_test']['editable_roles'][ $role ] );
	}
}
