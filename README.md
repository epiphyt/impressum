=== Impressum ===
Contributors: epiphyt, kittmedia, krafit
Tags: impressum, legal notice, terms
Requires at least: 5.0
Tested up to: 6.1
Requires PHP: 5.6
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Impressum provides you with a full-fledged easy to use imprint generator right within your WordPress site.

== Description ==

_Impressum_ adds a full-fledged and easy to use imprint generator in your WordPress dashboard. Once setup, _Impressum_ takes care of your legal notices. Once legal requirements change, _Impressum_ will update your legal content either on its own or asks for your help if the changes can't be made automatically. Your imprint is generated right within WordPress, so your personal information won't be sent to a third party server.


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

After you install and activate _Impressum_ we will kindly ask you to populate some form fields with all the data you are legally required to add to your imprint. _Impressum_ will guide you through this process.

By adding the "Imprint" block anywhere on your site, you can choose where to output your imprint. Alternatively, you can use the shortcode `[impressum]`.

After this initial setup, _Impressum_ just sits in your installation and serves your imprint. Once legal requirements change, _Impressum_ will notify you about necessary changes.

= Can I really use this plugin free of charge? =

Yes. _Impressum_ is and always will be available for free. However, if your site is legally operated by a legal entity who's not an individual person, you must install _Impressum Plus_, in order to use all fields necessary for your legal entity. Get it from here: [https://impressum.plus/en/](https://impressum.plus/en/).

= Does Impressum share any of my data? (GDPR) =

No. _Impressum_ keeps all your data inside your own WordPress install. There is no data transmitted to us or a third party service. But of course the '[impressum]' shortcode will be displayed publicly on your website.

= Is Impressum compatible with the WP block editor "Gutenberg"? =

Yes. Since version 2.0.0 there is a block "Imprint" for this exact purpose.

= Who are you folks? =

We are [Epiphyt](https://epiph.yt/en/), your friendly neighborhood WordPress plugin shop from southern Germany.

== Screenshots ==

1. Manage your imprint with _Impressum_'s easy to use interface.
2. Add the "Imprint" block wherever you want to output your imprint.
3. Your imprint will be formatted for you. Change its design with just a couple of lines of CSS.

== Changelog ==

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
* Added default `mailto://` links for mailadresses
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
