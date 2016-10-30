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
	$languages   = get_available_languages();
	$user_locale = $user->locale;
	$fallback    = get_locale();

	// Already en_US
	if ( 'en_US' === $user->locale ) { 
		$user_locale = false;

	// Language not available
	} elseif ( ! in_array( $user->locale, $languages, true ) ) {
		$user_locale = $fallback;
	}

	?>

	<table class="form-table">
		<tr class="user-language-wrap">
			<th scope="row">
				<label for="locale"><?php esc_html_e( 'Language', 'wp-user-profiles' ); ?></label>
			</th>
			<td><?php

				// Drop it down
				wp_dropdown_languages( array(
					'name'                        => 'locale',
					'id'                          => 'locale',
					'selected'                    => $user_locale,
					'languages'                   => $languages,
					'show_available_translations' => false
				) );

				?>
			</td>
		</tr>
	</table>

	<?php
}
