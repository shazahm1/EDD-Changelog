=== EDD Changelog ===
Contributors: shazahm1@hotmail.com
Donate link: http://connections-pro.com/
Tags: edd, easy digital downloads
Requires at least: 3.8
Tested up to: 4.0
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Add a new metabox to the download where you can input the changelog. The changelog will be appended to the download page and the purchase history.

== Description ==

This plugin requires  1.6 or greater.

When using the [Easy Digital Downloads](http://wordpress.org/extend/plugins/easy-digital-downloads/) plugin to sell digital downloads, this plugin will allow you to optionally add a change log and version number to each of your downloads.

**What does EDD Changelog do?**

It does a few things...

*   It adds a metabox to the download post page that allows you to enter the change log and the current version number.
*   It'll automatically append the change log at the end of the download's product page in a collapsible container. If you do not want to automatically append the change log, there is an option to turn this feature off per download.
*   It also adds a new shortcode, `[edd_changelog]`. This shortcode when used on a download page will automatically show the current downloads change log. The shortcode has 4 options, `id`, `toggle`, `show` and `hide`.
*   The change log and version will also be inserted in the `[edd_receipt]` shortcode under the download link. The change log is presented as a link, that when clicked, will show in a lightbox.


**What about <a href="https://easydigitaldownloads.com/extension/software-licensing/">EDD Software Licensing</a>?**

 This plugin completely compatible. If you're not yet using [EDD Software Licensing](https://easydigitaldownloads.com/extension/software-licensing/) and you decide to start, [EDD Software Licensing](https://easydigitaldownloads.com/extension/software-licensing/) will automagically use the change log and version number you entered using this plugin. If you are using [EDD Software Licensing](https://easydigitaldownloads.com/extension/software-licensing/) this plugin will avoid redundancy and will use the change log and version entered into it.


== Installation ==

Extract the zip file and just drop the contents in the `wp-content/plugins/` directory of your WordPress installation and then activate the Plugin from Plugins page.

== Frequently Asked Questions ==

None at this time.

== Screenshots ==

None at this time.
 
== Changelog ==

= 1.1 10/02/2014 =
* FEATURE: Add the current version to the download history shortcode output.
* BUG: Fix the double output of the quicktags in the changelog field.

= 1.0 6/26/2013 =
* Initial Release.

== Upgrade Notice ==

= 1.0 =
Initial Release.
