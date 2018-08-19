=== bread ===

Contributors: odathp, radius314, pjaudiomv, klgrimley
Tags: meeting list, bmlt, narcotics anonymous, na
Requires at least: 4.0
Requires PHP: 5.6
Tested up to: 4.9.8
Stable tag: 1.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
A web-based tool that creates, maintains and generates a PDF meeting list from BMLT.

== Description ==

"bread" is a fork of the BMLT meeting list generator.  It allows for the creation of a meeting schedule from a BMLT server.

== Installation ==

This section describes how to install the plugin and get it working.

1. Download and install the plugin from WordPress dashboard. You can also upload the entire BMLT Meeting List Generator Plugin folder to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress
3. Go to the Meeting List menu option.
4. Click on Read This Section First.

Upgrade Information from BMLT Meeting List Generator (original)

Follow all these steps, keep in mind that once you start using bread, it's not going to be easy to back to the original plugin.

1. Ensure that bread is de-activated.
2. Go to your existing "Meeting List", and export the configuration.  (This is in-case something goes bad and you need to undo something).
3. If you have a multi-site installation, be sure to export each one of the configurations within your Network.
4. De-activate the BMLT Meeting List Generator plugin from your site or network (for multisites).
5. Activate bread.  Bread is intended to be fully compatible with BMLT meeting list generator settings.
6. If there is an issue, you can always de-activate bread and go back to the original plugin.
7. You can always restore any files if something got damaged or corrupted assuming that you followed steps 2 & 3.

== Frequently Asked Questions ==

= What does bread use? =
- A complete customized meeting list editor on the web
- Generates and prints a current meeting list in PDF format
- Eliminates the task of maintaining a separate meeting list in MS Word, Excel, etc.
- Eliminates the need to upload a new meeting list to the website every month
- Makes the transition to a new trusted servant much easier
- The generated meeting list will match your BMLT Satellite or BMLT Tabs website meeting list
- The meeting list is setup one time and does not need to be edited when meetings change
- The meeting list can be backed up or exported then imported into another site
- Has its very own current meeting list link which can be shared across the web
- Can use custom queries to a BMLT root server semantic interface.  This can be used by adding everything after ‘?switcher=GetSearchResults’ into the custom query box, for example ‘&services[]=1&services[]=3&services[]=5’ would result in querying service bodies 1, 3 and 5. A good place to build a custom query is by using the semantic interface of your bmlt server.

= How do I contribute?
- Read here for more information: https://github.com/radius314/bread/blob/unstable/contribute.md

== Changelog ==

= 1.5.2 =
* Fixed a bug with the Recurse Service Bodies Checkbox
* Fixed a bug with PDF Protection for Half-Fold Page Layouts

= 1.5.1 =
* Fixing faulty version number

= 1.5.0 =
* Upgraded to mPDF 7.1.1
* Added recurse service bodies option for zones and metros
* Bug fixes for base font selection and some formatting issues
* Remove duplicate formats that can occur with Tomato
* Keep city headers grouped together if casing doesn't match (won't fix sorting issues).

= 1.4.0 =
* Support for PHP 7.1 and up
* Migrated/refactor to support mPDF 7.x
* Introducing base fonts which allow for a more customized styling

= 1.3.1 =

* Graceful protocol rewriting for Front Page + Custom Content
* Hardcoded links forced to HTTPS
* Contributor HTTPS (non-strict) capabilities in Docker (https://localhost:7443)
* Added Debug information for assisting in troubleshooting remotely

= 1.3.0 =

* Bumped up the HTTP GET timeout from 30 seconds to 2 mins.
* Allows for specifying an unpublished ASM (must use credentials.
* Restructured "Special Features" section.
* Removed hardcodings inherited from legacy code base.
* Cleaned up some dead code.

= 1.2.1 =

* Fixed a bug where it was rounding off the decimal duration to the nearest tenth instead of hundreth.
* Fixed a regression in continuing headers.
* Made sidebar colors different to visually differentiate between the BMLT meeting list generator (EOL).

= 1.2.0 =

* Added the ability to hide a sub header when using a two-dimensional group by.

= 1.1.0 =

* Patching bad bug in which GetServerInfo requests were made on every page load.

= 1.0.7 =

* Adding User Agent Signature

= 1.0.6 =

* Show that you are connected to a tomato server now, if you are.
* Minor fixes

= 1.0.5 =

* Fix for short tags that might be off on PHP settings.  There were a couple of dangling occurrences left.
* Compatibility testing for WP 4.9.4

= 1.0.4 =

* Minor bug fix for loading up a fresh plugin

= 1.0.3 =

* Support for Weekday + County group by.

= 1.0.2 =

* Fixed a bug where the custom query regressed because of escaping.

= 1.0.1 =

* Fixed a bug with encoding Spanish characters.
* Fixed a bug where Upper casing was not working on titles.
* Cleaned up boolean settings.
* Better documentation on how to contribute + release details with Github.
* Upgraded docker container to WP 4.9.2.
* Updated description.
* Added upgrade information.

= 1.0.0 =

* Allow for custom queries
* Tons of refactoring to make code more readable
* Implemented the Wordpress HTTP API instead of curl
* Fixed an issue with empty extra meetings arrays
* Securing, validating, escaping POST data
