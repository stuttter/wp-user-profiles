# Changelog

## Unreleased

## 2.7.1 (2026-09-14)

* Add the explicit short description required by the WordPress.org directory
  importer

## 2.7.0 (2026-09-14)

* Add integrations for the Two-Factor and User Switching plugins
* Add selectable local and remote strategies for discovering roles across a
  multisite network
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

## 2.6.2 (2021-08-18)

* Correct role assignment when saving the Permissions section on multisite
* Check `promote_users` in the context of each affected site
* Prevent stale cached user data from being saved
* Improve the Sites role list table
* Show status dates in the user's timezone
* Avoid a fatal error on WordPress versions without password reset emails
* Load unminified assets when `SCRIPT_DEBUG` is enabled

## 2.6.1 (2021-05-29)

* Update author information and project links

## 2.6.0 (2021-03-26)

* Improve compatibility with the Classic Editor plugin
* Correct translatable strings and text domains
* Improve right-to-left styling
* Add minified styles for production sites

## 2.5.1 (2021-03-24)

* Support password reset emails introduced in WordPress 5.7
* Prevent unintended removal of super administrator privileges
* Correct contact-method handling
* Correct translatable strings

## 2.5.0 (2020-11-12)

* Support Application Passwords introduced in WordPress 5.6

## 2.4.0 (2020-11-11)

* Improve BuddyPress compatibility
* Better match WordPress font sizing
* Allow a user's role to be removed from a site
* Add the section sub-navigation interface and API
* Add actions after every meta-box display function

## 2.3.1 (2020-10-07)

* Clarify site action links

## 2.3.0 (2020-10-07)

* Allow non-administrators to select a language
* Preserve a user's selected language instead of the site default
* Add the language chooser icon

## 2.2.1 (2020-07-07)

* Correct compatibility with the Genesis theme

## 2.2.0 (2020-05-06)

* Improve general code quality and compatibility

## 2.1.0 (2017-05-24)

* Correct the `IS_PROFILE_PAGE` constant
* Introduce `wp_is_profile_page()`

## 2.0.0 (2017-05-18)

* Apply the `edit` filter to user data
* Add capability checks when editing users
* Add the Other section for legacy profile fields

## 1.2.0 (2017-01-26)

* Use WordPress.org language packs for translations

## 1.1.0 (2016-11-18)

* Improve profile-saving security (props Brady Vercher)

## 1.0.0 (2016-10-30)

* Support installation as a must-use plugin
* Add WordPress 4.7 compatibility
* Improve section, meta-box, and field key sanitization
* Add multisite Sites support
* Add the Language account setting

## 0.2.0 (2015-12-23)

* Simplify meta-box registration
* Add actions for meta-box sections
* Introduce the `edit_profile` meta capability
* Improve plugin load order
* Improve network and user dashboard support
* Prevent access to the replaced profile screens

## 0.1.10 (2015-11-12)

* Improve user dashboard support

## 0.1.9 (2015-11-11)

* Correct profile script loading in network and user dashboards

## 0.1.8 (2015-11-11)

* Repackage version 0.1.7 to correct documentation

## 0.1.7 (2015-11-11)

* Add network and user dashboard support
* Improve WordPress 4.3 and 4.4 compatibility

## 0.1.6 (2015-11-09)

* Update plugin metadata

## 0.1.5 (2015-10-23)

* Support third-party sections
* Show the status meta-box in every section

## 0.1.4 (2015-10-21)

* Update scripts and hook priorities

## 0.1.3 (2015-10-15)

* Support user-status updates

## 0.1.2 (2015-10-15)

* Update the plugin description

## 0.1.1 (2015-10-15)

* Update documentation

## 0.1.0 (2015-10-15)

* Publish the initial release
