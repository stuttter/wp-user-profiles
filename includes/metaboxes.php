<?php

/**
 * Add the default user profile metaboxes
 *
 * @since 0.1.0
 *
 * @param   string  $type
 * @param   mixed   $user
 */
function wp_user_profiles_add_profile_meta_boxes( $type = '' ) {

	// Bail if not user metaboxes
	if ( 'admin_page_profile' !== $type ) {
		return;
	}

	// Register metaboxes for the user edit screen
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$type,
		'side',
		'core'
	);

	// Name
	add_meta_box(
		'name',
		_x( 'Name', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_name_metabox',
		$type,
		'normal',
		'core'
	);

	// About
	add_meta_box(
		'about',
		_x( 'About', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_about_metabox',
		$type,
		'normal',
		'core'
	);

	// Contact
	add_meta_box(
		'contact',
		_x( 'Contact', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_contact_metabox',
		$type,
		'normal',
		'core'
	);
}

/**
 * Add the default user profile metaboxes
 *
 * @since 0.1.0
 *
 * @param   string  $type
 * @param   mixed   $user
 */
function wp_user_profiles_add_account_meta_boxes( $type = '' ) {

	// Bail if not user metaboxes
	if ( 'admin_page_account' !== $type ) {
		return;
	}

	// Status
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$type,
		'side',
		'core'
	);

	// Password
	add_meta_box(
		'email',
		_x( 'Email', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_email_metabox',
		$type,
		'normal',
		'core'
	);

	// Password
	add_meta_box(
		'password',
		_x( 'Password', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_password_metabox',
		$type,
		'normal',
		'core'
	);

	// Sessions
	add_meta_box(
		'sessions',
		_x( 'Sessions', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_session_metabox',
		$type,
		'normal',
		'core'
	);
}

/**
 * Add the default user profile metaboxes
 *
 * @since 0.1.0
 *
 * @param   string  $type
 * @param   mixed   $user
 */
function wp_user_profiles_add_options_meta_boxes( $type = '' ) {

	// Bail if not user metaboxes
	if ( 'admin_page_options' !== $type ) {
		return;
	}

	// Always register the status box
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$type,
		'side',
		'core'
	);

	// Color schemes
	add_meta_box(
		'colors',
		_x( 'Color Scheme', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_color_scheme_metabox',
		$type,
		'normal',
		'core'
	);

	// Color schemes
	add_meta_box(
		'options',
		_x( 'Personal Options', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_personal_options_metabox',
		$type,
		'normal',
		'core'
	);
}

/**
 * Add the default user profile metaboxes
 *
 * @since 0.1.0
 *
 * @param   string  $type
 * @param   mixed   $user
 */
function wp_user_profiles_add_roles_meta_boxes( $type = '' ) {

	// Bail if not user metaboxes
	if ( 'admin_page_roles' !== $type ) {
		return;
	}

	// Always register the status box
	add_meta_box(
		'submitdiv',
		_x( 'Status', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_status_metabox',
		$type,
		'side',
		'core'
	);

	// Color schemes
	add_meta_box(
		'roles',
		_x( 'Roles', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_roles_metabox',
		$type,
		'normal',
		'core'
	);

	// Color schemes
	add_meta_box(
		'options',
		_x( 'Additional Capabilities', 'users user-admin edit screen', 'wp-user-profiles' ),
		'wp_user_profiles_additional_capabilities_metabox',
		$type,
		'normal',
		'core'
	);
}

/**
 * Render the primary metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_status_metabox( $user = null ) {

	// Bail if no user id or if the user has not activated their account yet
	if ( empty( $user->ID ) ) {
		return;
	}

	// Bail if user has not been activated yet (how did you get here?)
	if ( isset( $user->user_status ) && ( 2 == $user->user_status ) ) : ?>

		<p class="not-activated"><?php esc_html_e( 'User account has not yet been activated', 'wp-user-profiles' ); ?></p>

		<?php return;

	endif; ?>

	<div class="submitbox">
		<div id="minor-publishing">
			<div id="misc-publishing-actions">
				<?php

				// Get the spam status once here to compare against below
				if ( IS_PROFILE_PAGE && ( ! in_array( $user->user_login, get_super_admins() ) ) ) : ?>

					<div class="misc-pub-section" id="comment-status-radio">
						<label class="approved"><input type="radio" name="user_status" value="ham" <?php checked( $user->user_status, 2 ); ?>><?php esc_html_e( 'Active', 'wp-user-profiles' ); ?></label><br>
						<label class="spam"><input type="radio" name="user_status" value="spam" <?php checked( $user->user_status, 0 ); ?>><?php esc_html_e( 'Spammer', 'wp-user-profiles' ); ?></label>
					</div>

				<?php endif ;?>

				<div class="misc-pub-section curtime misc-pub-section-last">
					<?php

					// translators: Publish box date format, see http://php.net/date
					$datef = __( 'M j, Y @ G:i', 'wp-user-profiles' );
					$date  = date_i18n( $datef, strtotime( $user->user_registered ) ); ?>

					<span id="timestamp"><?php printf( __( 'Registered on: <strong>%1$s</strong>', 'wp-user-profiles' ), $date ); ?></span>
				</div>
			</div>

			<div class="clear"></div>
		</div>

		<div id="major-publishing-actions">
			<div id="publishing-action">
				<a class="button" href="<?php echo esc_url(); ?>" target="_blank"><?php esc_html_e( 'View User', 'wp-user-profiles' ); ?></a>
				<?php submit_button( esc_html__( 'Update', 'wp-user-profiles' ), 'primary', 'save', false ); ?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user->ID ); ?>" />
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<?php
}

/**
 * Render the personal options metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_color_scheme_metabox( $user = null ) {

	// Bail if no color schemes
	if ( ! count( $GLOBALS['_wp_admin_css_colors'] ) || ! has_action( 'admin_color_scheme_picker' ) ) {
		return;
	} ?>

	<table class="form-table">
		<tr class="user-admin-color-wrap">
			<th scope="row"><?php esc_html_e( 'Admin Color Scheme', 'wp-user-profiles' ); ?></th>
			<td><?php
			/**
			 * Fires in the 'Admin Color Scheme' section of the user editing screen.
			 *
			 * The section is only enabled if a callback is hooked to the action,
			 * and if there is more than one defined color scheme for the admin.
			 *
			 * @since 3.0.0
			 * @since 3.8.1 Added `$user_id` parameter.
			 *
			 * @param int $user_id The user ID.
			 */
			do_action( 'admin_color_scheme_picker', $user->ID );
			?></td>
		</tr>
	</table>

	<?php
}

/**
 * Render the personal options metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_personal_options_metabox( $user = null ) {
?>

	<table class="form-table">

		<?php if ( ! ( IS_PROFILE_PAGE && ! ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) ) ) : ?>

			<tr class="user-rich-editing-wrap">
				<th scope="row"><?php esc_html_e( 'Visual Editor', 'wp-user-profiles' ); ?></th>
				<td>
					<label for="rich_editing"><input name="rich_editing" type="checkbox" id="rich_editing" value="false" <?php checked( 'false', $user->rich_editing ); ?> />
						<?php esc_html_e( 'Disable the visual editor when writing', 'wp-user-profiles' ); ?>
					</label>
				</td>
			</tr>
			<tr class="user-comment-shortcuts-wrap">
				<th scope="row"><?php esc_html_e( 'Keyboard Shortcuts', 'wp-user-profiles' ); ?></th>
				<td>
					<label for="comment_shortcuts"><input type="checkbox" name="comment_shortcuts" id="comment_shortcuts" value="true" <?php checked( 'true', $user->comment_shortcuts ); ?> />
						<?php esc_html_e( 'Enable keyboard shortcuts for comment moderation.', 'wp-user-profiles' ); ?>
					</label>
				</td>
			</tr>

		<?php endif; ?>

		<tr class="show-admin-bar user-admin-bar-front-wrap">
			<th scope="row"><?php esc_html_e( 'Toolbar', 'wp-user-profiles' ); ?></th>
			<td>
				<fieldset>
					<legend class="screen-reader-text"><span><?php esc_html_e( 'Toolbar', 'wp-user-profiles' ) ?></span></legend>
					<label for="admin_bar_front">
						<input name="admin_bar_front" type="checkbox" id="admin_bar_front" value="1" <?php checked( _get_admin_bar_pref( 'front', $user->ID ) ); ?> />
						<?php esc_html_e( 'Show Toolbar when viewing site', 'wp-user-profiles' ); ?>
					</label>
				</fieldset>
			</td>
		</tr>
		<?php
		/**
		 * Fires at the end of the 'Personal Options' settings table on the user editing screen.
		 *
		 * @since 2.7.0
		 *
		 * @param WP_User $user The current WP_User object.
		 */
		do_action( 'personal_options', $user );
		?>

	</table>

	<?php
}

/**
 * Render the personal options metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_name_metabox( $user = null ) {

	if ( IS_PROFILE_PAGE ) {
		/**
		 * Fires after the 'Personal Options' settings table on the 'Your Profile' editing screen.
		 *
		 * The action only fires if the current user is editing their own profile.
		 *
		 * @since 2.0.0
		 *
		 * @param WP_User $user The current WP_User object.
		 */
		do_action( 'profile_personal_options', $user );
	} ?>

	<table class="form-table">
		<tr class="user-user-login-wrap">
			<th><label for="user_login"><?php esc_html_e( 'Username', 'wp-user-profiles' ); ?></label></th>
			<td>
				<cite title="<?php esc_html_e( 'Usernames cannot be changed.', 'wp-user-profiles' ); ?>"><?php echo esc_attr( $user->user_login ); ?></cite>
				<input type="hidden" name="user_login" id="user_login" value="<?php echo esc_attr($user->user_login); ?>" disabled="disabled" class="regular-text" />
			</td>
		</tr>

		<tr class="user-first-name-wrap">
			<th><label for="first_name"><?php _e('First Name') ?></label></th>
			<td><input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $user->first_name ); ?>" class="regular-text" /></td>
		</tr>

		<tr class="user-last-name-wrap">
			<th><label for="last_name"><?php esc_html_e( 'Last Name', 'wp-user-profiles' ) ?></label></th>
			<td><input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $user->last_name ); ?>" class="regular-text" /></td>
		</tr>

		<tr class="user-nickname-wrap">
			<th><label for="nickname"><?php esc_html_e( 'Nickname', 'wp-user-profiles' ); ?>
					<span class="description"><?php esc_html_e( '(required)', 'wp-use-profiles' ); ?></span>
				</label>
			</th>
			<td><input type="text" name="nickname" id="nickname" value="<?php echo esc_attr($user->nickname) ?>" class="regular-text" /></td>
		</tr>

		<tr class="user-display-name-wrap">
			<th><label for="display_name"><?php esc_html_e( 'Display name publicly as', 'wp-user-profiles' ); ?></label></th>
			<td>
				<select name="display_name" id="display_name">
				<?php
					$public_display = array();

					// Nick & User
					$public_display['display_nickname']  = $user->nickname;
					$public_display['display_username']  = $user->user_login;

					// First
					if ( ! empty( $user->first_name ) ) {
						$public_display['display_firstname'] = $user->first_name;
					}

					// Last
					if ( ! empty( $user->last_name ) ) {
						$public_display['display_lastname'] = $user->last_name;
					}

					// Combined
					if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
						$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
						$public_display['display_lastfirst'] = $user->last_name  . ' ' . $user->first_name;
					}

					// Display
					if ( ! in_array( $user->display_name, $public_display ) ) {
						$public_display = array( 'display_displayname' => $user->display_name ) + $public_display;
					}

					// Trim & tidy
					$public_display = array_map( 'trim', $public_display );
					$public_display = array_unique( $public_display );

					// Show options
					foreach ( $public_display as $item ) : ?>

						<option <?php selected( $user->display_name, $item ); ?>><?php echo esc_html( $item ); ?></option>

					<?php endforeach; ?>

				</select>
			</td>
		</tr>
	</table>

	<?php
}

