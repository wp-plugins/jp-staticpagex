=== Static Page eXtended ===
Contributors: jixor
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=stephen%40margni%2ecom&item_name=XSLT%20RSS&no_shipping=1&return=http%3a%2f%2fwww%2ejixor%2ecom%2fthankyou%2ehtm&cancel_return=http%3a%2f%2fjp%2ejixor%2ecom&no_note=1&tax=0&currency_code=AUD&lc=AU&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: page,pages,post,posts,php,redirect,includes
Requires at least: 1.5
Tested up to: 2.6.2

Inline PHP in posts and pages. Posts and pages can redirect based on category,
id and title. Pages may be replaced by external php scripts. Pages and posts may
define includes.



== Description ==

This plugin creates a variety of new functionallity.

Redirection features: Posts and pages may feature a meta redirect key which can
redirect the user to a post/page based on a specific id, the most recent in a
given category or the most recent with a given title. 

Replace feature: The contents of an entire page may be replaced with a
single PHP file. 

Include feature: You may use inline includes in your posts and pages to include
a php script or otherwise.

Inline PHP: You may write PHP directly into your posts and pages. However ensure
you read the instructions carefully before doing this. Most importantly inline
PHP must not echo or print output, all output must be returned. As the code is
is passed through eval() there are some other niggles too.



== Installation ==

1. Upload the jp-staticpagex plugin directory, along with its contents, to your
   worpress plugins directory: `/wp-content/plugins/`.
2. In your wp-content folder ensure that there is a folder anmed "staticpages"
   which is writable. (The plugin will however attempt to create this itself.)
3. Activate the plugin
4. Follow the instructions in the description; go to the options page and enable
   the features you want to use.



== Frequently Asked Questions ==

= My inline PHP is causing errors =

Inline PHP must not echo or print output, all output must be returned. Using
eval() can also create other problems, if you have a complex script consider
using an inline include or content replacement file.

= This plugin broke my caching =

The content replacement and inline include features are not compatible with
caching plugins.



== Screenshots ==

1. The options page
2. The content replacement files management page
