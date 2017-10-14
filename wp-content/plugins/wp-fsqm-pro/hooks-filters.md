# Information on eForm Hooks and Filters

This file serves as a list of updated hooks and filters and their location within eForm Plugin.

You can check the source code directly to find out their purpose. Also a developer's guide is available
at the end of this file.


#### Admin Classes (`/classes/class-ipt-fsqm-admin.php`)
* `ipt_fsqm_submission_deleted` - Hook
* `ipt_fsqm_submissions_deleted` - Hook
* `ipt_fsqm_form_deleted` - Hook
* `ipt_fsqm_forms_deleted` - Hook
* `ipt_fsqm_admin_format_options` - Hook
* `ipt_fsqm_tab_settings` - Filter
* `ipt_fsqm_all_forms_row_action` - Filter
* `ipt_fsqm_all_forms_row_action` - Filter

#### Base Class(`/classes/class-ipt-fsqm-form-elements-base.php`)
* `ipt_fsqm_default_settings` - FILTER
* `ipt_fsqm_form_element_structure` - FILTER
* `ipt_fsqm_valid_elements` - FILTER
* `ipt_fsqm_form_data_structure` - FILTER

#### Data Class (`/classes/class-ipt-fsqm-form-elements-data.php`)
* `ipt_fsqm_filter_data_errors` - Filter
* `ipt_fsqm_filter_save_error` - Filter
* `ipt_fsqm_filter_social_buttons` - Filter
* `ipt_fsqm_admin_email` - Filter
* `ipt_fsqm_user_email` - Filter
* `ipt_fsqm_hook_save_error` - Hook
* `ipt_fsqm_hook_save_insert` - Hook
* `ipt_fsqm_hook_save_update` - Hook
* `ipt_fsqm_hook_save_success` - Hook
* `ipt_fsqm_hook_save_fileupload` - Hook
* `ipt_fsqm_hook_integration` - Hook
* `ipt_fsqm_hook_core_integrations` - Hook
* `ipt_fsqm_form_elements_quick_preview_email_style` - Filter - Change email style.
* `ipt_fsqm_submission_db_elms` - Filter
* `ipt_fsqm_submission_lock_status` - Filter
* `ipt_fsqm_user_payment_email` - Filter
* `ipt_fsqm_user_payment_email_{$mode}` - Filter
* `ipt_fsqm_form_success_message` - Filter
* `ipt_fsqm_form_redirect_components` - Filter
* `ipt_fsqm_format_strings` - Filter

#### Static Class (`/classes/class-ipt-fsqm-form-elements-static.php`)
* `ipt_fsqm_shortcode_wizard` - Hook
* `ipt_fsqm_filter_static_report_print` - Filter
* `ipt_fsqm_payment_methods` - Filter

#### Utilities Class (`/classes/class-ipt-fsqm-form-elements-utilities.php`)
* `ipt_fsqm_filter_utilities_report_print` - Filter
* `ipt_fsqm_report_js` - Filter
* `ipt_fsqm_report_enqueue` - Hook to script and styles of the report generator (trends)

#### Form Admin Class (`/classes/class-ipt-fsqm-form-elements-admin.php`)
* `ipt_fsqm_form_updated` - Hook
* `ipt_fsqm_form_created` - Hook
* `ipt_fsqm_integration_settings_tabs` - Filter
* `ipt_fsqm_settings_core_tabs` - Filter

#### Upload Class (`/classes/class-ipt-fsqm-form-elements-uploader.php`)
* `ipt_fsqm_files_blacklist` - Filter
* `ipt_fsqm_files_error_messages` - Filter

#### Form Front Class (`/classes/class-ipt-fsqm-form-elements-front.php`)
* `ipt_fsqm_hook_form_before` - Hook
* `ipt_fsqm_hook_form_fullview_before` - Hook
* `ipt_fsqm_hook_form_doing_admin_before` - Hook
* `ipt_fsqm_hook_form_after` - Hook
* `ipt_fsqm_form_elements_front_enqueue` - Hook to script and styles of the front end
* `ipt_fsqm_payment_retry_types` -  Filter
* `ipt_fsqm_payment_retry_selections` - Filter
* `ipt_fsqm_payment_retry_form` - Hook

For complete information please visit [eForm Developer's Handbook](https://iptms.co/3)

