=== SimpleMap ===

Contributors: aliso
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7382728
Tags: map, store locator, database, locations, stores, Google maps, locator
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: trunk

SimpleMap is an easy-to-use international store locator plugin that uses Google Maps to display information directly on your WordPress site.

== Description ==

SimpleMap is a *powerful* and *easy-to-use* international store locator plugin. It has an intuitive interface and is completely customizable. Its search features make it easy for your users to find your locations quickly.

Key features include:

* Manage locations from any country supported by Google Maps
* Manage an unlimited number of locations
* Put a Google Map on any page or post that gives instant results to users
* Users can enter a street address, city, state, or even the name of a location to search the database
* Customize the appearance of the map and results with your own themes
* Use a familiar interface that fits seamlessly into the WordPress admin area
* Import and export your database as a CSV file
* Quick Edit function allows for real-time updates to the location database
* Make certain locations stand out with a customizable tag (Most Popular, Ten-Year Member, etc.)
* Easy-to-use settings page means you don't need to know any code to customize your map

See the screenshots for examples of the plugin in action.

With SimpleMap, you can easily put a store locator on your WordPress site in seconds. Its intuitive interface makes it the easiest plugin of its kind to use, and the clean design means you'll have a classy store locator that fits in perfectly with any WordPress theme.

== Installation ==

1. Upload the entire `simplemap` folder to your `/wp-content/plugins/` folder.
2. Go to the 'Plugins' page in the menu and activate the plugin.
3. Go to [Google Maps](http://code.google.com/apis/maps/signup.html) to sign up for an API key for your domain.
4. Enter the API key in the 'General Options' page of SimpleMap.
5. Type `[simplemap]` into any Post or Page you want SimpleMap to be displayed in.
6. Enter some locations in the database and start enjoying the plugin!

== Screenshots ==

1. Example of the map and results
2. Location with an image tag in the description
3. Location with HTML formatting in the description
4. General Options page
5. Managing the database

== Frequently Asked Questions ==

= Can I suggest a feature for SimpleMap? =

Of course! Visit [the SimpleMap home page](http://simplemap-plugin.com/) to do so.

= What if I have a problem with SimpleMap, or find a bug? =

Please visit [the SimpleMap home page](http://simplemap-plugin.com/) and leave a comment or [contact me](mailto:alison@alisothegeek.com) with any questions or concerns.

== Changelog ==

= 1.1.1 =
* Support for Cyrillic characters
* Autoloading locations now respects the default zoom level set in General Options
* Link to sign up for Google Maps API key will link to a translated signup page (English, Spanish, German, Japanese, Korean, Portugese, Russian, and Chinese)
* Fixed bug causing error when adding a location before any categories had been created
* Fixed bug causing the default country to reset to "United States"
* Fixed bug regarding duplicate function names; all function names are now unique to the plugin

= 1.1 =
* New Feature: Support for international locations
* New Feature: HTML descriptions for locations
* New Feature: Custom categories for locations
* New Feature: Choose to show or hide the search box with the map
* New Feature: Geocode your default location right in the Admin section
* Simplified Autoload feature: automatically loads at the default location
* Improved Admin interface
* Slightly improved CSV compatibility (in regards to quotation marks)
* Fixed bug causing map overlays to display strangely when a background color was applied to images in that WordPress theme

= 1.0.6 =
* Fixed bug that was causing map to always appear at the top of a page or post
* Modified CSV importing function to be more flexible about quotation marks
* Modified paging in the Manage Database screen to better accomodate a large number of pages
* Added button to Manage Database screen to clear entire database
* Added German & Spanish translations
* Fixed some tiny cosmetic errors
* Removed some redundant code in the Manage Database screen

= 1.0.5 =
* Changed required WordPress version to 2.8

= 1.0.4 =
* Added support for localization
* Fixed bug causing fatal error when trying to activate the plugin

= 1.0.3 =
* Added optional "Powered by SimpleMap" link to map display

= 1.0.2 =
* Fixed bug that was showing ".svn" in the drop-down list of themes
* Added the ability to automatically load the map results from a given location
* Added the ability to change the default search radius
* Added support for both miles and kilometers
* Fixed invalid markup in search form
* Fixed invalid markup in Google Maps script call
* Fixed bug appearing on certain servers when trying to access remote file created by Google Maps

= 1.0.1 =
* Fixed a folder structure problem where an auto-install from within WordPress would give a missing header error.

= 1.0 =
* Initial release

== Making Your Own Theme ==

To upload your own SimpleMap theme, make a directory in your `plugins` folder called `simplemap-styles`. Upload your theme's CSS file here.

To give it a name that shows up in the **Theme** drop-down menu (instead of the filename), use the following markup at the beginning of the CSS file:

`/*
Theme Name: YOUR THEME NAME HERE
*/`

== Other Notes ==

Planned for future releases:

* Custom map markers (that can vary by category)

To suggest any new features, please visit [the SimpleMap home page](http://simplemap-plugin.com/) and leave a comment or [contact me](mailto:alison@alisothegeek.com).

== Translation Credits ==

* German: Thorsten at [.numinose](http://www.numinose.com)
* Spanish: Fernando at [Dixit](http://www.dixit.es)
* Portugese (Brazil): Rodolfo Rodrigues at [ChEngineer Place](http://chengineer.com/)

If you want to help with any translation for this plugin, please don't hesitate to [contact me](mailto:alison@alisothegeek.com). Any help translating is greatly appreciated! The updated `.POT` file is always included in every release, in the `lang` folder.

== License ==

SimpleMap - the easy store locator for WordPress.
Copyright (C) 2009 Aliso the Geek.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>.
