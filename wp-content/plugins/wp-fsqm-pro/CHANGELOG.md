From version 4.0.0 this file will only contain changelog for the current active
version according to [semver](http://semver.org/). For older changelog, kindly
see version history.

## Version 4.0.1

> Quick patch for WordPress MultiSite.

### Changes

* **Fix** - Static database table naming issue with WordPress MS

--------------------------------------------------------------------------------

## Version 4.0.0

> Major code refactor to introduce modern workflow and features focused on payment
and cost estimation.

Many breaking API changes. Check the [DevOps](https://wpq-develop.wpquark.xyz/wp-fsqm-pro/)
page for more information.

### Changes

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

### Under the hood

* **New** - Adaptation to modern workflow with modular approach
* **New** - Grunt based CI/CD with support for automatic plugin updates for clients
* **New** - Payment module refactoring
* **New** - PHPUnit testing for a better continuous integration
* **New** - UI class refactoring
* **New** - Use bower to manage front-end dependencies
* **New** - Use composer to manage PHP dependencies
* **New** - Use NPM to manage dev dependencies

### File Changes

```
Too many to list. Do a clean install
```
