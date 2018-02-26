=== Simple Lead Gen Form ===
Contributors: Sofyan Sitorus
Tags: lead-form
Requires at least: 4.4
Tested up to: 4.9.4
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Simple Lead Gen Form Plugin for WordPress

== Description ==
A Simple Lead Gen Form Plugin for WordPress. 

The form has 5 built-in fields:

1. Full Name
1. Phone Number
1. Email Address
1. Desired Budget
1. Message

= How to use =

To display the form, simply place a shortcode into a page or post with tag:

[slgf_lead_form]

The form appearence can be modified by adding attributes as follow:

= full_name_label =
Customize the filed label for Full Name field.

[slgf_lead_form full_name_label="Name"]

= full_name_attrs =
Customize the attributes 'maxlength', 'placeholder' for Full Name field. Each attribute separated using semicolon and key value attribute paired with colon. 

[slgf_lead_form full_name_attrs="maxlength:100;placeholder:Write your name"]

= phone_number_label =
Customize the filed label for Phone Number field.

[slgf_lead_form phone_number_label="Phone"]

= phone_number_attrs =
Customize the attributes 'maxlength', 'placeholder' for Phone Number field. Each attribute separated using semicolon and key value attribute paired with colon. 

[slgf_lead_form phone_number_attrs="maxlength:100;placeholder:Write your phone"]

= email_address_label =
Customize the filed label for Email Address field.

[slgf_lead_form email_address_label="Email"]

= email_address_attrs =
Customize the attributes 'maxlength', 'placeholder' for Email Address field. Each attribute separated using semicolon and key value attribute paired with colon. 

[slgf_lead_form email_address_attrs="maxlength:100;placeholder:Write your email"]

= desired_budget_label =
Customize the filed label for Desired Budget field.

[slgf_lead_form desired_budget_label="Budget"]

= desired_budget_attrs =
Customize the attributes 'maxlength', 'placeholder' for Desired Budget field. Each attribute separated using semicolon and key value attribute paired with colon. 

[slgf_lead_form desired_budget_attrs="maxlength:100;placeholder:Write your budget"]

= message_label =
Customize the filed label for Message field.

[slgf_lead_form message_label="Note"]

= message_attrs =
Customize the attributes 'maxlength', 'placeholder', 'cols', 'rows' for Message field. Each attribute separated using semicolon and key value attribute paired with colon. 

[slgf_lead_form message_attrs="maxlength:1000;cols:100;rows:20;placeholder:Write your note"]

= submit_button =
Customize the form submit button.

[slgf_lead_form submit_button="Send Message"]

== Installation ==
1. Upload the entire "/slgf" directory to the "/wp-content/plugins/" directory.
2. Activate Simple Lead Gen Form through the "Plugins" menu in WordPress.

== Changelog ==
= 0.0.1 =
 * First Release

== Upgrade Notice ==
= 0.0.1 =

First Release