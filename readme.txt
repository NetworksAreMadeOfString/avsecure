=== AvSecure for Wordpress ===
Contributors: contributors
Donate link: http://site.com
Tags: check age, access to data
Requires at least: 4.8
Requires PHP: 5.6
Tested up to: 4.9.1
Stable tag: trunk

Age verification plugin for providing access to the site page with "restricted content".

== Description ==

The plugin starts its work immediately after installation and activation on any site running WordPress.
At the first visit to the site page (with "restricted content") where  the plugin is installed, a redirect to the authentication page takes place.
After successful registration/authorization, the user of the site receives an age token, which will automatically be added to all internal links of the site.
Next, the user will be redirected  to the original page with a token in the URL.
Now the pages of the site are available to the user who has passed an age test.

== Installation ==

1. Copy the <strong> avsecure </strong> plugin's folder in <strong>/wp-content/plugins/</strong>.
2. Activate the plugin through the menu<strong> Plugins</strong>.

== Upgrade Notice ==

Replace plugin files with new ones.

== Screenshots ==

1. Authentication page
2. Authorization page

== Changelog ==

= 1.1 =
* Code optimization
= 1.0 =
* First version