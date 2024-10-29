=== Riverty Payments for Woocommerce ===
Contributors: afterpay
Tags: afterpay, riverty, bnpl, payment, woocommerce
Requires at least: 4.5.0
Requires PHP: 5.6
Tested up to: 6.6.2
Stable tag: 7.1.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Author URI: https://developer.riverty.com

Riverty is the most consumer-friendly BNPL payment method in Germany, Austria, Switzerland, the Nordics, Netherlands and Belgium. Add Riverty to your WooCommerce storefront with this plugin! Riverty is the new AfterPay.

== Description ==

With a growing range of Buy now, pay later (BNPL) payment solutions, Riverty offers you and your online shoppers an outstanding webshop experience. You get simple and fast integration plus the guarantee of being paid, while your customers get more freedom, safety and security. It’s win-win for you and your customers.

Riverty makes you be even more successful by optimizing your payment method mix and helping consumers complete their checkout. For example, Riverty’s Buy now, pay later offering meets a growing need, as 25% of European online shoppers have already used this payment solution, and 50% confirm they are considering doing so in the future.

[Get started with Riverty Payments](https://www.riverty.com/en/business/products/get-started-riverty-buy-now-pay-later/)

= Riverty is the new AfterPay  =

Riverty offers a totally new perspective on financial sustainability, as it now includes all our smart and contemporary payment and receivables management solutions. By starting from the real needs and financial wellbeing of consumers, we enable merchants, partners and consumers to live their best financial life in a rapidly changing world.

= The benefits of choosing Riverty =

= Fast & easy set-up =

Are you ready for Riverty payment solutions? Follow the straight-forward instructions and receive confirmation quickly. Follow our installation and setup guidelines to get started.

= Payment solutions designed for you =

Seamless integration with your webshop is guaranteed as our plugin is designed for your platform.

= A direct connection to Riverty with no PSP in between =

With a direct connection to Riverty, you have full control of the integration. You will also receive regular updates concerning support, functionality and security.

= Lifetime support =

If ever you need help, we’re there to help you. Got a question: get in touch and we will sort you out as fast as we can.

= Easy order management (capturing & refunding) = 

Riverty’s smart order management process makes it easy to manage payments and refunds of items paid with Riverty. By easily capturing previously authorized funds, voiding an unsettled transaction, or doing a full or partial refund of a settled transaction, Riverty’s order management system enables you to manage all transactions that happen for an order.

= Always know what’s going on =

Our merchant dashboard helps you keep track of orders, returns and invoices.

= Your brand in the spotlight =

While our consumer-focused tools; the Riverty app (Google Play Store and Apple App Store) & MyRiverty offer more freedom and transparency to shoppers, they also ensure you as a merchant have more opportunities and touchpoints to interact with your shoppers as your brand remains in the spotlight.

= A broad range of payment methods =

With our seamless integration you can quickly get started with the payment options you want to offer (depending on the countries your webshop operates in).

* **14-day invoice** - Available in every market, 14-day Invoice is the default payment method of Riverty. It is primary designed for business-to-consumer sales.
* **Instalments** - This is a Part Payment invoice. The customer can split their purchase over multiple monthly payments.
* **Direct Debit** - This is a Direct Debit version of the 14-day invoice payment method. The consumers IBAN is collected at time of purchase in the check-out and the purchase amount is then debited from the consumer's bank account once the goods have been delivered.
* **Campaign invoice** - During peak seasons, such as Christmas, Black Friday, etc, merchants may use campaign invoicing. This gives shoppers extended payment terms per purchase with either fixed date (such as: 15. January) or floating (such as: in 60 days).
* **B2B invoice** - This is the the Business to Business (B2B) version of the default 14-day invoice payment method of Riverty.
* **Flex** - Flex offers to spread payments to the customers’ desired pace. Giving customers the ultimate freedom of payment customization. With this payment option consumers can adjust at any moment how and when they pay.
* **Pay in 3** - Particularly popular with younger, financially stable audiences buying premium value goods, Pay in 3 splits the check-out amount into 3 interest-free payment parts for completion within 90 days.

Riverty Payments are available in the following markets:

* Germany
* Austria
* Switzerland
* Netherlands
* Belgium
* Denmark
* Sweden
* Norway
* Finland.

== Installation ==

1. Install directly through the WordPress Plugins screens or upload the plugin files to the `/wp-content/plugins/plugin-name` directory.
2. Activate in the 'Plugins' screen in WordPress.
3. Go to Woocommerce->Settings->Checkout to configure the Riverty payment methods.

[Contact us](https://www.riverty.com/en/business/products/get-started-riverty-buy-now-pay-later/) to sign up and get your test and live credentials.

== Changelog ==

2024.09.17 - version 7.1.6

* Removed profile tracking checkbox at checkout.
* Resolved issue with deprecated php function used for shipping data.
* Removed sandbox environment from admin panel.
* Installed new php sdk library for connecting to riverty api. 
* Furthermore did compatibility tests to ensure that the Riverty plugin is fully compatible with the most recent WordPress and Woocommerce versions.

2024.08.21 - version 7.1.5

* Resolve issue with guzzlehttp fatal errors.
* We have added dynamic code of conduct text at netherlands to be retrieved from APM call instead of static text that was previously used.
* Furthermore did compatibility tests to ensure that the Riverty plugin is fully compatible with the most recent WordPress and Woocommerce versions.

2024.08.07 - version 7.1.4

* Resolve issue with difference between billing and delivery address at B2B transactons.
* Updated Riverty PHP SDK to version 4.5.0.

2024.07.10 - version 7.1.3

* Adjust Riverty logo style at checkout.
* Resolve issue with preloader icon not visible when triggering api key validation at the admin panel configuration.
* Furthermore did compatibility tests to ensure that the Riverty plugin is fully compatible with the most recent WordPress and Woocommerce versions.

2024.05.13 - version 7.1.2

* Resolved issue with fatal error on plugin activation.
* Adjusted hiding and showing capture config settings
* Updated profile tracking text visible to users at checkout and profile tracking scripts subdomain for new installations.
* Updated Riverty PHP SDK to version 4.4.0.
* Furthermore did compatibility tests to ensure that the Riverty plugin is fully compatible with the most recent WordPress and Woocommerce versions.


2024.01.13 - version 7.1.1

* Added code of conduct text to netherlands payment methods.
* Added support for Woocommerce High Performance Order Storage.
* Updated T&C as well as privacy statements and links for netherlands payment methods.
* Updated profile tracking text visible to users at checkout.
* Updated Riverty PHP SDK to version 4.2.0.
* Furthermore did compatibility tests to ensure that the Riverty plugin is fully compatible with the most recent WordPress and Woocommerce versions.

2023.11.01 - version 7.1.0

* In this release we have added Profile tracking to our Woocommerce plugin, and have an extra layer of security in the checkout.
* Furthermore did compatibility tests to ensure that the Riverty plugin is fully compatible with the most recent Woocommerce version.

2023.06.21 - version 7.0.0

* Compatibility with WordPress 6.2.2 and WooCommerce 7.8.0 ensured.
* Improved support for the default local pickup point shipping method in WooCommerce.
* Updated Riverty PHP SDK to version 4.0.0.

2023.05.09 - version 6.9.0

New features for the Nordics (Sweden, Norway, Finland and Denmark):

* Added the 'Campaign Invoice' payment method.
* Added the 'Business 2 Business' payment method.
* Resolved an issue where some installment options were not visible.

Compatibility testing:

* Tested compatibility with Woocommerce versions 7.4.1, 7.5.1, 7.6.0, and 7.6.1.
* Tested compatibility with Wordpress version 6.2.

Configuration improvements:

* Resolved a minor issue with advanced settings in the plugin.
* Added an explanation to the 'Refund tax percentage' configuration field.
* Improved the explanation for the 'Exclude shipping methods' configuration field.

2023.05.08 - version 6.8.0

* Enhanced validation error messages in checkout for improved usability across all supported languages.
* Updated payment titles for B2B payment methods in the Netherlands and Germany to provide clearer payment identification.
* Tested and confirmed compatibility with Woocommerce version 7.4.0.
* Resolved deprecation issues for PHP version 8.1 to ensure the plugin is up to date with current standards.
* Improved the configuration of the payment method by adding more detailed explanations for enhanced user understanding.

2023.02.24 - version 6.7.1

* We have fixed an issue with the configuration screen of legacy SOAP payment methods.

2023.02.22 - version 6.7.0

* We have changed the terms and conditions text for the Pay-in-3 payment method. This is now retrieved from the Riverty API.
* We have added a button to check the validity of the API keys, to take away the doubt if an API key is working.
* We have fixed an issue with wrong currency signs being shown in the fixed installment payment method in Norway.

2023.02.20 - version 6.6.2

* We have checked compatibility with the latest version of WordPress (6.1.1).
* We have checked compatibility with the latest version of WooCommerce (7.3.0).
* We have checked and fixed the compatibility and responsiveness of payment methods on mobile and tablet.
* We have updated the Riverty PHP SDK to version 3.9.0.
* We have updated the endpoints of the Riverty REST API.
* We have checked and fixed the compatibility with the Custom Order Numbers plugin (version 1.4.0 by Tyche Softwares).
* We have translated the descriptions for 'Discount' and 'Shipping' that are used in the order information of the transaction.
* We have changed AfterPay into Riverty on the Debug Email feature and into the plugin provider information.
* We have improved the error messages in the authentication process, especially when the SCA process is used.
* We have made the configuration of payment methods more user-friendly.
* We have fixed an issue with the installments payment method regarding links for more information.
* We have improved the user experience for the Pay-in-3 payment method by adding a Riverty element showing the offering.

2022.11.18 - version 6.5.0

* DP-1253 - We have added the new Pay in 3 payment method for the Netherlands.
* DP-1330 - We have checked the compatibility with the latest version of Wordpress 6.1
* DP-1341 - We have checked the compatibility with the latest version of Woocommerce 7.1
* DP-1356 - We have updated to the latest version of the Riverty PHP SDK 3.8.0.
* DP-1267 - We have updated the backend experience for the Riverty branding.

2022.11.03 - version 6.4.3

* DP-1324 - We have fixed an issue with a validation error on the terms and conditions.
* DP-1323 - We have removed the functionality of the terms and conditions opening in a modal box.
* DP-1317 - We have removed the GuzzleHttp library from the vendor folder, to avoid compatibility issues.
* DP-1303 - We have updated the checkout experience for the Riverty branding.

2022.10.26 - version 6.4.2

* DP-1308 - We have fixed an issue with the legal information shown in the checkout.
* DP-1304 - We have removed the checkboxes for the terms and conditions in NL and BE on legacy SOAP connections.

2022.10.12 - version 6.4.1

* DP-1295 - We have fixed an issue with using PHP functions that are PHP 8 only.

2022.10.05 - version 6.4.0

* DP-1254 - We have tested the compatibilty with Woocommerce 6.9.2
* DP-1238 - We now use the legal information (terms and conditions) from available payment methods API call
* DP-1233 - We have added the country code to the available payment methods call
* DP-1229 - We have updated the plugin to PHP Library version 3.7.0
* DP-1216 - We have fixed an javascript issue with Wordpress version 6.0.1
* DP-1184	- We have fixed a translation issue for the Date of birth.

2022.09.19 - version 6.3.0

* DP-1136 - Implementation of available payment methods for brand change in the checkout.
* DP-1194 - Brand change for SOAP payment methods.
* DP-1115 - Added plugin provider details to API calls.
* DP-1174 - Update translations.
* DP-1210 - Checked compatiblity with Wordpress 6.0.2

2022.09.12 - version 6.2.0

* DP-1205 - Checked compatibility with Woocommerce 6.8.2 and Wordpress 6.0.1
* DP-1159 - Checked compatibility with Woocommerce 6.7.0
* DP-1099 - Updated the latest version of the PHP Library (3.6.0)
* DP-836  - Optimized compatibility with the One Page Checkout

2022.07.12 - version 6.1.1

* DP-1039 - Disabled AfterPay Elements Tab

2022.07.11 - version 6.1.0

* DP-959 - Checked compatibility with Wordpress 6.0
* DP-857 - Updated to the latest version of the AfterPay PHP Library (3.5.1)
* DP-947 - Updated to the latest version of the AfterPay PHP Library (3.5.3)
* DP-961 - Checked compatibility with Woocommerce 6.6.1
* DP-962 - Changed the titles of the payment methods to uniform standard
* DP-1038 - Changed the order of the payment methods
* DP-963 - Added extra information to the order confirmation / 'thank you' page
* DP-964 - Removed the gender fields
* DP-1042 - Changed the styling of the terms and conditions
* DP-1049 - The terms and conditions will now open in a modal window to improve the checkout proces.
* DP-965 - Added the possibility for merchant specific terms and conditions
* DP-968 - Added payment methods Direct Debit and Business 2 Business for the Netherlands.
* DP-1039 - Added support for AfterPay Elements.
* DP-1046 - Added a box with USP's per payment method.
* DP-1047 - Added an intro text to guide the customer into the form fields.
* DP-1048 - Updated the bank account field, with field validation and feedback to improve the user experience.

2022.03.08 - version 6.0.0

* DP-881 - Comply to requirements Wordpress.

2022.02.03 - version 5.9.0

* DP-832 - Fix issue with Flex payment methods in the Nordics.
* DP-871 - Checked compatibility with Wordpress 5.9 and Woocommerce 6.1.1

2022.01.17 - version 5.8.0

* DP-760 - Updated payment methods in DACH (installments + birthday field)
* DP-760 - Updated birthday field in the Netherlands and Belgium.
* DP-815 - Removed Digital Invoice Extra for Germany.

2021.11.04 - version 5.7.0

* DP-800 - Fixed issue with conflicting GuzzleHttp libraries.
* DP-790 - Fixed issue with image urls using variables, which are not allowed by the AfterPay API.
* DP-793 - Fixed issue with frontend validation on birthdate, when nothing entered.
* DP-789 - Fixed issue with too much decimals in vatAmount.

2021.11.01 - version 5.6.0

* DP-789 - Fixed issue with too much decimals in vatAmount.

2021.09.30 - version 5.5.0

* DP-775 - Fixed issue with overwriting settings.
* DP-773 - Removed BIC from bank account validation, direct debit and installments.
* DP-773 - Updated PHP Library to version 3.4.0.
* DP-772 - Update styling to new logo.
* DP-786 - Added plugin data fields in the 'authorize' and 'available payment methods' calls.
* DP-782 - Added Date of Birth to all payment methods for DE, AT, CH, DK, NL and BE.

2021.09.20 - version 5.4.0

* DP-681 - Add compatibility for NL Shipping methods to REST payment methods.
* DP-761 - Filter active payment methods on active countries.
* DP-772 - Updated payment logo of AfterPay.

2021.06.01 - version 5.3.0

* DP-744 - Update PHP Library to version 3.2.0.

2021.05.27 - version 5.2.0

* DP-736 - Allow addresses without housenumbers and remove housenumber and housenumber element when empty.
* DP-747 - Fixed issue with capturing orders because of order fees.
* DP-733 - Fixed issue with streetnames starting with number.

2021.04.06 - version 5.1.0

* DP-728 - Fixed compatibility issue with MyParcel pickup points.

2021.03.09 - version 5.0.0

* DP-699 - Checked compatibility with Wordpress 5.6 and Woocommerce 5.0
* DP-701 - Fixed compatibility issue with MyParcel pickup points.
* DP-677 - Added AfterPay Strong Customer Authentication.

2020.11.26 - version 4.9.0

* DP-511 - Use the store address when local pickup is used as shipping method.
* DP-454 - Fix deprecated function for canceling orders.
* DP-698 - Updated the AfterPay PHP Library to version 2.9.1

2020.08.13 - version 4.8.0

* DP-663 - Updated and added payment methods for Norway, Sweden, Denmark and Finland.
* DP-662 - Tested compatibility with Wordpress 5.5 and Woocommerce 4.3.2

2020.07.09 - version 4.7.0

* DP-659 - Updated SOAP and REST endpoints.

2020.05.27 - version 4.6.0

* DP-621 - Tested compatibility with Wordpress 5.4.1 and WooCommerce 4.1.0.
* DP-638 - Updated the AfterPay PHP Library to version 2.4.
* DP-644 - Updated the AfterPay PHP Library to version 2.5.
* DP-657 - Updated the AfterPay PHP Library to version 2.6.
* DP-490 - Added French translations for supporting the french speaking part of Belgium.
* DP-295 - Exclude payment method based on shipping method.
* DP-201 - Terms and conditions should always be visible for the Netherlands and Belgium.
* DP-575 - Changed order of bank details fields for DACH.
* DP-545 - Support Dutch 'formal' language.
* DP-544 - Support configured language to used in the authorization for Belgium and Switzerland.
* DP-427 - Added functionality to notify admin about new AfterPay order.
* DP-658 - Add privacy statement text to Danish payment method.

2020.01.08 - version 4.5.0

* DP-468 - Remove pending functionality / push status for Belgium including order update mail
* DP-552 - Hide capture status field when automatisc capturing is selected
* DP-605 - Add warning to not adjust Capture/Refund settings
* DP-221 - Use logo from CDN

2019.11.14 - version 4.4.0

* Tested with Wordpress 5.3 and WooCommerce 3.8.0
* DP-604 - Update to new version of PHP Library 2.2.0
* DP-595 - Add AfterPay logo in SVG format
* DP-607 - Sent in full first name instead of initials for NL and BE requests

2019.05.27 - version 4.3.0

* Fixed issue showing all configuration options at start.
* DP-580 - Add duplicates of payment methods NL open invoice, NL open invoice B2b and DE open invoice for more flexibility.
* Tested with alpha release of Wordpress 5.2.2 and Woocommerce 3.6.3.

2019.01.29 - version 4.2.1

* DP-527 - Updated option for Customer Individual Score to enable and sent default value.

2019.01.29 - version 4.2.0

* DP-532 - Fixed issue with special characters. Description in SOAP is now only allowed on A-Z, a-z, 0-9, space, dash.
* DP-532 - Updated to version 2.0.0 of the AfterPay PHP Library

2019.01.09 - version 4.1.1

* Changed copyright to 2018
* DP-538 - Adjustments for Dutch low vat class.
* DP-518 - Advanced settings should not be shown by default
* DP-541 - Base max order limit on whole cart including shipping and payment fee
* DP-533 - Fixed issue with housenumber addition

2018.12.10 - version 4.1.0

* Tested with alpha release of Wordpress 5.0.1.
* DP-516 - Upgraded to version 1.9 of the AfterPay PHP Library
* DP-493 - Add sandbox as extra webservice to communicate with

2018.11.20 - version 4.0.3

* Tested with beta version of Wordpress 5.0.-beta5

2018.11.07 - version 4.0.2

* DP-504 - Fixed problem activating plugin in Wordpress Multisite Network
* DP-506 - Test compatibility with Woocommerce version 3.5.1

2018.10.18 - version 4.0.1

* DP-481 - Problem with address fields of MyParcel, seperate address fields for shipping were used, causing validation errors.
* DP-240 - Use address fields of pickup point location provided by MyParcel
* DP-329 - Minor translation updates for Norway open invoice and installments

2018.09.27 - version 4.0.0

* DP-449 - Reworked module according to Wordpress Coding Standards (New AfterPay Base and AfterPay Base Rest class where the payment methods are inherited on).

2018.08.10 - version 3.8.0

* Compatible with WP 4.9.8 + Woocommerce 3.4.4
* DP-404 - Added compatibility for PostNL shipment and pickup point delivery address
* DP-384 - Added possibility to show or hide the gender in DACH
* DP-323 - Added profile tracking for DACH
* DP-321 - Added customer individual score for DACH
* DP-386 - Used new terms and conditions and privacy statement from CDN
* DP-141 - Added installments for Sweden
* DP-152 - Added installments for Finland
* DP-148 - Added installments for Norway
* DP-326 - Added 'advanced configuration' to prevent mistakes or misconfigurations
* DP-325 - Used customerfacing message from webservice to show in case of address correction
* Cleaned up duplicate code in classes
* Updated coupon class to WordPress coding standards

2018.06.26 - version 3.7.0

* DP-319 - All javascripts are loaded on all pages, even when AfterPay is not enabled
* DP-311 - Change Payment method description according to display guidelines
* DP-329 - Fixed correct conversation language for Norway, Sweden, Finland
* DP-396 - Default disable captures and refunds for NL and BE
* DP-141 and DP-152 - Added preparations for installments Sweden and Finland
* DP-308 - Enable StreetNumbers with additional letters
* DP-397 - Bug with Sendclound, when no shipping method is used
* DP-308 - Enable StreetNumbers with additional letters
* DP-140 - Add open invoice for Sweden
* DP-398 - Use correct payment terms and conditions for Dutch connections
* DP-399 - Fixed bug with order status when capturing is disabled

2018.05.15 - version 3.6.0

* DP-226 - Added bankaccount validation
* DP-155 - Added direct debit for Germany
* DP-160 - Added direct debit for Austria
* DP-161 - Added direct debit for Switzerland
* DP-257 - Implemented new translations for Denmark
* DP-115 - Added compatibility with Sendcloud
* DP-256 - Woocommerce create option to capture based on status
* Updated to the latest AfterPay Library (1.7.0) + updated dependencies
* DP-269 - Postalcode plugin caused issue not sending shipping housenumber, build in check.

2018.02.12 - version 3.5.1

* DP231 - Add check for product image to be filled with an url, otherwise sent empty string
* DP232 - Add check on vat calculation on product with a price of 0.
* Updated translations

2018.02.07 - version 3.5.0

* Compatible with WP 4.9.4 + Woocommerce 3.3.1
* Updated to the latest AfterPay Library (1.6.0)
* DP-212 - Make address correction also respond on return code 200.101
* DP-135 - Added support for installments in Germany, Austria and Switzerland
* DP-135 - Added new transparent version of the logo
* DP-135 - Added new german translations for installments
* DP-135 - Fixed a bug with multiple items calculating the wrong tax amount for REST calls.
* DP-210 - Implement product images and product urls for OneAPI connections
* DP-140 - Add open invoice for Sweden (still disabled because of translations)
* DP-147 - Add open invoice for Denmark (still disabled because of translations)
* DP-149 - Add open invoice for Norway (still disabled because of translations)
* DP-150 - Add open invoice for Finland (still disabled because of translations)

2017.10.20 - version 3.3.0

* DP-113 - Updated to the latest AfterPay Library (1.4.0)
* DP-109 - Checked compatibility WordPress 4.8.2
* DP-112 - Checked compatibility Woocommerce version 3.2.1
* DP-29 - Fixed IP Restriction Bug
* DP-96 - Added gender as optional field for payment methods NL Open Invoice, NL Direct Debit and BE Open Invoice
* DP-110 - Fields for housenumber and housenumber addition are now configurable

2017.10.12 - version 3.2.0

* Compatible with WP 4.8.2 + Woocommerce 3.1.2
* Update to latest version of AfterPay Library 1.3.0
* Added configuration option to show or hide terms and conditions
* DE: Add a configurable information field to the checkout which can be used for extra information to the customer
* Compatible with Germanized Plugin version (1.9.1), compatible with gender selection and payment fee.
* DE: When an order is rejected the payment will be failed but the order will not be cancelled, to make new payment on the same order possible
* Tested compatibility with Pronamic Pay plugin (4.6.0)
* Placed disclaimer for capture and refund option in the configuration
* Added fallback to addressline 2 when housenumber is used in the second field for address
* Added code cleanup and PHP docblocks for release
* Add 'Call to action' on payment methods to get in contact with the sales department
* Add AfterPay Austria as a payment method
* Add AfterPay Switserland as a payment method
* DE: Sending VAT amounts and percentage in the request
* Updated German translations

2017.10.06 - version 3.1.1

* Hotfix: removed discount rule

2017.09.12 - version 3.1.0

* compatible with WP 4.8.1 + Woocommerce 3.1.2
* Update to latest version of AfterPay Library (1.2.9)
* Added capture and refund functionality to all payment options
* Added small updates and bugfixes to AfterPay Germany as a result of intensive testing
* Show country under address in address correction (AfterPay DE)
* Code cleanup

2017.07.26 - version 3.0.0

* compatible with WP 4.8 + Woocommerce 3.1.1
* Updated code to Woocommerce 3.0 standards, solved deprecation errors
* Fixed bug in overview of payment methods
* Removed merchantid and password check for availability
* Added availability check if not logged in as admin
* Update to latest version of AfterPay Library (1.2.8)
* NL: Check all dutch validation messages in dutch
* Remove text with amount limits in checkout
* Make it possible to restrict on multiple IP addresses (seperated by comma)
* Removed unneccesary javascript, composer files
* Added https to terms and conditions urls

2017.06.19 - version 2.9.1

* Bugfix for problem with showing wordpress menus in combination with this extension

2017.05.10 - version 2.9

* Used new version of the AfterPay Library (1.2.0 https://bitbucket.org/afterpay-plugins/afterpay-composer-package)
* Because of new AfterPay Library names with special characters can be used
* Removed gender from fields
* From this version it is mandatory for customers to agree to the terms and conditions of AfterPay
* compatible with WP 4.7.4 + WooCommerce 3.0.5

2017.01.05 - version 2.8

* The year selectbox of the date of birth field are now starting at the current year minus 18 years and arranged from newest year to oldest year.
* Removed css from checkout and changed field order
* When a validation error occurs, than the order will not be cancelled
* Added new copyright notice
* Updated AfterPay core library to 1.1.8
* compatible with WP 4.7 + WooCommerce 2.6.11

2016.09.05 - version 2.7

* fix for validations in Belgium
* used updated version of composer library

2016.08-26 - version 2.6

* fix for default country, removed option to base housenumber on default country
* fix for calculation of tax rate for shipping based on tax amount

2016.08.09 - version 2.5

* fix for housenumberaddition, now works with space (10 a), nospace (10a), special chars (- + , | )
* fix for vat category when rounding errors
* translation for validation problem with bankaccount numbers

2016.06.13 - version 2.4

* fix for issue where housenumber and housenumberaddition are not send separately
* fix for issue where B2B orders fail due to missing gender field
* removed unneeded B2B fields
* fix for order status not updating on Belgium orders, using WP 4.5.3
* added Dutch translations for all error messages
* compatible with WP 4.5.3 + WooCommerce 2.6.2

2016.04.05 - version 2.3

* Removed automatic update functionality to comply Woocommerce Standards
* Tested compatibility with Woocommerce 2.5.5
* Core AfterPay Class = Composer / Packagist class 1.0.8: https://packagist.org/packages/payintegrator/afterpay
* Changed naming and description of payment methods
* Removed changable option for description
* Removed OsPinto dBug Class
* Added most recent AfterPay Logo

2016.02-09 - version 2.2

* Added AfterPay NL Business 2 Business
* Tested compatibility with Woocommerce 2.4.2
* Several small bugfixes

2015.06.11 - version 2.1

* Added automatic update functionality

2015.06.04 - version 2.0.2

* Added AfterPay Belgium
* Update validation errors
* Added posibility for showing phone number
* Added posibility for ip restriction in testing
* Added better way for requesting client IP address
* Tested compatibility with Woocommerce 2.3.0
* Several small bugfixes

2015.04.22 - version 1.9.1

* Fixed add_error problems
* Fixed several php warning problems

2015.04.13 - version 1.9

* Fixed add_error problems

2015.01.19 - version 1.8

* Added compatibility with http://www.woothemes.com/products/sequential-order-numbers-pro/

2014.10.07 - version 1.7

* Removed surcharge code, advice to use https://wordpress.org/plugins/woocommerce-add-extra-charges-option-to-payment-gateways/
* Fixed WP DEBUG messages

2014.05.06 - version 1.6

* Code cleaned and set to WooCommerce standards. Also added default gateway to extend other payment methods. And payment fee added to cart.

2014.04.30 - version 1.5

* Added AfterPay Direct Debit
* Fixed invoice fee, added as new surcharge method. Now visible in checkout.

2014.04.24 - version 1.4

* Fixed issue with invoice fee, only added with AfterPay orders
* Correct response for validation errors

2014.04.10 - version 1.3

* Added phone number format to AfterPay Library

2014.04.10 - version 1.2

* Fixed return url problem

2014.02.11 - version 1.1

* Removed bankaccount number for SEPA

2013.06.19 - version 1.0

* First Release
