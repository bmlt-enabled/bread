=== bread ===

Contributors: odathp, radius314, pjaudiomv, klgrimley, jbraswell, otrok7, alanb2718
Tags: meeting list, bmlt, narcotics anonymous, na
Requires PHP: 8.1
Requires at least: 6.2
Tested up to: 6.8
Stable tag: 2.9.4

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
- Read here for more information: https://github.com/bmlt-enabled/bread/blob/main/contribute.md

== Changelog ==

= 2.9.4 =
* Bug fix dealing with alternate headings

= 2.9.1 =
* Bug fix when additional lists are in a different language, time could be computed wrong.

= 2.9.0 =
* Added output to download mPDF debug log.
* Added option to set mPDF optimization settings.
* Fix extra meetings storage.

= 2.8.11 =
* Remove conflicts when TinyMCE is used in the frontend.

= 2.8.10 =
* Improve code quality

= 2.8.7 =
* Fixes in heading generation.

= 2.8.6 =
* Performance Improvements.

= 2.8.5 =
* Bug fixes for Arial, Times fonts.

= 2.8.4 =
* Bug fixes for ASM Meeting Lists

= 2.8.3 =
* Bug fixes

= 2.8.0 =
* Wizard to help getting started on Bread
* Preview meeting lists without saving
* UI Improvements including combining with crouton in WP Admin-Menu
* Major refactoring of code structure
* Removed ASM table as alternative to additional meeting template
* Removed BMLT Login during printing of additional meetings.

= 2.7.13 =
* Multilingual [month_upper].

= 2.7.12 =
* Surpress warning when ob_end_clean is called.

= 2.7.11 =
* Late loading of mPDF to prevent conflicts with other plugins.

= 2.7.10 =
* Updated mPDF
* Added "wheelchair" shortcode
* Small fixes

= 2.7.9 =
* Fixes for aggregator.

= 2.7.8 =
* Fix for Curl User Agent being rejected by SiteGround

= 2.7.4 =
* Brute force cleanup of MPDF temp files
* Force MPDF to use a modern UserAgent

= 2.7.3 =
* Settable footer margin

= 2.7.2 =
* Fix for margins not saving regression.

= 2.7.1 =
* Stability Improvements

= 2.7.0 =
* In Booklet layout, the page number can be customized and set to different text for the meeting list, the additional meeting list, and the other pages.
* Virtual_Meeting_Link and _Additional_Info are now standard fields.
* Always retrieve all fields
* Minor fixes

= 2.6.5 =
* Add a shortcode for format legends in French [format_codes_used_basic_fr]

= 2.6.4 =
* Fixed an issue with plugin activation when using PHP8.
* Upgraded to mPDF 8.0.17
* Allow mPDF initialization values to be set through a filter
* Minor bug fixes.

= 2.6.3 =
* Give the admin the ability to turn off checking the SSL cert.
* Added filter to modify MPDF initialization parameters (e.g. page-size)

= 2.6.0 =
* Added ability to read meeting timezones, and print the meeting list for a target timezone

= 2.5.9 =
* Using json endpoint for version checking.
* Added filter to change download name
* Allow non-integer line heights for custom content.

= 2.5.7 =
* Fixed problem with Caching

= 2.5.5 =
* Fixed problem with user-defined headings
* Fixed problem with hybrid meetings

= 2.5.1 =
* Added Additional Meeting information

= 2.5.0 =
* Customized query for additional meeting list
* Bug fixes

= 2.4.4 =
* Lazy Load Options and Hybrid Meetings

= 2.4.0 =
* Added QR Code generation from shortcode
* Made User-Agent confifurable

= 2.3.1 =
* Special Handling for Virtual Meetings as additional list

