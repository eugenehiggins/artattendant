=== iOS images fixer ===
Contributors: Bishoy.A
Tags: ios, iphone, thumbnails, media, images, upload
Donate link: http://bishoy.me/donate
Requires at least: 2.0.0
Tested up to: 4.7.3
Stable tag: 1.2.4
Author: Bishoy A.
License: GPL2
License URI: http://www.gnu.org/licenses/license-list.html#GPLCompatibleLicenses

Automatically fix iOS-taken images' orientation using ImageMagic/PHP GD upon upload.

== Description ==
By default, thumbnails of photos taken by an iOS device (iPhone or iPad) are flipped 90 degrees to the left, it's a long image EXIF information story. This plugin takes care of this and fixes the uploaded images orientation's (if needed, based on EXIF data) using ImageMagic Library if available or PHP GD as a fallback. 

No settings editing required, just activate the plugin and try uploading an image from your idevice!

== Frequently Asked Questions ==
= Is there any special requirement? = 
* PHP GD library or ImageMagic.
* exif extension installed.

= Are there any code-level modifications required? =
No, just install the plugin and continue blogging happily.

== Screenshots ==

1. Apart from automatically fixing images upon upload, we're introducing a new feature for fixing iOS images manually, including images that were uploaded before the plugin's installation!
2. Fix Images. Those are the ones that were not catched by the plugin for some reason, or has been uploaded before the plugin's installation.
3. This is what the plugin would do to the image in figure 2.

== Installation ==
1. Go to your admin area and select Plugins -> Add new from the menu.
2. Search for "iOS Images Fixer".
3. Click install.
4. Click activate.

== Changelog ==

= 1.2.4 =
* Support for PHP 7, (used exif_read_data function instead, props @lumpysimon)
* Code revision
* Updated Donation link.

= 1.2.3 =
* Code revision
* Added Pointer with donate rate links.

= 1.2.2 =
* Bug fixes

= 1.2.1 =
* Added a check to make sure the image has EXIF data before proceeding.

= 1.2 =
* Added manual fix page to let you fix images that were uploaded before installing the plugin!

= 1.1 =
* Added conditional check and admin notice in case of disabled required functions.

= 1.0 =
* Initial plugin release.
