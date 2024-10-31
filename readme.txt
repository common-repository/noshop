=== NoShop Product Page ===

Contributors: dkguru
Tags: noshop, products, list, shop
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: trunk
License: GPLv2

NoShop Product Page allows you to create a list of products/items with pictures without using a full shopping cart.

== Description ==

NoShop Product Page allows you to create a formatted list of products/items with pictures.

This allows you to create a shopping cart like product page or employee list with pictures without having to install
and maintain a full shopping cart.

Data is stored in the wordpress database.

Pictures has to be pre-uploaded to somewhere and then referenced via URL.

For each item you can add specifications tags.

See also: [NoShop](http://wordpress.org/extend/plugins/noshop/).

== Installation ==

How to install the plugin and get it working.

1. Upload `plugin-name.php` to the `/wp-content/plugins/` directory. (Or install via WP plugin directory)

2. Activate the plugin through the 'Plugins' menu in WordPress.

3. Add products on the option page.

4. Place `[NoShop <category name>]` in your page.

== Frequently Asked Questions ==

= Why is this so clunky =

This plugin is still in BETA. Please feel free to visit my page and submit some comments and suggestions :-)

= Are there other plugins that works! =

This works! I am currently using it in a live environment - however - it IS clunky...

I wrote is because I couldn't find any other plugins that does the same (simple list of products - no shopping cart - no fancy setup).

If you find a solution that's easy to use, feel free to tell me and I'll happily stop wasting my time re-inventing the wheel :-)

== Screenshots ==

1. NoShop! in action
2. NoShop! Admin interface

== Changelog ==

= 0.8.4 =

* Change: Reversed This ChangeLog
* New: Added {hashtitle} that will take you directly to a product on the page (See table.template.xhtml for example)
* New: Added {autourlbegin} and {autourlend} that will create the A HREF tag for you, but NOT do so if you do not have an URL for the product.
       {url} still works and is still used for the product image so the template has an example of both.

= 0.8.3 =

* Fix: Corrected call to dbDelta() and update_option(). Database should now upgrade without need for deactivate/activate

= 0.8.2 =

* Added note about multi-categories.
* Added link to settings page to Wordpress Plugin Directory Support page for this plugin.

= 0.8.1 =

* Tested on WordPress 3.4.2 - Passed!
* DB Version updated to 0.8.1 to keep in sync with plugin

= 0.8 =

* New: Sorting index for entries
* New: Added Category name and index as variable to template (use {cat} and {ndx}).
* New: Added Category name and index as HTML comment in table.template.
* Fix: Multiple list on one page NOW WORKS! (I had a DAH moment :-)

= 0.7.8 =

* Fix: Whoops, got my versioning out of sync. Fixed. :-)

= 0.7.7 =

* Added delete button for products.
* Fix: Multiple Lists on same page now working.
= 0.7.6 =

* Fix: Invalid argument supplied for foreach() - Thanks Gayan Hewa!

= 0.7.5 =

* Added donation button. Thank you for considering it :-)
* Fixed this readme file (again!)

= 0.7.4 =

* Rewrote CSS and XHTML templates for better CSS
* New CSS much leaner
* New simpler back end been revised but still buggy (will be released later this week)
* This version now allow for custom CSS separate from install
* Added width field for value headers

= 0.6 =

* First rewrite. Horrible. Trying to forget.

= 0.5.3 =

* Uh oh. DB didn't update. Temporary fix in there. Plz ignore update error - it's not real :-)

= 0.5.2 =

* Moved output to template based output. Plugin now uses html templates and css for all formatting.
* Template per item: table.template.html
* Template per specification: subtable.template.html
* Keep templates in plugin directory.
* ONLY HARDCODED TEMPLATE for now. Later I'll add selectable :-)

= 0.5.1 =

* Redesigned options screen to more compact format.

= 0.5 =

* Fixed small errors.
* Fixed versioning.
* Added imgurlmode field to DB (DB now version 0.5!)
* Fixed ' != ` error in dbDelta that prevented DB upgrade!

= 0.4.5 =

* Added screenshots.

= 0.4.4 =

* Added default image to settings.

= 0.4.3 =

* Updated options page: Add specification button added
* Updated options page: Showing thumbnail picture preview

= 0.4.2 =

* Updated options page: Moved 'Create Product' to a button rather than a checkbox.

= 0.4.1 =

* Error fix: Added 'static' to NoShop::Products()
* Updated this readme.txt

= 0.4 =

* New and improved admin page

= 0.3 = 

* First version of admin page

= 0.2 =

* Created functions to show database contents in list
* Created CSS

= 0.1 =

* Copied hello.php
