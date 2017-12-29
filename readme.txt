=== BMLT Meeting List Generator ===

Contributors: Jack S, Danny G
Tags: meeting list, bmlt, narcotics anonymous, na
Requires at least: 4.0
Tested up to: 4.9.1
Stable tag: 1.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
A web-based tool that creates, maintains and generates a PDF meeting list from BMLT.

== Description ==

The BMLT Meeting List Generator is a complete web-based tool which provides the capability to create, maintain and generate a current PDF meeting list. The Meeting List Generator works hand in hand with the Basic Meeting List Toolbox (BMLT).

FEATURES

- A complete customized meeting list editor on the web
- Generates and prints a current meeting list in PDF format
- Eliminates the task of maintaining a separate meeting list in MS Word, Excel, etc.
- Eliminates the need to upload a new meeting list to the website every month
- Makes the transition to a new trusted servant much easier
- The generated meeting list will match your BMLT Satellite or BMLT Tabs website meeting list
- The meeting list is setup one time and does not need to be edited when meetings change
- The meeting list can be backed up or exported then imported into another site
- Has its very own current meeting list link which can be shared across the web

EXAMPLE MEETING LISTS

- <a href="http://orlandona.org/?current-meeting-list=1" target="_blank">http://orlandona.org/?current-meeting-list=1</a>
- <a href="http://nameetinglist.org/san-diego-imperial-counties-region-central-area/?current-meeting-list=1" target="_blank">http://nameetinglist.org/san-diego-imperial-counties-region-central-area/?current-meeting-list=1</a>
- <a href="http://nameetinglist.org/south-florida-region/?current-meeting-list=1" target="_blank">http://nameetinglist.org/south-florida-region/?current-meeting-list=1</a>

MORE INFORMATION

<a href="http://nameetinglist.org/" target="_blank">http://nameetinglist.org/</a>
== Installation ==

This section describes how to install the plugin and get it working.

1. Download and install the plugin from WordPress dashboard. You can also upload the entire BMLT Meeting List Generator Pluginfolder to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress
3. Go to the Meeting List menu option.
4. Click on Read This Section First.

== Frequently Asked Questions ==

Visit this webpage.

http://nameetinglist.org/faqs/

== Changelog ==

= 1.3.4 =

* Fix: Problem with outputting ASM meetings using the service_body shortcode. Removed hard-coded format code index.
* Change: Output area service meetings (ASM) from Service Body 1 only.
* Note: A future release will allow mix and match of Service Bodies 1-5 to output ASM meetings. Useful for regional meeting lists.

= 1.3.3 =

* Fix: SVN problems.

= 1.3.2 =

* Fix: SVN problems.

= 1.3.1 =

* Fix: SVN problems.

= 1.3 =

* Upgrade: mPDF to v6.1 (see github changelog https://github.com/mpdf/mpdf/blob/6.1/CHANGELOG.txt).
* Added: Shortcode for including service meetings from service body 1 only [service_meetings_service_body_1].
* Added: Meeting template field shortcode for including bus line [bus_line].
* Change: Password fields from text to password input format (mask field value).

= 1.2.11 =

* Fix: Forgot to include javascript to support new screencast.

= 1.2.10 =

* Fix: Do include ASM in meeting format legend when "Show Area Service Meetings" not selected.
* Add: Screencast instructions for "Show Area Service Meetings" (under Meeting tab).

= 1.2.9 =

* Fix: Change log was not showing v1.2.7 and v1.2.8 changes.

= 1.2.8 =

* Fix: The string "state" in the meeting template street address was being replace with the actual State from the BMLT database.

= 1.2.7 =

* Fix: cURL was not allowing BMLT servers using SSL.

= 1.2.6 =

* Fix: Missed adding a javascript file from v1.2.5.

= 1.2.5 =

* Fix: Meeting lists using region service body quit working with BMLT server v2.6.31 update.
* Added: "Weekday + City" sort option for meeting group header.

= 1.2.4 =
* Fix: Included utf8_encode function to french translation for month shortcode.
* Change: Added new loading spinners for "Connecting to BMLT" and "Saving Changes".

= 1.2.3 =

* Change: Expanded Service Body dropdown info to accomodate BMLT servers with multiple regions.

= 1.2.2 =

* Change: Switched to getting native language from BMLT using switcher=GetServerInfo.

= 1.2.1 =

* Fix: Fatal error in PHP v5.3 and earier resulting from empty array.
* Fix: Remove stray commas at the end of line in the meeting template.
* Added: Get server language to help with French translation of (cont) string in group column header.

= 1.2 =

* New: Feature to add and merge extra meetings into meeting list from other service bodies.
* Change: Show password fields. Tool is not public facing and already has a layer of credentials.

= 1.1.5 =

* Fix: Enabled [service_meetings] shortcode on front and last page templates.

= 1.1.4 =

* Fix: Changed $mirrormargins to false. Was causing duplex printing issues in Acrobat.

= 1.1.3 =

* Fix: Suffix field was not saving properly.

= 1.1.2 =

* Added: Optional suffix field when Borough and/or County group sort is selected.
* Fix: Sort Borough + County group.
* Fix: Filter/remove erroneous comma at beginning of line in meeting template.

= 1.1.1 =

* Added: BMLT neighborhood location field to meeting template.

= 1.1 =

* New: Option to encrypt and set PDF document permissions for the PDF file with password protection.
* Fix: Filenaming convention when saving PDF from browser.

= 1.0.13 =

* Fix: Template for column separator causing erroneous characters (question marks).

= 1.0.12 =

* Fix: Function being used in v1.0.11 to strip invalid characters was stripping line feeds.

= 1.0.11 =

* Fix: HTML contains invalid UTF-8 character(s). Copy and pasted text was introducing invalid characters. Added function to strip invalid characters.

= 1.0.10 =

* Fix: File encoding issue causing headers already sent.

= 1.0.9 =

* Fix: Correct Spanish translation for Monday.
* Added: Portuguese weekday selection to meeting group header
* Fix: Carrot not displaying on instructions header.
* Fix: Correct verbage for "Are You Sure" confirmation when using import function.
* Fix: Weekday header not printing when using weekday + area sort.

= 1.0.8 =

* Fix: PHP file encoding creating "headers already sent" issue
* Fix: Disabled Column Seperator when using half-page booklet layout.
* Fix: Improved usability of dropdown for Format Code Legend shortcodes.

= 1.0.7 =

* Fix: Added a space to formate codes (OD, BT, S) to allow proper word-wrapping.
* Added: Shortcodes for French and Spanish months [month_lower_fr], [month_lower_es), etc.

= 1.0.6 =

* House Cleaning

= 1.0.5 =

* House Cleaning

= 1.0.4 =

* House Cleaning

= 1.0.3 =

* Added: French date format.
* Added: French weekday selection to meeting group header.
* Added: Validation to export submit button when no file has been chosen.
* Fix: Problem with headers already sent.
* Fix: Cleaned up some styles and spelling corrections.

= 1.0.2 =

* House Cleaning

= 1.0.1 =

* House Cleaning

= 1.0.0 =

* Initial Release