/**
 * Render the contact metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_email_metabox( $user = null ) {
	$current_user = wp_get_current_user(); ?>

	<table class="form-table">
		<tr class="user-email-wrap">
			<th><label for="email"><?php _e('Email'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
			<td><input type="email" name="email" id="email" value="<?php echo esc_attr( $user->user_email ) ?>" class="regular-text ltr" />
			<?php
			$new_email = get_option( $current_user->ID . '_new_email' );
			if ( $new_email && $new_email['newemail'] != $current_user->user_email && $user->ID == $current_user->ID ) : ?>
			<div class="updated inline">
			<p><?php
				printf(
					__( 'There is a pending change of your email to %1$s. <a href="%2$s">Cancel</a>' ),
					'<code>' . $new_email['newemail'] . '</code>',
					esc_url( self_admin_url( 'profile.php?dismiss=' . $current_user->ID . '_new_email' ) )
			); ?></p>
			</div>
			<?php endif; ?>
			</td>
		</tr>
	</table>

	<?php
}

/**
 * Render the contact metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_contact_metabox( $user = null ) {

	// Get methods
	$methods = wp_get_user_contact_methods( $user ); ?>

	<table class="form-table">

		<?php foreach ( $methods as $name => $desc ) : ?>

			<tr class="user-<?php echo esc_attr( $name ); ?>-wrap">
				<th>
					<label for="<?php echo esc_attr( $name ); ?>">
						<?php
						/**
						 * Filter a user contactmethod label.
						 *
						 * The dynamic portion of the filter hook, `$name`, refers to
						 * each of the keys in the contactmethods array.
						 *
						 * @since 2.9.0
						 *
						 * @param string $desc The translatable label for the contactmethod.
						 */
						echo apply_filters( "user_{$name}_label", $desc ); ?>
					</label>
				</th>
				<td><input type="text" name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $user->$name ); ?>" class="regular-text" /></td>
			</tr>

		<?php endforeach; ?>

	</table>

	<?php
}

