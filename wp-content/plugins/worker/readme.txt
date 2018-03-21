=== ManageWP Worker ===
Contributors: managewp,freediver
Tags: manage multiple sites, backup, security, migrate, performance, analytics, Manage WordPress, Managed WordPress, WordPress management, WordPress manager, WordPress management, site management, control multiple sites, WordPress management dashboard, administration, automate, automatic, comments, clone, dashboard, duplicate, google analytics, login, manage, managewp, multiple, multisite, remote, seo, spam
Requires at least: 3.1
Tested up to: 4.9
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/quick-guide-gplv3.html

A better way to manage dozens of WordPress websites.

== Description ==

So you're looking for a better way to manage WordPress websites? We have you covered! [ManageWP](https://managewp.com/ "Manage Multiple WordPress Websites") is a dashboard that helps you save time and nerves by automating your workflow, so you could focus on things that matter. It is fast, secure and free for an unlimited number of websites.

= Everything in One Place =
Just the hassle of logging into each of your websites is enough to ruin your day. ManageWP compiles the data from all of your sites on one dashboard, so you can check up on your websites in a single glance. And if you need to take a better look at a particular website, you're just a click away. [Read more](https://managewp.com/features/1-click-login "1-click login")

= Bulk actions =
57 updates on 12 sites? Update them all with a single click. And it's not just updates. Clean spam, database overhead, run security checks and more - with just one click you can do these things on all your websites at once. [Read more](https://managewp.com/features/manage-plugins-and-themes "Manage plugins & themes")

= Cloud Backup that just works =
A reliable backup is the backbone of any business. And we have a free monthly backup for all of your websites. It's, incremental, reliable, and works where other backup solutions fail. The free Backup includes monthly scheduled backup, off-site storage, 1-click restore, US/EU storage choice and the option to exclude files and folders. The premium Backup gives you on-demand backups, weekly/daily/hourly backup cycles & [more](https://managewp.com/features/backup "ManageWP Backup").

