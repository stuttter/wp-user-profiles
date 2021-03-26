<?php

/**
 * User Profile Session Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Sessions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the password metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_application_metabox( $user = null ) {

	// Get the List Table
	$application_passwords_list_table = _get_list_table( 'WP_Application_Passwords_List_Table', array(
		'screen' => 'application-passwords-user'
	) );

	// Prepare the Application Passwords
	$application_passwords_list_table->prepare_items();

	// Get the submit button
	$button = get_submit_button(
		esc_html__( 'Add Password', 'wp-user-profiles' ),
		'secondary',
		'do_new_application_password',
		false
	);

	// Get the password field
	$password = sprintf(
		/* translators: %s: Application name. */
		esc_html__( 'The password for %s is:', 'wp-user-profiles' ),
		'<strong>{{ data.name }}</strong>'
	);

	// Before
	do_action( __FUNCTION__ . '_before', $user );

	// Output
	?><div class="application-passwords hide-if-no-js" id="application-passwords-section">
			<div class="create-application-password form-wrap">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="new_application_password_name"><?php esc_html_e( 'Password Name', 'wp-user-profiles' ); ?></label>
					</th>
					<td>
						<input type="text" size="30" id="new_application_password_name" name="new_application_password_name" placeholder="<?php esc_attr_e( 'Name of App or Service', 'wp-user-profiles' ); ?>" class="input" aria-required="true" aria-describedby="<?php esc_attr_e( 'Required to create an Application Password.', 'wp-user-profiles' ); ?>" />

						<?php

						// Output the button
						echo $button;

						// This action documented in wp-admin/user-edit.php
						do_action( 'wp_create_application_password_form', $user );

					?></td>
				</tr>
			</table>
		</div>

		<div class="application-passwords-list-table-wrapper"><?php

			// Display the List Table
			$application_passwords_list_table->display();

		?></div>
	</div>

	<script type="text/html" id="tmpl-new-application-password">
		<div class="notice notice-success is-dismissible new-application-password-notice" role="alert" tabindex="-1">
			<p class="application-password-display">
				<label for="new-application-password-value"><?php

					// Output the password
					echo $password;

				?></label>

				<input id="new-application-password-value" type="text" class="code" readonly="readonly" value="{{ data.password }}" />
			</p>

			<p><?php

				esc_html_e( 'Save this in a safe location. You cannot retrieve it again later.', 'wp-user-profiles' );

			?></p>

			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php

					esc_html_e( 'Dismiss this notice.', 'wp-user-profiles' );

				?></span>
			</button>
		</div>
	</script>

	<script type="text/html" id="tmpl-application-password-row"><?php

		// Output the template row
		$application_passwords_list_table->print_js_template_row();

	?></script><?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