= 2.2.2 =
* Fix for sub header not displaying. [#132]

= 2.2.1 =
* Bad version bump

= 2.2.0 =

* User defined grouping / headers
* Add extended fields as possible headings
* Additional List can have same groupings as main list
* Combine headers
* Added Polish
* Added checkbox for using tomato as root server.

= 2.1.2 =
* Brought back 5inch format

= 2.1.1 =
* Bread now automatically calculates how many meetings fit in a column or on a page.
  This eliminates the need to guess an appropriate value for Page-Height-adjustment
  and also makes a more efficient use of the space.
* Support for languages has been refactored to make adding new languages easier.
  Simply add a file in the appropriate directory.
* Shortcodes in the meeting template can be set off with square brackets ({}).
* Additional List can be in a different language from the main list.
* User can restrict the colorspace to Greyscale, RGB or CMYK.

= 2.0.0 =
* Support for multiple configurations.
* Access to configure is now controlled with a custom capability called `manage_bread`.  This is automatically added to the `Administrator` role.
* Include additional (secondary) meeting list.  This can be used to provide a seperate list
  of special interest or foreign language meetings, or to list meetings that for some
  reason are not included in the main list.  This is a generalized version of Bread 1's "additional_list" functionality,
  which was used to list area service meetings.
* Italian, German and Farsi support.
* New layout options
  - Full-Page Layout now full functional
  - A6 Booklet (fits more meetings than flyer, but more convenient to carry around than A5).
  - Watermarks (typically the NA Logo, but you can specify a URL to another image).
  - Page Headers (good when generating posters)
  - Flyer Layout (instead of a tri-fold meeting list, 3 identical meeting lists on a single
    sheet, good for special interest meeting lists)
* Extensibility
  - Site specific BMLT Meeting fields may be used in templates
  - Sites can defined their own complex fields, calculated from other fields,
    that can then be used in templates.
* More robust and efficient handling of short codes in meeting templates.
* Corrections to the meeting group-by mechanism.

= 1.10.0 =
* Limiting query size to needed fields.
* Updated base templates to exclude tables, which cause slow generation.
* Fixed many warning / notices messages.
* Upgraded to mPDF 8.x

= 1.9.8 =
* Fix for margins that are acting "extra" [#65]

= 1.9.7 =
* Fix for string replace with meeting name. [#69]

= 1.9.6 =
* Added support for Danish.

= 1.9.5 =
* Removed most TrueType Fonts to cut down size considerably.

= 1.9.4 =
* Fixing botched 1.9.3 build missing autoloader

= 1.9.3 =
* Added a check for checking if temp folder is writable.  Using the Wordpress influenced temp folder. [#64]
* Changed latest root server version source of truth. [#60]
* Fix for column separator checkbox.
* Fix for day continuation headers [#62]
* Added travis support

= 1.9.0 =
* Added a feature to change the start day of the week [#55]
* Support for Swedish, better language support overall [#54]
* Only allow Administrator role to see the plugin (specifically manage_options permission flag) [#53]

= 1.8.0 =
* Toggle added for extra meetings, improves plugin page load time by not fetching all meetings unless the feature is required.
* Improved handling for page numbering margins [#46]
* Compatible with Wordpress 5.0.0. [#38]
* Added debugging capabilities

= 1.7.7 =
* Icon fixes didn't take

= 1.7.6 =
* Icon fixes didn't take

= 1.7.5 =
* Updating icon and banner image with new BMLT ecosystem design.
* Stock configs images are hosted on bmlt.app instead of nameetinglist.org now. [#49]

= 1.7.4 =
* Moved "Meeting List" link to the bottom of the admin menu to avoid conflicts with hardcoded positioning. [#40]
* Added an actual icon for the "Meeting List" link on the admin page.

= 1.7.3 =
* Version bump

= 1.7.2 =
* Version bump

= 1.7.1 =
* Rollback entrypoint to bread.php change.

= 1.7.0 =
* Upgraded to mPDF 7.1.6
* Added page numbering font size adjustment. [#41]
* Added sort by Neighborhood+City option. [#22]
* Removed default information in stock configuration files. [#35]
* Addressed a number of general long-standing housekeeping issues.

= 1.6.2 =
* Added icon

= 1.6.1 =
* Bad version bump

= 1.6.0 =
* Reusable templating allows overriding a custom query and using some magic shortcodes via the querystring.

= 1.5.3 =
* Upgraded to mPDF 7.1.5
* Fixed a bug with Half-Fold Page
* Fixed a bug in which page numbering was adding blank pages

= 1.5.2 =
* Upgraded to mPDF 7.1.4
* Fixed a bug with the Recurse Service Bodies Checkbox
* Fixed a bug with PDF Protection for Half-Fold Page Layouts
* Fixed image stetchiness on Half-Fold w/ Letter Page Layouts

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
* Allows for specifying an unpublished additional_list (must use credentials.
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
