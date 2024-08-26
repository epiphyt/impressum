=== Impressum ===
Contributors: epiphyt, kittmedia, krafit
Tags: impressum, legal notice, imprint, privacy policy
Requires at least: 5.0
Stable tag: 2.0.5
Tested up to: 6.6
Requires PHP: 5.6
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Impressum provides you with a full-fledged easy to use imprint generator right within your WordPress site.

== Description ==

Impressum adds a full-fledged and easy to use imprint generator in your WordPress dashboard. Once setup, Impressum takes care of your legal notices. Once legal requirements change, Impressum will update your legal content either on its own or asks for your help if the changes can't be made automatically. Your imprint is generated right within WordPress, so your personal information won't be sent to a third party server.

### Impressum Plus

Impressum Plus is the pro version of Impressum, which not only adds support for a wide variety of additional legal entities, but also provides a modular privacy policy and the opportunity to update your data via an extensive REST API endpoint.

#### All features of Impressum Plus

* Support for a wide variety of legal entities (AG, e.K., e.V., eG, Einzelkaufmann, GbR, gGmbH, GmbH, GmbH & Co. KG, KG, KGaA, OHG, Partnership, UG (haftungsbeschränkt) and UG (haftungsbeschränkt) & Co. KG)
* a feature rich and automated Privacy Policy generator
* Preset for new sites in a multisite
* Extensive REST API
* Many more filters for developers

[Buy now](https://epiph.yt/en/product/impressum-plus/)

[Checkout all features of Impressum Plus](https://impressum.plus/en/features/)


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/impressum` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the "Plugins" screen in WordPress.
1. Use the **Settings > Impressum** screen to configure the plugin.
1. Add the "Imprint" block wherever you want to output your imprint.


== Frequently Asked Questions ==

= Why would I need an imprint on my website? =

In certain countries like Germany and Austria, the law requires some website owners to add their contact information to their sites.

= But there are online generators for that =

Yes, and they are great. But adding an imprint generator directly to your WordPress install makes it pretty easy for you to stay up to date.

= How do I use Impressum? =

After you install and activate Impressum we will kindly ask you to populate some form fields with all the data you are legally required to add to your imprint. Impressum will guide you through this process.

By adding the "Imprint" block anywhere on your site, you can choose where to output your imprint. Alternatively, you can use the shortcode `[impressum]`.

After this initial setup, Impressum just sits in your installation and serves your imprint. Once legal requirements change, Impressum will notify you about necessary changes.

= Can I really use this plugin free of charge? =

Yes. Impressum is and always will be available for free. However, if your site is legally operated by a legal entity who's not an individual person, you must install _Impressum Plus_, in order to use all fields necessary for your legal entity. Get it from here: [https://impressum.plus/en/](https://impressum.plus/en/).

= Does Impressum share any of my data? (GDPR) =

No. Impressum keeps all your data inside your own WordPress install. There is no data transmitted to us or a third party service. But of course the "Imprint" block or the '[impressum]' shortcode will be displayed publicly on your website.

= Is Impressum compatible with the WP block editor "Gutenberg"? =

Yes. Since version 2.0.0 there is a block "Imprint" for this exact purpose.

= Who are you folks? =

We are [Epiphyt](https://epiph.yt/en/), your friendly neighborhood WordPress plugin shop from southern Germany.

= How can I report security bugs? =

You can report security bugs through the Patchstack Vulnerability Disclosure Program. The Patchstack team help validate, triage and handle any security vulnerabilities. [Report a security vulnerability.](https://patchstack.com/database/vdp/impressum)

== Screenshots ==

1. Manage your imprint with Impressum's easy to use interface.
2. Add the "Imprint" block wherever you want to output your imprint.
3. Your imprint will be formatted for you. Change its design with just a couple of lines of CSS.

== Changelog ==

= 2.0.5 =
* Improved: Made it more clear that you can also use the "Imprint" block in the block editor
* Improved: The comparison table between Impressum and Impressum Plus is now accessible

= 2.0.4 =
* Fixed a PHP notice in PHP 8.2

= 2.0.3 =
* Added legal entity eG (requires _Impressum Plus_ to be usable)
* Fixed loading translations for the block editor

= 2.0.2 =
* Fixed PHP error due to missing file

= 2.0.1 =
* Maintenance update

= 2.0.0 =
* Added imprint block
* Allow setting the imprint page
* Complete code refactoring
* Add comparison table for _Impressum Plus_
* Add possibility to output fields without title
* Small user interface improvements
* Fixed removing the welcome message

= 1.0.4 =
* Added new select for "Partnership" legal entity.
* Removed “according to § 55 paragraph 2 RStV” for the responsible person as it doesn’t reflect the correct legal base anymore (it’s been covered by § 18 Abs. 2 MStV now).

= 1.0.3 =
* Improved legal entity
* Fixed issue causing the welcome panel to be misformatted on mobile devices.

= 1.0.2 =
* Removed the check if a VAT ID is entered. It is based on sales, not on a legal entity.
* Fixed a design issue that leads to overlapped welcome panel by any dismissible notice.

= 1.0.1 =
* Fixed a potential fatal error if Impressum and Impressum Plus are both enabled, ❤️ [@drivingralle](https://profiles.wordpress.org/drivingralle)

= 1.0.0 =
* Improved admin notices
* Improved user experience for first time users
* Improved code structure (nerd stuff: namespaces, wrapper classes, and a bit of a cleanup)
* Improve compatibility with [Impressum Plus](https://impressum.plus/en/)
* Added structured country data
* Added check for required fields per legal entity

And a final thank you to our friends [@pixolin](https://profiles.wordpress.org/pixolin) and [@mahype](https://profiles.wordpress.org/mahype) for their support and great feature suggestions. 🤗

= 0.2 =
* Added default `mailto://` links for mail adresses
* Added a couple of descriptive texts
* Added "self-employed" as a new option
* Added VAT ID checkup
* Added a notice in case no field all fields are left empty
* Added a nice reminder to use the `[impressum]` shortcode
* Added a disclaimer to remind everyone, we can't guarantee legal compliance
* Fixed some additional small bugs 

Thanks [@pixolin](https://profiles.wordpress.org/pixolin) & [@zodiac1978](https://profiles.wordpress.org/zodiac1978) for testing v0.1 and suggesting most of these new feature. ❤️

= 0.1.1 =
* Fixed typos, ❤️ [@florianbrinkmann](https://profiles.wordpress.org/florianbrinkmann)

= 0.1 =
* Initial release

== Upgrade Notice ==
