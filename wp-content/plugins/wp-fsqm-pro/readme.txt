=== eForm - WordPress Form Builder ===
Contributors: swashata, wpquark
Tags: form, quiz, survey, payment, woocommerce
Requires at least: 4.0.0
Tested up to: 4.8.1
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Gather feedbacks and run surveys on your WordPress Blog. Stores the gathered data in database. Displays the form & trends with shortcodes.

== Description ==

eForm is an advanced and flexible form builder that can be integrated into your existing WordPress site. This is a complete form management solution, for quizzes, surveys, data collection and user feedback of all kinds.

With the quick and easy drag and drop form builder, you can build unlimited forms and manage them from your admin dashboard. All submissions are stored in your eForm database, so you can view, track, analyze and act on the data you have captured. A user portal also allows registered users to review and track their submissions.

We have integrated eForm with the best in class e-mail newsletter providers and payment services, for even greater flexibility and security.

This robust and comprehensive form builder is the perfect combination of style and functionality: packed with all the elements you need, while clean and elegant to use.

== Installation ==

After you have downloaded eForm from codecanyon, install it as a manual plugin for the first time.

e.g.

1. Upload `wp-fsqm-pro` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Activate eForm from eForm > Settings with your purchase code and get auto updates.

== Frequently Asked Questions ==

= Where can I find documentation? =

Updated version of documentation can always be found at [WPQuark Knowledgebase](https://wpquark.com/kb/fsqm)

= I am stuck, where can I get support? =

Please visit our [support forum](https://wpquark.com/kb/support).

== Changelog ==

= 4.0.0 =

* **New** - Authorize.net payment integration
* **New** - Auto Update Functionality
* **New** - Automatic score for feedback elements
* **New** - Estimation Slider interface for payment forms
* **New** - Input masking on freetype form elements
* **New** - Interactive form elements support for piping element values into labels
* **New** - OpenGraph & Twitter metadata in standalone form pages
* **New** - Option to change color of summary table icons
* **New** - Pricing Table Form Element
* **New** - Row index for checkbox, radio and thumnail numeric values in math element
* **New** - Zoom for statistics charts
* **Update** - Better colorpicker for Form Builder
* **Update** - Better looking payment forms
* **Update** - Better Signature Element
* **Update** - Implement changes according to new facebook API
* **Update** - Inline appearance for feedback small element
* **Update** - iziModal in popup forms with support for better manual popup
* **Update** - jQuery UI Sliders are now more responsive
* **Update** - Leaderboard shows rank and timer value
* **Update** - Select2 styling is now consistent with inputs
* **Fix** - Auto fix bad color codes in customizable material theme
* **Fix** - Auto Save Form Progress UI inconsistency
* **Fix** - Cookies based limitation not working under IE11
* **Fix** - Hidden mathematical element appearance issue
* **Fix** - Issue with file upload size
* **Fix** - Issue with sort by name in payment listing
* **Fix** - Issue with User Portal page logout redirect
* **Fix** - Placeholder issue in multiple grading settings

= 3.7.5 =
* Fixed: Typo in the default process title
* Fixed: Empty space in login form
* Fixed: IE11 bug which wouldn't let thumbnail pickers work properly

= 3.7.4 =
* Added: Clear button for datetime pickers
* Added: Changable default year range in datepicker dropdown
* Added: Option to hide datepicker icon
* Fixed: Issue with toggle element and conditional logic under a special edge case
* Fixed: Issue with repeatable element and floating number values

== Upgrade Notice ==

Plugin updates are automatic starting version 4.0.0. You will not loose any of your data. But you may want to backup following tables, just to be sure.

* `wp_fsq_form` - Holds all your forms.
* `wp_fsq_data` - Holds all your submission data.
* `wp_fsq_files` - Holds information about uploaded files.
* `wp_fsq_category` - Holds form categories.
* `wp_fsq_payment` - Holds all your payment and invoice related data.
