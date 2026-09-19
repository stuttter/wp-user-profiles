=== WP User Profiles ===
Author:            Triple J Software, Inc.
Author URI:        https://jjj.software
Plugin URI:        https://wordpress.org/plugins/wp-user-profiles/
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
License:           GPLv2 or later
Contributors:      johnjamesjacoby, baden03
Tags:              user, profile, edit, metabox
Requires PHP:      7.4
Requires at least: 6.4
Tested up to:      7.1
Stable tag:        2.7.3

A sophisticated way to edit users in WordPress.

== Description ==

WP User Profiles is a sophisticated way to edit users in WordPress.

* Includes all functionality from WordPress itself
* Includes 4 top-level "Sections"
* Includes an "Other" section to automatically work with third-party plugins
* Each section includes 1 or more meta-boxes
* Status meta-box allows easily changing user status
* Works great with multisite Network and User Dashboards
* Works great with WP User Groups and WP User Avatars plugins

= Recommended Plugins =

If you like this plugin, you'll probably like these!

* [WP User Profiles](https://wordpress.org/plugins/wp-user-profiles/ "A sophisticated way to edit users in WordPress.")
* [WP User Activity](https://wordpress.org/plugins/wp-user-activity/ "The best way to log activity in WordPress.")
* [WP User Avatars](https://wordpress.org/plugins/wp-user-avatars/ "Allow users to upload avatars or choose them from your media library.")
* [WP User Groups](https://wordpress.org/plugins/wp-user-groups/ "Group users together with taxonomies & terms.")
* [WP User Signups](https://wordpress.org/plugins/wp-user-signups/ "The best way to manage user & site sign-ups in WordPress.")
* [WP Term Authors](https://wordpress.org/plugins/wp-term-authors/ "Authors for categories, tags, and other taxonomy terms.")
* [WP Term Colors](https://wordpress.org/plugins/wp-term-colors/ "Pretty colors for categories, tags, and other taxonomy terms.")
* [WP Term Families](https://wordpress.org/plugins/wp-term-families/ "Associate taxonomy terms with other taxonomy terms.")
* [WP Term Icons](https://wordpress.org/plugins/wp-term-icons/ "Pretty icons for categories, tags, and other taxonomy terms.")
* [WP Term Images](https://wordpress.org/plugins/wp-term-images/ "Pretty images for categories, tags, and other taxonomy terms.")
* [WP Term Locks](https://wordpress.org/plugins/wp-term-locks/ "Protect categories, tags, and other taxonomy terms from being edited or deleted.")
* [WP Term Order](https://wordpress.org/plugins/wp-term-order/ "Sort taxonomy terms, your way.")
* [WP Term Visibility](https://wordpress.org/plugins/wp-term-visibility/ "Visibilities for categories, tags, and other taxonomy terms.")
* [WP Media Categories](https://wordpress.org/plugins/wp-media-categories/ "Add categories to media & attachments.")
* [WP Pretty Filters](https://wordpress.org/plugins/wp-pretty-filters/ "Makes post filters better match what's already in Media & Attachments.")
* [WP Chosen](https://wordpress.org/plugins/wp-chosen/ "Make long, unwieldy select boxes much more user-friendly.")

== Screenshots ==

1. Profile
2. Account
3. Options
4. Permissions

== Installation ==

* Download and install using the built in WordPress plugin installer.
* Activate in the "Plugins" area of your admin by clicking the "Activate" link.
* No further setup or configuration is necessary.

== Frequently Asked Questions ==

= Does this work with multisite? =

Yes. It works awesome with the User Dashboard, too!

= Can I add my own sections? =

Yes. There are hooks to add/remove sections, their boxes, and fields.

See: `wp-user-profiles/includes/hooks.php`

= Can I enable the modern profile layout? =

Yes. Add this filter in a small plugin, an mu-plugin, or your theme's code:

`add_filter( 'wp_user_profiles_use_modern_styles', '__return_true' );`

The classic layout remains the default unless the site owner enables this filter.

= Where can I get support? =

* Community: https://wordpress.org/support/plugin/wp-user-profiles
* Development: https://github.com/stuttter/wp-user-profiles/discussions

== Changelog ==

= [2.7.3] - 2026-09-19 =
* Add an opt-in modern profile layout controlled by the `wp_user_profiles_use_modern_styles` filter
* Prevent profile screens from overriding unrelated administration page titles
* Preserve WordPress's user-validation hook contract for compatibility with Jetpack Account Protection and other integrations
* Match current WordPress behavior for profile preferences, downloadable user languages, email validation, and password normalization
* Restore WordPress's confirmation flow for self-service email changes
* Allow deliberate access to WordPress's original profile screens with the `wpup-skip-redirect=1` query argument

= [2.7.2] - 2026-09-16 =
* Require WordPress 6.4 or newer

= [2.7.1] - 2026-09-14 =
* Add the explicit short description required by the WordPress.org directory importer

= [2.7.0] - 2026-09-14 =
* Add integrations for the Two-Factor and User Switching plugins
* Add selectable local and remote strategies for discovering roles across a multisite network
* Correct the password-nag link to open the Account section
* Prevent users without the `promote_users` capability from changing roles
* Improve PHP 8 compatibility for multisite role discovery
* Allow the list of loaded plugin files to be filtered
* Correct saving for syntax highlighting and profile validation errors
* Fix dismissible administration notices
* Improve output escaping throughout profile administration
* Prevent direct access to the multisite sites list table
* Update WordPress compatibility metadata and development dependencies
* Replace retired release and translation chores with deterministic commands
* Add managed release, Plugin Check, and real-WordPress compatibility validation

= [2.6.2] - 2021-08-18 =
* Correct role assignment when saving the Permissions section on multisite
* Check `promote_users` in the context of each affected site
* Prevent stale cached user data from being saved
* Improve the Sites role list table
* Show status dates in the user's timezone
* Avoid a fatal error on WordPress versions without password reset emails
* Load unminified assets when `SCRIPT_DEBUG` is enabled

= [2.6.1] - 2021-05-29 =
* Update author info
* Add sponsor link

= [2.6.0]- 2021-03-26 =
* Improve compatibility with Classic Editor plugin
* Fix some untranslatable strings
* Fix styling for RTL languages
* Add minified styling for production sites

= [2.5.1]- 2021-03-24 =
* Add support for Sending Password Reset Email, in WordPress 5.7
* Fix bug causing unintended revocation of super admin abilities
* Fix bug causing contact methods not to work
* Fix some untranslatable strings

= [2.5.0]- 2020-11-11 =
* Add support for Application Passwords, in WordPress 5.6

= [2.4.0]- 2020-11-11 =
* Improve BuddyPress support
* Improve font size to better match WordPress defaults
* Fix not being able to remove a user's role from a site
* Add sub-navigation UI and API
* Add developer actions at the end of every meta-box display function

= [2.3.1]- 2020-10-07 =
* Fix confusion with site action links

= [2.3.0]- 2020-10-07 =
* Fix non-admins not able to pick languages
* Fix user language being saved as site default
* Add language icon to chooser

= [2.2.1]- 2020-07-07 =
* Fix plugin conflict with Genesis theme

= [2.2.0]- 2020-05-05 =
* General code improvements

= [2.1.0]- 2017-05-24 =
* Fix bug with IS_PROFILE_PAGE constant
* Introduce wp_is_profile_page() function

= [2.0.0]- 2017-05-18 =
* Use 'edit' filter on user data
* Additional capability checks when editing
* First pass support for "Other" section

= [1.2.0]- 2017-01-26 =
* Use WordPress.org for translations

= [1.1.0]- 2016-11-18 =
* Improve security of profile saving (thanks Brady Vercher)

= [1.0.0]- 2016-10-30 =
* Support mu-plugins installation location
* Support for WordPress 4.7
* Improved section/metabox/field key sanitization
* Add multisite "Sites" support
* Add "Language" account setting

= [0.2.0]- 2015-12-21 =
* Simplify metabox registration
* Add actions for metabox sections
* Introduce `edit_profile` meta capability
* Improve load order of files
* Improve support for IS_PROFILE_PAGE
* Improve support for network & user dashboards
* Prevent access to old profile pages (user-edit.php, profile.php, etc...)

= [0.1.10]- 2015-11-12 =
* Improve user dashboard support

= [0.1.9]- 2015-11-11 =
* Fix user-profile script loader issue on network & user dashboards

= [0.1.8]- 2015-11-11 =
* Repackage of 0.1.7 to fix typos

= [0.1.7]- 2015-11-10 =
* Add support for network & user dashboards
* Improve support for WordPress 4.3 & 4.4

= [0.1.6]- 2015-11-09 =
* Improve metaboxes for

= [0.1.5] - 2015-10-23 =
* Add support for third party sections

= [0.1.4] - 2015-10-21 =
* Update scripts & prioritize actions

= [0.1.3] - 2015-10-15 =
* Add support user-status updating

= [0.1.2] - 2015-10-15 =
* Updated description

= [0.1.1] - 2015-10-15 =
* Updated documentation

= [0.1.0] - 2015-09-28 =
* Initial release