= Safe updates =
Updating plugins & themes is a huge pain, so we came with this: a backup is automatically created before each update. After the update, the system checks the website and rolls back automatically if something's wrong. And the best part is that you can set these updates to run at 3am, when the website traffic as its lowest.
[Read more](https://managewp.com/features/safe-updates "Safe Updates").

= Client Report =
Summarize your hard work in a professional looking report and send it to your clients to showcase your work. The free Client Report includes basic customization and on-demand reports. The premium Client Report lets you white label and automate your reports. [Read more](https://managewp.com/features/backup "Client Report")

= Performance and Security Checks =
Slow or infected websites are bad for business. Luckily, you can now keep tabs on your websites with regular performance & security checks. The free [Security Check](https://managewp.com/features/security-check "security check") & [Performance Check](https://managewp.com/features/performance-scan "performance check") come with fully functional checks and logging. Premium versions let you fully automate the checks, and get an SMS or an email if something's wrong.

= Google Analytics integration =
Connect multiple Google Analytics accounts, and keep track of all the important metrics from one place.  [Read more](https://managewp.com/features/analytics "Google Analytics integration")

= Uptime Monitor (premium add-on) =
Be the first to know when your website is down with both email and SMS notifications, and get your website back online before anyone else notices. [Read more](https://managewp.com/features/uptime-monitor "Uptime Monitor")

= Cloning & Migration (bundled with premium Backup add-on) =
What used to take you hours of work and nerves of steel is now a one-click operation. Pick a source website, pick a destination website, click Go. Within minutes, youw website will be alive and kicking on a new server. Yeah, it's that easy. [Read more](https://managewp.com/features/clone "Cloning & migration")

= SEO Ranking (premium add-on) =
Be on top of your website rankings and figure out which keywords work best for you, as well as keeping on eye on your competitors. This way you will know how well you stack up against them. [Read more](https://managewp.com/features/seo-ranking "SEO Ranking")

= White Label (premium add-on) =
Rename or completely hide the ManageWP Worker plugin. Clients don’t need to know what you are using to manage their websites. [Read more](https://managewp.com/features/white-label "White Label")

= Is This All? =
No way! We've got a bunch of other awesome features, both free and premium, you can check out on our [ManageWP features page](https://managewp.com/features "ManageWP Features")

Check out the [ManageWP promo video](https://vimeo.com/220647227).

https://vimeo.com/220647227

== Changelog ==

= 4.5.0 =

- Improvement: Removed deprecated ManageWP Classic code.

= 4.4.0 =

- Fix: Communication failing with a website behind CloudFlare, that has warnings turned on, and currently has warnings.

= 4.3.4 =

- Improvement: The Worker plugin can now only be activated network wide on multisite installs.
- Fix: Edge cases where the connection key was not visible.
- Fix: Edge cases with Multisite communication failure.

= 4.3.3 =

- Improvement: Always force the correct charset for database backups.
- Improvement: The Worker plugin is now fully compatible with WordPress 4.9.

= 4.3.2 =

- Fix: The Worker plugin threw an exception while recovering from failed update.

= 4.3.1 =

- Fix: The Worker plugin could not fetch keys for the new communication system in some cases.

= 4.3.0 =

- New: Ability to install/update Envato plugins and themes.
- New: WooCommerce database upgrade support.
- New: More secure and flexible communication between the Worker plugin and the ManageWP servers.

= 4.2.27 =

- Fix: Added missing property check when checking for updates.

= 4.2.26 =

- Fix: Added index file to every Worker directory to prevent file listing.
- Fix: Use the correct database prefix in a recently added user query.

= 4.2.25 =

- Improvement: When managing 500+ users. they are no longer sorted by roles.
- Improvement: The Worker plugin will no longer automatically deactivate in specific cases.

= 4.2.24 =

- Improvement: ManageWP Worker plugin can now automatically connect to another account even if the plugin is hidden by our white label feature.

= 4.2.23 =

- Improvement: We can now display whether a site is connected to a GoDaddy Pro or ManageWP account.

= 4.2.22 =

- Fix: Detecting real upload path when using symbolic links.

= 4.2.21 =

- Fix: We can now always detect the parent site correctly on multisite networks.
- Fix: Fixed an issue where a website could not be backed up in specific permission setups on Windows.
- Fix: The parent site in the multisite network is now always going to list all super admins correctly.

= 4.2.20 =

- Improvement: Fixed multisite not showing theme updates correctly in some cases.

= 4.2.19 =

- New: Ability to turn off ManageWP analytics.
- Improvement: Multisite compatibility.
- Fix: Plugin installations falsely marked as failed.

= 4.2.18 =

- Fix: Some compatibility issues with Pantheon hosting.
- Fix: Plugin not activating after installation in some cases.
- Fix: We now correctly return the number of users for multisite installations.

= 4.2.16 =

- New: Multisite backup support for ManageWP Orion.

= 4.2.15 =

- Fix: Core updates not showing correctly in some cases.
- Fix: Better memory limit handling.
- Fix: SpamShield plugin compatibility.

= 4.2.14 =

- Fix: Compatibility with WordPress 4.7.

= 4.2.12 =

- Fix: Plugin/theme updates not showing correctly in some cases.

= 4.2.11 =

- Fix: Connectivity issues caused by non-UTF-8 characters.

= 4.2.10 =

- Improvement: Symlink support for Orion backups.

= 4.2.9 =

- New: Support for translation updates.
- Improvement: White Label option allows to separate editing from install/update.
- Improvement: Full PHP7 compatibility.

= 4.2.7 =

- Improvement: Detection of child theme updates.

= 4.2.6 =

- Improvement: Reduced Worker memory footprint during sync by 50%.
- Improvement: Better handle available updates in some special cases.
- Fix: Fixed sync issues with sites using Shield plugin.
- Fix: Correctly remove child themes in some special cases.

= 4.2.2 =

- Fix: Fixed a bug that showed false positives when updating multiple plugins.

= 4.2.1 =

- Improvement: Multisite support has been heavily improved and implemented in Orion.

= 4.2.0 =

- Improvement: Now the sync process is faster and more reliable.
- Improvement: Updating plugins/themes has also been enhanced.
- Improvement: The plugin is now able to self recover deleted files without failing any requests before that.
- Improvement: The incremental update feature has been improved.
- Fix: Better PHP7 support.
- Fix: The "nonce already used" error message is fixed in some cases where it was due to a plugin conflict.

= 4.1.33 =

- Improvement: Website wp-admin login is now faster,
- Fix: Certain cases where logging into an HTTP website with an HTTPS wp-admin did not log you in properly.
- Fix: Redeclare error that caused a plugin update to report a false negative.

= 4.1.32 =

- Improvement: With a few improvements we are fully compatible with Pantheon.
- Fix: Previous problems with the false-positive theme and plugin updates have been resolved. These updates will now be performed fully.
- Fix: You will no longer receive an unhandled exception error message regarding the unlink of a directory in the Worker plugin.

= 4.1.31 =

- Fix: Comments with non-UTF-8 characters no longer have issues with sync.

= 4.1.30 =

- Improvement: Hardened the ManageWP Worker recovery mode, which allows it to automatically resync to your ManageWP dashboard after a crash. That's one small step for mankind, one giant leap for Skynet.
- Improvement: Incorrect syntax used to define plugin and content directories no longer prevents ManageWP Orion from adding these directories to the backup archive.
- Fix: Updates not showing up on websites with WPMU DEV plugins. Well, technically it's a workaround and not a fix, since the previous fix didn't fix things, but nobody reads these changelogs anyway. Oh you do? Sorry about that, then. /highfive
- Fix: ManageWP Worker plugin losing the white label setting in certain specific scenarios.

= 4.1.29 =

- Improvement: Post revision cleanup is now faster & furiouser (is that even a word?)
- Improvement: Reduced the number of queries for non-ManageWP requests (e.g. frontpage load) by roughly 80%
- Fix: Compatibility issue with WPMU DEV plugins that prevented updates from showing up on the ManageWP dashboard
- Fix: Several connectivity issues caused by non-UTF-8 characters
- Fix: Admin login bug

= 4.1.28 =

- Fix: Compatibility issue with the wpShopGermany plugin. Ausgezeichnet!

= 4.1.27 =

- Improvement: Due to popular demand, a number of improvements have been made to the backup script, making it more stable.
- Fix: Plugin/theme updates double-crossing you with a false positive, even though the updates have not been performed.
- Fix: Database optimization not doing what it is told.
- Added: A sense of humor to an otherwise boring changelog.

= 4.1.26 =

- Fix: Bug fixes and performance improvements

= 4.1.25 =

- New: Added functionality for plugin/theme management
- Improvement: Improved Worker performance
- Fix: Improved the Orion incremental backup compatibility
- Fix: Improved the one-click login functionality to work with some special cases
- Fix: Improved the plugin/theme update functionality

= 4.1.24 =

- Fix: Incremental backup table dump improvement
- Fix: Incremental backup file listing improvements
- Fix: Better recovery system for the Worker
- Fix: Improved the incremental updating system of the Worker

= 4.1.23 =

- Fix: Improved compatibility with other plugins

= 4.1.22 =

- Fix: Some minor fixes.

= 4.1.21 =

- Fix: Improve compatibility with other plugins

= 4.1.20 =

- Fix: Improved one-click login

= 4.1.19 =

- Fix: Some minor fixes and improvements
- Fix: Improve compatibility with other plugins

= 4.1.18 =

- Fix: Improved the ManageWP incremental backup system

= 4.1.17 =

- Fix: Updated the ignore list for backups

= 4.1.16 =

- Fix: Some minor fixes and improvements.

= 4.1.15 =

- Fix: Fix an issue where the plugin would not work properly if the OpenSSL extension was corrupted

= 4.1.14 =

- New: Expired transient cleaner
- Fix: Improve compatibility with other plugins.
- Fix: Improve plugin installation

= 4.1.13 =

- Add translation domain for WordPress plugin repository.

= 4.1.12 =

- Fix: Improve support for non UTF8 encoded file names for [incremental backups](https://managewp.com/managewp-orion-developer-diary-3-bulletproof-backup-solution)
- Fix: Fix an issue where valid keys were rejected on some specific configurations.

= 4.1.11 =

- Fix: Fix issue where the plugin did not return error codes when the remote call is not authenticated, which resulted in techie-talk error messages.

= 4.1.10 =

- Fix: Improve available update detection when working with plugins that hook onto WordPress update API.

= 4.1.9 =

- Fix: Improve available update detection.
- Fix: Improve the incremental ManageWP Worker plugin updating system.

= 4.1.8 =

- Fix: Potential security issue patched.
- Fix: Optimize spam comment cleanup time.
- Fix: Numerous improvements for [incremental backup system](https://managewp.com/managewp-orion-developer-diary-3-bulletproof-backup-solution).
- Fix: Improve compatibility with other plugins.
- Fix: Improve available update detection.

= 4.1.7 =

- Fix: Optimize memory usage with incremental backups.
- Fix: Improve compatibility on sites with open_basedir restriction.
- Fix: Numerous other fixes and improvements.

= 4.1.6 =

- Fix: Improve [incremental database backup](https://managewp.com/managewp-orion-developer-diary-3-bulletproof-backup-solution) reliability.
- Fix: Improve automatic ManageWP Worker plugin recovery.

= 4.1.5 =

- New: Add automatic recovery process when the ManageWP Worker plugin update gets interrupted on some server setups.
- Fix: Fix maintenance mode on some WordPress setups.
- Fix: Fix issue when a wrong backup file was being deleted.
- Fix: Fix issues when sites got disconnected from [ManageWP Orion](https://managewp.com/managewp-orion-official-announcement "ManageWP Orion Official Announcement").

= 4.1.4 =

- Fix: Improve [incremental backup](https://managewp.com/managewp-orion-developer-diary-3-bulletproof-backup-solution) success rate.

= 4.1.3 =

- Fix: Fix database backup functionality on servers without mysqldump.
- Fix: Fix incremental backups on PHP 5.2.

= 4.1.2 =

- The following changelog is for [ManageWP Orion](https://managewp.com/managewp-orion-official-announcement "ManageWP Orion Official Announcement") only.
- Fix: Fix PHP database dumper fallback in incremental backups.
- Fix: Fix restore functionality on some server setups.

= 4.1.1 =

- Fix: Fix incremental backup issue when dealing with deep symlinks.
- Fix: Slightly increase memory limit if needed, after successfully adding a website.

= 4.1.0 =

- New: Incremental backup capability for [ManageWP Orion](https://managewp.com/managewp-orion-official-announcement "ManageWP Orion Official Announcement").

= 4.0.15 =

- Fix: Improve compatibility with some plugin updates in [ManageWP Orion](https://managewp.com/managewp-orion-official-announcement "ManageWP Orion Official Announcement").

= 4.0.14 =

- Fix: Show custom message when the plugin can not destroy active [ManageWP Orion](https://managewp.com/managewp-orion-official-announcement "ManageWP Orion Official Announcement") sessions when logging out.

= 4.0.13 =

- New: Destroy all admin sessions started from [ManageWP Orion](https://managewp.com/managewp-orion-official-announcement "ManageWP Orion Official Announcement") when a user logs out from the dashboard.
- Fix: Improve update detection.

= 4.0.12 =

- Fix: Improve white-labeling.
- Fix: Better compatibility with development builds of WordPress.
- Fix: Improve one-click restore functionality on non-English installations of WordPress.

= 4.0.11 =

- Fix: Better detect available updates
- Fix: Improve compatibility with other plugins

= 4.0.10 =

- Fix: Fix update functionality on some installations that use FTP credentials
- Fix: Fix Google Drive uploading on installations without cURL PHP extension

= 4.0.9 =

- New: Make the ManageWP Worker plugin upgradable through the dashboard widget
- Fix: Improve auto-connect functionality with [ManageWP Orion](https://managewp.com/managewp-orion-official-announcement "ManageWP Orion Official Announcement")

= 4.0.8 =

- Fix: Fix single-click restore functionality

= 4.0.7 =

- Fix: Fix issues related to cloning and backup restoration
- Fix: Improve precision of the hit counter
- Fix: Improve compatibility with other plugins
- Fix: Numerous small improvements and fixes

= 4.0.5 =

- Fix: Misc bug fixes and performance improvements

= 4.0.1 =

- New: New features for the [ManageWP Orion release](https://managewp.com/managewp-orion-official-announcement "ManageWP Orion Official Announcement")
- Fix: Misc bug fixes and performance improvements

= 3.9.30 =

- New: Fully compatible with WordPress 4.0
- New: Adding websites to your ManageWP Dashboard is now easier than ever
- Fix: Backup tool improvements (especially for websites located on Rackspace)
- Fix: Various Clone/Migration tool improvements and fixes
- Fix: SEO PDF report visual enhancement
- Fix: Various interface improvements and fixes

= 3.9.29 =

- New: Worker plugin is now 36% faster and uses 83% less memory
- New: Backup no longer relies on WordPress cron
- New: New Server-Client communication fixing some of the previous issues
- New: Notes and Recent backups widgets
- New: Refreshed app interface :)

= 3.9.28 =
- New: Control WordPress Automatic Background Updates for plugins and themes!
- Fix: Tweaks to SFTP support for backups and clone
- Fix: Enhancements to Backup and Branding features


= 3.9.27 =
- New: SFTP support for backups and clone!
- Fix: Database dump for backup tasks with defined socket path or port number in wp-config.php
- Fix: Optimize WordPress tables before backup
- Fix: Compatibility with Better WP Security
- Fix: Not adding jQuery on front page while using branding option

= 3.9.26 =
- New: Improved branding feature
- New: Disable Plugin and Theme changes for your clients
- New: Support Page for non-Admin Users
- New: Manage biographical info of user
- Fix: Restore backup action keeps all backup tasks and backups
- Fix: Add/delete post action uses WordPress hook
- Fix: Delete user action was not functioning properly

= 3.9.25 =
- New: Improved Worker branding feature
- Fix: Traffic alerts feature was not functioning properly
- Fix: Backup information was sometimes incorrectly displayed
- Fix: DB Table overhead was not shown on the dashboard

= 3.9.24 =
- New: Better support for large database dumps
- Fix: PHP notice for WordPress 3.5
- Fix: Support for automatic backup reports
- Fix: Incorrect backup result message for S3 large files

= 3.9.23 =
- New: SEO reports can be branded and viewed by sharing an URL
- New: Set custom database prefix for new clone destination
- New: Automatic change all URL paths for new clone destination
- New: Success and fail email notifications for scheduled backup tasks
- Fix: Improved scheduled backups for limited server resources
- Fix: Improved backup to Dropbox (now supporting larger backup files)
- Fix: Handling of external images with bulk posting
- Fix: Display plugin versions on manage plugins
- Fix: Deprecated get_themes function
- Fix: Special characters support for notes

= 3.9.22 =
- New: Backup support for Google Drive
- New: Keyword tracking limit increased from 5 to 20 times the website limit (ie. with 25 website account you can now track the ranking for 500 keywords!)
- New: Support for Google Analytics API 3.0
- New: Website preview screenshot
- New: Ability to assign a newly added website to existing Backup tasks (under "advanced" in add website dialogue)
- Fix: Clone tool now supports special characters and localized WP installs
- Fix: Backup history preserved on website re-add

= 3.9.21 =
* New: Continuous updates! Read more at https://managewp.com/continuous-updates

= 3.9.20 =
* New: ManageWP iOS app compatibility
* New: Perform security and performance test as you add websites
* New: New comment handling screen

= 3.9.19 =
* New: Improved mechanism for refreshing website stats. You should have fresh information every 4 hours without refreshing now
* Fix: Categories now showing properly in Manage posts
* Fix: Website stats now ignore uptime monitoring pings

= 3.9.18 =
* New: Pagelines themes added to the list of partners
* New: Comprehensive website performance scan tool
* New: You can now bulk edit posts/pages (updating that contact info will become piece of cake)
* New: Upload and save your premium plugins/themes in your personal repository for quick installation
* New: Run code snippets now get a repository. Save your snippets and share them with other users
* New: SEO reports can now be sorted. Export as CSV and PDF reports.
* New: Manage Blogroll links
* New: Clean post revisions now has an option to save last x revisions when cleaning
* New: Bulk delete na posts/pages/links
* Fix: Amazon S3 backups failing

= 3.9.17 =
* New: Add your favorite sites to the Favorites  bar (just drag&drop them to the small heart on the top)
* New: Entirely new website menu loaded with features and tools
* New: Manage Posts and Pages across all sites in a more efficient way
* New: Support for all WPMU.org premium plugin updates
* New: Complete Dropbox integration through Oauth which allows us to restore/delete Dropbox backups directly
* New: We have the user guide as PDF now. [Download] (https://managewp.com/files/ManageWP_User_Guide.zip)


= 3.9.16 =
* New: Option to "Run now" backup tasks
* New: Traffic alerts functionality
* New: Support for Genesis premium theme updates
* Fix: In some circutmsances .htaccess was not correctly zipped in the backup archive

= 3.9.15 =
* New: Full range of SEO Statistics now trackable for your websites (Google Page Rank and Page Speed, Backlinks and 20+ more)
* New: Google keyword rank tracking with history
* New: Uptime monitoring (5 min interval with email/SMS notification)
* New: Insights into server PHP error logs right in your dashboard
* New: Remote maintenance mode for your websites
* Fix: A bug when a completed backup was reported as failed

= 3.9.14 =
* Two factor authentication
* Run code tool
* Quick access to security check and broken link tools
* More accurate pageview statistics
* You can now opt to completely hide the Worker plugin from the list of plugins (part of Worker branding features)
* We improved the backups for folks running Windows servers
* Amazon S3 directory name now "ManageWP" by default
* Read more on ManageWP.com https://managewp.com/update-two-factor-authentication-run-code-tool-sucuri-security-check-more-accurate-pageview-statistics

= 3.9.13 =
* Added bucket location for Amazon S3 backups
* Better backup feature for larger sites
* Added Disable compression to further help with larger sites
* Backing up wp-admin, wp-includes and wp-content by default now, other folders can be included manually

= 3.9.12 =
* Minor bug fixes
* Backup, clone, favorites functionality improved

= 3.9.10 =
* Supporting updates for more premium plugins/themes
* Backup notifications (users can now get notices when the backup succeeds or fails)
* Support for WordPress 3.3
* Worker Branding (useful for web agencies, add your own Name/Description)
* Manage Groups screen
* Specify wp-admin path if your site uses a custom one
* Amazon S3 backups support for mixed case bucket names
* Bulk Add Links has additional options
* Better Multisite support
* Option to set the number of items for Google Analytics
* ManageWP backup folder changed to wp-content/managewp/backups

= 3.9.9 =
* New widget on the dashboard - Backup status
* New screen for managing plugins and themes (activate, deactivate, delete, add to favorites, install) across all sites
* New screen for managing users (change role or password, delete user) across all sites
* Option to overwrite old plugins and themes during bulk installation
* Your website admin now loads faster in ManageWP
* Added API for premium theme and plugin updates

= 3.9.8 =
* Conversion goals integration
* Update notifications
* Enhanced security for your account
* Better backups
* Better update interface
* [Full changelog](https://managewp.com/update-goals-and-adsense-analytics-integration-update-notifications-login-by-ip-better-backups "Full changelog")

= 3.9.7 =
* Fixed problem with cron schedules

= 3.9.6 =
* Improved dashboard performance
* Fixed bug with W3TC, we hope it is fully comptabile now
* Improved backup feature
* Various other fixes and improvements

= 3.9.5 =
* Now supporting scheduled backups to Amazon S3 and Dropbox
* Revamped cloning procedure
* You can now have sites in different colors
* W3 Total Cache comptability improved

= 3.9.3 =
* Included support for WordPress 3.2 partial updates

= 3.9.2 =
* Fixed problem with full backups
* Fixed problem with wordpress dev version upgrades

= 3.9.1 =
* Support for sub-users (limited access users)
* Bulk add user
* 'Select all' feature for bulk posting
* Featured image support for bulk posting
* Reload button on the dashboard (on the top of the Right now widget) will now refresh information about available updates
* Fixed a problem with the import tool
* Fixed a problem when remote dashboard would not work for some servers

= 3.9.0 =
* New feature: Up to 50% faster dashboard loading
* New feature: You can now ignore WordPress/plugin/theme updates
* New feature: Setting 'Show favicon' for websites in the dashboad
* New feature: Full backups now include WordPress and other folders in the root of the site
* Fixed: Bug with W3 TotalCache object cache causing weird behaviour in the dashboard
* Fixed: All groups now show when adding a site

= 3.8.8 =
* New feature: Bulk add links to blogroll
* New feature: Manual backups to email address
* New feature: Backup requirements check (under Manage Backups)
* New feature: Popup menu for groups allowing to show dashboard for that group only
* New feature: Favorite list for plugins and themes for later quick installation to multiple blogs
* New feature: Invite friends
* Fixed: problem with backups and write permissions when upload dir was wrongly set
* Fixed: problem adding sites where WordPress is installed in a folder
* Fixed: 408 error message problem when adding site
* Fixed: site time out problems when adding site
* Fixed: problems with some WP plugins (WP Sentinel)
* Fixed: problems with upgrade notifications

= 3.8.7 =
* Fixed 408 error when adding sites
* Added support for IDN domains
* Fixed bug with WordPress updates
* Added comment moderation to the dashboard
* Added quick links for sites (menu appears on hover)

= 3.8.6 =
* Added seach websites feature
* Enhanced dashboard actions (spam comments, post revisions, table overhead)
* Added developer [API] (https://managewp.com/api "ManageWP API")
* Improved Migrate/Clone site feature

= 3.8.4 =
* Fixed remote dashboard problems for sites with redirects
* Fixed IE7 issues in the dashboard

= 3.8.3 =
* Fixed problem with capabilities

= 3.8.2 =
* New interface
* SSL security protocol
* No passwords required
* Improved clone/backup

= 3.6.3 =
* Initial public release

== Installation ==

1. Create an account on [ManageWP.com](https://managewp.com/ "Manage Multiple WordPress Sites")
2. Follow the steps to add your first website
3. Celebrate!

Seriously, it's that easy! If you want more detailed instructions, check out our [User Guide](https://managewp.com/guide/getting-started/add-website-managewp-dashboard "Add your website to ManageWP")

== Screenshots ==

1. ManageWP dashboard with a thumbnail view of 20 websites
2. Tags and stars help you organize your websites
3. A summary of available updates and health of all your websites
4. Track your website performance regularly, so you could know right away if something goes wrong
5. Managing plugins and themes is just as easy with 100 websites as with 3 websites
6. Client Report is an executive summary of everything you've done for your client
7. Cloud backups with detailed information about each restore point
8. Uptime Monitor logs up and down events, and notifies you via email and SMS
9. Aside from being able to white label the ManageWP Worker plugin, you can also add a support form on the client's website

== Upgrade Notice ==

= 3.9.30 =
Worker plugin is now fully compatible with WordPress 4.0, adding websites is now easier and we have made fixes and improvements in Backup and Clone tools


= 3.9.29 =
Worker plugin is 36% faster and uses 83% less memory. Backup tool no longer relies on WordPress cron


= 3.9.28 =
It is now possible to control WordPress automatic background updates for plugins and themes!


= 3.9.27 =
We have added compatibility with Better WP Security. Also, it is now possible to backup and clone to SFTP


== License ==

This file is part of ManageWP Worker.

ManageWP Worker is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

ManageWP Worker is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with ManageWP Worker. If not, see <https://www.gnu.org/licenses/>.


== Frequently Asked Questions ==

= Is ManageWP free? =

ManageWP is using the freemium model. All the core features are free for an unlimited number of websites. And for those of you who need more, we have a set of premium features to help you out.

= Do you offer support for free users? =

Yes. No matter if you're free or premium user, we are here for you 24/7. Expect a 1h average response time and a 65% answer resolution in the first reply.

= How much do premium ManageWP features cost? =

Our pricing is highly flexible, we don't charge anything upfront. The usage is calculated on a per-website, per-addon basis, like Amazon Web Services. Check out our [pricing page](https://managewp.com/pricing "ManageWP pricing page") for more info.

= Is ManageWP secure? =

Yes. All of our code is developed in-house and we have a top notch security team. With half a million websites managed since 2012 we did not have a single security incident. We've accomplished this through high standards, vigilance and the help of security researchers, through our [white hat security program](https://managewp.com/white-hat-reward).

= I have websites on several different hosts. Will ManageWP work all of them? =

Yes. ManageWP plays nice with all major hosts, and 99% of the small ones.

= Does ManageWP work with multisites? =

Yes, multisite networks are fully supported, including the ability to backup and clone a multisite network.

= Does ManageWP work with WordPress.com sites? =

No. ManageWP works only with self-hosted WordPress sites.

= Worker plugin can connect to ManageWP and Pro Sites. What is the difference between the two? =

[ManageWP](https://managewp.com "ManageWP website") is focused on the hosting-agnostic WordPress website management. [Pro Sites](https://www.godaddy.com/pro "GoDaddy Pro Sites website") is the GoDaddy version of the service. It's part of the GoDaddy Pro program, which incorporates different tools for website & client management, lead generation, and tighter integration with other GoDaddy products.

= I have problems adding my site =

Make sure you use the latest version of the Worker plugin on the site you are trying to add. If you still have problems, check our dedicated [FAQ page](https://managewp.com/troubleshooting/site-connection/why-cant-i-add-some-of-my-sites "Add site FAQ") or [contact us](https://managewp.com/contact "ManageWP Contact").

= How does ManageWP compare to backup plugins like BackupBuddy, Backwpup, UpdraftPlus, WP-DB-Backup ? =

There is a limit to what a PHP based backup can do, that's why we've built a completely different backup - cloud based, incremental, it keeps working long after others have failed.

= How does ManageWP compare with clone plugins like Duplicator, WP Migrate DB, All-in-One WP Migration, XCloner ? =

These solutions are simple A-B cloning solutions that tend to break in critical moments. ManageWP does it more intelligently. We first upload the backup archive to a cloud infrastructure that we control, and then we transfer it to the destination website. This effectively compartmentalizes the process into two separate steps, making the whole cloning experience much more robust and stress free.

= Is Worker PHP7 compatible? =

Yes, ManageWP Worker is fully compatible with PHP7. We also have chunks of backward compatible code, that triggers in case you're still running PHP5.x - if your code check comes up with a compatibility flag, just ignore it.


Got more questions? [Contact us!](https://managewp.com/contact "ManageWP Contact")
