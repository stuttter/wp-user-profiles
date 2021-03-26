<?php

/**
 * User Profile Language Metabox
 *
 * @package Plugins/Users/Profiles/Metaboxes/Laguage
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render the language metabox for user profile screen
 *
 * @since 0.1.0
 *
 * @param WP_User $user The WP_User object to be edited.
 */
function wp_user_profiles_language_metabox( $user = null ) {

	// Defaults
	$languages = get_available_languages();
	$user_locale = $user->locale;
	if ( 'en_US' === $user_locale ) {
		$user_locale = '';
	} elseif ( '' === $user_locale || ! in_array( $user_locale, $languages, true ) ) {
		$user_locale = 'site-default';
	}

	// Before
	do_action( __FUNCTION__ . '_before', $user ); ?>

	<table class="form-table">
		<tr class="user-language-wrap">
			<th scope="row">
				<label for="locale">
					<?php esc_html_e( 'Language', 'wp-user-profiles' ); ?>
					<span class="dashicons dashicons-translation" aria-hidden="true"></span>
				</label>
			</th>
			<td><?php

				// Drop it down
				wp_dropdown_languages(
					array(
						'name'                        => 'locale',
						'id'                          => 'locale',
						'selected'                    => $user_locale,
						'languages'                   => $languages,
						'show_available_translations' => false,
						'show_option_site_default'    => true,
					)
				);

				?>
			</td>
		</tr>
	</table>

	<?php

	// After
	do_action( __FUNCTION__ . '_after', $user );
}