/**
 * Render the about metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_about_metabox( $user = null ) {
?>

	<table class="form-table">
		<tr class="user-url-wrap">
			<th><label for="url"><?php _e('Website') ?></label></th>
			<td><input type="url" name="url" id="url" value="<?php echo esc_attr( $user->user_url ) ?>" class="regular-text code" /></td>
		</tr>
		<tr class="user-description-wrap">
			<th><label for="description"><?php _e('Biographical Info'); ?></label></th>
			<td>
				<textarea name="description" id="description" rows="5" cols="30"><?php echo $user->description; // textarea_escaped ?></textarea>
				<p class="description">
					<?php _e('Share a little biographical information to fill out your profile. This may be shown publicly.'); ?></p>
			</td>
		</tr>
	</table>

	<?php
}


/**
 * Render the password metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_session_metabox( $user = null ) {

	// Get session
	$sessions = WP_Session_Tokens::get_instance( $user->ID ); ?>

	<table class="form-table">

		<?php if ( IS_PROFILE_PAGE && count( $sessions->get_all() ) === 1 ) : ?>

			<tr class="user-sessions-wrap hide-if-no-js">
				<th><?php _e( 'Sessions' ); ?></th>
				<td aria-live="assertive">
					<div class="destroy-sessions"><button type="button" disabled class="button button-secondary"><?php _e( 'Log Out Everywhere Else' ); ?></button></div>
					<p class="description">
						<?php _e( 'You are only logged in at this location.' ); ?>
					</p>
				</td>
			</tr>

		<?php elseif ( IS_PROFILE_PAGE && count( $sessions->get_all() ) > 1 ) : ?>

			<tr class="user-sessions-wrap hide-if-no-js">
				<th><?php _e( 'Sessions' ); ?></th>
				<td aria-live="assertive">
					<div class="destroy-sessions"><button type="button" class="button button-secondary" id="destroy-sessions"><?php _e( 'Log Out Everywhere Else' ); ?></button></div>
					<p class="description">
						<?php _e( 'Did you lose your phone or leave your account logged in at a public computer? You can log out everywhere else, and stay logged in here.' ); ?>
					</p>
				</td>
			</tr>

		<?php elseif ( ! IS_PROFILE_PAGE && $sessions->get_all() ) : ?>

			<tr class="user-sessions-wrap hide-if-no-js">
				<th><?php _e( 'Sessions' ); ?></th>
				<td>
					<p><button type="button" class="button button-secondary" id="destroy-sessions"><?php _e( 'Log Out Everywhere' ); ?></button></p>
					<p class="description">
						<?php
						/* translators: 1: User's display name. */
						printf( __( 'Log %s out of all locations.' ), $user->display_name );
						?>
					</p>
				</td>
			</tr>

		<?php endif; ?>

	</table>

