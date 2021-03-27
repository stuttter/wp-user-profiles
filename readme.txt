=== WP User Profiles ===
Contributors:      johnjamesjacoby, stuttter, baden03
Tags:              users, user, profile, edit, metabox
Requires PHP:      7.2
Requires at least: 5.2
Tested up to:      5.8
Stable tag:        2.6.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Donate link:       https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9Q4F4EL5YJ62J

== Description ==

WP User Profiles is a sophisticated way to edit users in WordPress.

* Includes all functionality from WordPress itself
* Includes 4 top-level "Sections"
* Includes an "Other" section to automatically work with third-party plugins
* Each section includes 1 or more meta-boxes
* Status meta-box allows easily changing user status
* Works great with multisite Network and User Dashboards
* Works great with WP User Groups and WP User Avatars plugins

= Also checkout =

* [WP Chosen](https://wordpress.org/plugins/wp-chosen/ "Make long, unwieldy select boxes much more user-friendly.")
* [WP Pretty Filters](https://wordpress.org/plugins/wp-pretty-filters/ "Makes post filters better match what's already in Media & Attachments.")
* [WP Media Categories](https://wordpress.org/plugins/wp-media-categories/ "Add categories to media & attachments.")
* [WP Term Order](https://wordpress.org/plugins/wp-term-order/ "Sort taxonomy terms, your way.")
* [WP Term Authors](https://wordpress.org/plugins/wp-term-authors/ "Authors for categories, tags, and other taxonomy terms.")
* [WP Term Colors](https://wordpress.org/plugins/wp-term-colors/ "Pretty colors for categories, tags, and other taxonomy terms.")
* [WP Term Icons](https://wordpress.org/plugins/wp-term-icons/ "Pretty icons for categories, tags, and other taxonomy terms.")
* [WP Term Visibility](https://wordpress.org/plugins/wp-term-visibility/ "Visibilities for categories, tags, and other taxonomy terms.")
* [WP User Groups](https://wordpress.org/plugins/wp-user-groups/ "Group users together with taxonomies & terms.")
* [WP User Activity](https://wordpress.org/plugins/wp-user-activity/ "The best way to log activity in WordPress.")
* [WP User Avatars](https://wordpress.org/plugins/wp-user-avatars/ "Allow users to upload avatars or choose them from your media library.")

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

Yes. There are a bunch of filters to add/remove sections, and their boxes & fields.

= Where can I get support? =

* Community: https://wordpress.org/support/plugin/wp-user-profiles
* Development: https://github.com/stuttter/wp-user-profiles/discussions

= Where can I find documentation? =

http://github.com/stuttter/wp-user-profiles

== Changelog ==

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
