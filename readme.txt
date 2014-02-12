=== Server Status ===
Contributors: extendwings,
Donate link: http://www.extendwings.com/donate/
Tags: uptime, load average, server, linux, status, dashboard, multisite, network
Requires at least: 3.8
Tested up to: 3.9-alpha-27111
Stable tag: 0.1.2
License: AGPLv3 or later
License URI: http://www.gnu.org/licenses/agpl.txt

Show server information widget in Dashboard and Network Admin Dashboard.(Currently, only RHEL is tested)

== Description ==

*Do you want to monitor your server without using SSH?*

*Don't you know how to use difficult commands? (looks like a spell!)*

> **OK! Leave all to this plugin!**

"Server Status" adds widget like 'uptime' command in Dashboard and Network Admin Dashboard.

= Notice =
* **Currently, only RHEL/CentOS is tested.** And OS X is tesing now! (The number of tested OS will increase shortly.)
* **PECL Zend OPcache users**, *please add server-status.php* to black list! Otherwise, there must be segmentation fault.
	This troublesome process isn't required on PHP5.5!

== Installation ==

1. Upload the `server-status` folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Why doesn't this plugin work with more server OS? =

I don't have enough money&time to adapt this plugin to these OSs.
Any `worked` report and bug report is welcome! Please send me detailed information.

== Screenshots ==

1. First Access (Without cached data)
2. Second Access (With cached data)

== Changelog ==

= 0.1.2 =
* Minor Bug Fix: Not working before PHP 5.4.0

= 0.1.1 =
* Bug Fix: Not working before PHP 5.4.0

= 0.1 =
* Initial Beta Release

== Upgrade Notice ==

= 0.1.1 =
* None

= 0.1 =
* None

== Arbitrary section ==

No content.