<?php
}


/**
 * Render the capabilities metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_roles_metabox( $user = null ) {

	// Get the roles global
	$sites = get_blogs_of_user( $user->ID, true ); ?>

	<table class="form-table">

		<?php foreach ( $sites as $site_id => $site ) :

			// Switch to this site
			if ( is_multisite() ) {
				switch_to_blog( $site_id );
			} ?>

			<tr class="user-role-wrap">
				<th>
					<label for="role[<?php echo $site_id; ?>]">
						<?php echo $site->blogname; ?><br>
						<span class="description"><?php echo $site->siteurl; ?></span>
					</label>
				</th>
				<td><select name="role[<?php echo $site_id; ?>]" id="role[<?php echo $site_id; ?>]" <?php disabled( ! IS_PROFILE_PAGE && ! is_network_admin(), false ); ?>>
						<?php

						// Compare user role against currently editable roles
						$user_roles = array_intersect( array_values( $user->roles ), array_keys( get_editable_roles() ) );
						$user_role  = reset( $user_roles );

						// Print the full list of roles
						wp_dropdown_roles( $user_role );

						// print the 'no role' option. Make it selected if the user has no role yet.
						if ( $user_role ) : ?>

							<option value=""><?php esc_html_e( '&mdash; No role for this site &mdash;', 'wp-user-profiles' ); ?></option>

						<?php else : ?>

							<option value="" selected="selected"><?php esc_html_e( '&mdash; No role for this site &mdash;', 'wp-user-profiles' ); ?></option>

						<?php endif; ?>

					</select>
				</td>
			</tr>

		<?php

		// Switch back to this site
		if ( is_multisite() ) {
			restore_current_blog();
		}

		endforeach; ?>

	</table>

<?php
}

/**
 * Render the capabilities metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_additional_capabilities_metabox( $user = null ) {

	// Get the roles global
	$wp_roles = new WP_Roles();?>

	<table class="form-table">

		<?php if ( is_multisite() && is_network_admin() && ! IS_PROFILE_PAGE && current_user_can( 'manage_network_options' ) && ! isset( $GLOBALS['super_admins'] ) ) : ?>

			<tr class="user-super-admin-wrap"><th><?php esc_html_e( 'Super Admin', 'wp-user-profiles' ); ?></th>
				<td>

					<?php if ( $user->user_email !== get_site_option( 'admin_email' ) || ! is_super_admin( $user->ID ) ) : ?>

						<p>
							<label>
								<input type="checkbox" id="super_admin" name="super_admin"<?php checked( is_super_admin( $user->ID ) ); ?> /> <?php esc_html_e( 'Grant this user super admin privileges for the Network.', 'wp-user-profiles' ); ?>
							</label>
						</p>

					<?php else : ?>

						<p><?php esc_html_e( 'Super admin privileges cannot be removed because this user has the network admin email.', 'wp-user-profiles' ); ?></p>

					<?php endif; ?>

				</td>
			</tr>

		<?php endif; ?>

		<tr class="user-capabilities-wrap">
			<th scope="row"><?php esc_html_e( 'Capabilities', 'wp-user-profiles' ); ?></th>
			<td>
				<?php
					$output = '';
					foreach ( $user->caps as $cap => $value ) {
						if ( ! $wp_roles->is_role( $cap ) ) {
							if ( '' !== $output ) {
								$output .= ', ';
							}

							$output .= $value
								? sprintf( esc_html__( 'Allowed: %s', 'wp-user-profiles' ), $cap )
								: sprintf( esc_html__( 'Denied: %s',  'wp-user-profiles' ), $cap );
						}
					}

					if ( ! empty( $output ) ) {
						echo $output;
					} else {
						esc_html_e( 'No additional capabilities', 'wp-user-profiles' );
					}
				?>
			</td>
		</tr>
	</table>

<?php
}

/**
 * Render the password metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_password_metabox() {
?>

	<table class="form-table">
		<tr id="password" class="user-pass1-wrap">
			<th><label for="pass1"><?php esc_html_e( 'New Password', 'wp-user-profiles' ); ?></label></th>
			<td>
				<input class="hidden" value=" " /><!-- #24364 workaround -->
				<button type="button" class="button button-secondary wp-generate-pw hide-if-no-js"><?php esc_html_e( 'Generate Password', 'wp-user-profiles' ); ?></button>
				<div class="wp-pwd hide-if-js">
					<span class="password-input-wrapper">
						<input type="password" name="pass1" id="pass1" class="regular-text" value="" autocomplete="off" data-pw="<?php echo esc_attr( wp_generate_password( 24 ) ); ?>" aria-describedby="pass-strength-result" />
					</span>
					<button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password' ); ?>">
						<span class="dashicons dashicons-hidden"></span>
						<span class="text"><?php esc_html_e( 'Hide', 'wp-user-profiles' ); ?></span>
					</button>
					<button type="button" class="button button-secondary wp-cancel-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Cancel password change' ); ?>">
						<span class="text"><?php esc_html_e( 'Cancel', 'wp-user-profiles' ); ?></span>
					</button>
					<div style="display:none" id="pass-strength-result" aria-live="polite"></div>
				</div>
			</td>
		</tr>
		<tr class="user-pass2-wrap hide-if-js">
			<th scope="row"><label for="pass2"><?php esc_html_e( 'Repeat New Password', 'wp-user-profiles' ); ?></label></th>
			<td>
				<input name="pass2" type="password" id="pass2" class="regular-text" value="" autocomplete="off" />
				<p class="description"><?php esc_html_e( 'Type your new password again.', 'wp-user-profiles' ); ?></p>
			</td>
		</tr>
		<tr class="pw-weak">
			<th><?php esc_html_e( 'Confirm Password', 'wp-user-profiles' ); ?></th>
			<td>
				<label>
					<input type="checkbox" name="pw_weak" class="pw-checkbox" />
					<?php esc_html_e( 'Confirm use of weak password', 'wp-user-profiles' ); ?>
				</label>
			</td>
		</tr>
	</table>

<?php
}
