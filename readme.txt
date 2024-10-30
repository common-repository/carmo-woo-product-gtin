=== Carmo Product GTIN for WooCommerce ===
Contributors: carmopereira
Donate link: https://ko-fi.com/carmopereira
Tags: gtin, ean, upc
Requires at least: 6.0
Tested up to: 6.6.2
Stable tag: 1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin will add a numeric GTIN field to Simple Products and Product Variation if they exist. 
This field can be used via shortcode [carmogtin] on product pages and for product feeds.

== Description ==

This plugin adds a numeric GTIN field to Simple Products and Product Variations if they exist. This field can be used on product pages and for product feeds through shortcode.
Additionally, the plugin provides an options menu where users can delete all GTIN entries from the database before uninstalling the plugin.

== Screenshots ==

1. Example of the field GTIN on variable products.
2. Example of the field GTIN on simple products.

== Frequently Asked Questions ==

= Can I add different lengths of GTIN?  =

Yes. The field will accept any conventional GTIN length. You can use either EAN, UPC(13) or even GTIN-8 (EAN-8), GTIN-12 (UPC with 12 digits) and GTIN-14 (ITF-14).

= How can I use it on my product page?  =

Just use the shortcode [carmogtin]. If there's a value it will output that same value. If not, the result will be empty.

= Who can I use it on my product feed?  =

If you use some plugin to output product feeds, just search carmogtin field. This was tested through Product Feed Pro (version free).
This will only work if you have at least 1 product with a GTIN code saved.

== Changelog ==

= 1.0 =
* First Version.