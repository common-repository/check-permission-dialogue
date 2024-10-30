=== Check Permission Dialogue ===
Contributors: danmz
Tags: tracking, cookies, analytics
Requires at least: 4.9.8
Tested up to: 6.3
Requires PHP: 7.2.0
Stable Tag: trunk
License: LGPLv3
License URI: https://www.gnu.org/licenses/lgpl-3.0.html

This plugin adds an opt-in permission for certain known tracking scripts and tracking cookies.  

== Description ==

Background/Motivation: Historically analytics and tracking have been used without explict user consent.  
Recently there has been a push to change this, both from a technical standpoint and from a legal standpoint.  
While analytics are recognized to be useful to website owners, users should be aware that they are being tracked, and have the option to avoid this tracking.  

This plugin aims to make it easy and simple to get tracking/analytics consent from users, and respect the users' stated preferences for the most common tracking scenarios.  
The user's preferences are stored for the duration of their browser session. 


This plugin removes known tracking scripts (google, facebook, and crazyegg) from generated markup until a user explicitly opts in to allow their use.  
This applies to all markup prior to `wp_footer();`.  Due to limitations of WordPress hooks we cannot filter content after this.  
This plugin attempts to ONLY block markup whose stated purpose is tracking (such as items from the googletagmanager.com domain), and not other items which might potentially do tracking.  

This plugin also clears known tracking cookies associated with those known trackers for users who have not opted in to tracking functionality.  

== Installation ==

1. Upload the plugin archive file to the `wp-content/plugins` directory in your WordPress installation.
1. De-compress (un-zip or un-tar) the plugin archive file, ensuring that the resulting `chk-perm-dialog` directory resides in the `wp-content/plugins` directory in your WordPress installation.  
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. (optional) If you want to provide a link to allow users to clear settings, ensure that somewhere on your site includes the following shortcode: `[chk_perm_clear_link]`

== Frequently Asked Questions ==

= Does this plugin provide any guarantees or warranties? =

NO!  Tracking scripts are subject to change without notice, and we cannot guarantee that google and other organizations will not change their code in such a way that this plugin no longer works as intended.  

= Does this plugin make me GDPR compliant? =

Not on its own, no.  
If correctly configured, this can be one component of a GDPR-compliant site, but it is up to the website owner to know what data is collected, to know your responsibilities under the GDPR, and to verify that everything functions as required.  
Most websites collect additional data such as form submissions, which is outside of the scope of this plugin.  
We WILL NOT and CANNOT gaurantee that every site that uses this plugin is GDPR compliant.  

= Can I configure this only for particular browsers, particular connections, particular locations, etc. ? =

No.  This plugin treats all users equally and we will never accept anything upstream that changes that.

= Why isn't a dialog appearing even though the plugin is enabled? =

Assuming you have javascript enabled, the dialog will not appear if your website doesn't actually use any trackers that this plugin is intended to block.    
This is intentional, as there's no point in asking for permission that you will not need from your users.  
The dialog should start appearing once you add google or facebook tracking scripts to your site.  


== Screenshots ==

1. Chrome on desktop
1. Firefox on desktop
1. iOS
1. Android

== Changelog ==

= 2023.08 =
* Tested in wordpress 6.3

= 2022.06 =
* Tested in wordpress 6.0

= 2022.01 =
* Tested in wordpress 5.9
* Updates to support wordpress 5.9 block editor and API changes
* Fixed bug where GET data that was previously-set wasn't preserved after dialog is closed

= 2021.08 =
* Tested in wordpress 5.8
* Changed allow/deny buttons to use GET data instead of POST data (and the corresponding server-side changes)
* Changed clear permission links to use GET data instead of POST data (and the corresponding server-side changes)
* Added progressive enhancement javascript so that users who have javascript enabled don't see GET data in their urls (still works fine without javascript).  

= 2021.03 =
* Tested in wordpress 5.7
* Updated button styles to prevent theme default styles from setting button background color in the permission dialog as easily

= 2020.12 =
* Tested in wordpress 5.6
* Minor styling update for broader theme compatibility

= 2020.10 =
* Tested in wordpress 5.5.1

= 2020.04 =
* Tested in wordpress 5.4
* Minor styling updates to over-ride defaults in the twentytwenty theme

= 2020.01 =
* Tested in wordpress 5.3.2
* Updated facebook trackng cookie list to blacklist more cookie prefixes

= 2019.11 =
* Tested in wordpress 5.3

= 2019.07 =
* Tested in wordpress 5.2.2

= 2018.12 =
* Tested in wordpress 5.0
* Changed styling units from rem to px because wordpress's twentynineteen theme breaks rem units hard

= 2018.10 =
N/A (this is the first release)


