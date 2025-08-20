=== est Financial Chatbot ===
Contributors: estfinancial
Tags: chatbot, ai, appointment booking, financial services, go high level
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered chatbot for est Financial services with appointment booking and Go High Level integration.

== Description ==

The est Financial Chatbot plugin provides an intelligent AI assistant for your WordPress website that can:

* Provide information about est Financial services
* Help visitors book appointments
* Integrate with Go High Level CRM via webhooks
* Offer a professional, responsive chat interface
* Work on both desktop and mobile devices

= Features =

* **Service Information**: Automatically provides details about all est Financial services including Finance & Mortgage Broking, Investment Acquisition, Asset Management, Legal Services, Tax Accounting, and Financial Advice.

* **Appointment Booking**: Collects visitor information and sends appointment requests to your Go High Level CRM system.

* **Customizable Design**: Choose your preferred position (bottom-right, bottom-left, top-right, top-left) and theme color to match your website.

* **Mobile Responsive**: Optimized for all device sizes with touch-friendly interactions.

* **Easy Integration**: Simple setup with your existing chatbot backend service and Go High Level webhook.

= Setup Requirements =

1. A deployed chatbot backend service (API endpoint)
2. Go High Level webhook URL for appointment integration
3. WordPress admin access to configure settings

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/est-chatbot` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Navigate to Settings > est Chatbot to configure the plugin.
4. Enter your API endpoint URL and Go High Level webhook URL.
5. Customize the appearance and position settings.
6. Enable the chatbot to start using it on your website.

== Configuration ==

After installation, go to **Settings > est Chatbot** in your WordPress admin to configure:

* **Enable Chatbot**: Toggle the chatbot on/off
* **API Endpoint URL**: Your deployed chatbot backend service URL
* **Go High Level Webhook URL**: Your GHL webhook for appointment integration
* **Chatbot Position**: Choose where the chatbot appears on your site
* **Theme Color**: Customize the chatbot's primary color

== Usage ==

= Automatic Display =
Once configured and enabled, the chatbot will automatically appear on all pages of your website as a floating widget.

= Shortcode Usage =
You can also embed the chatbot in specific pages or posts using the shortcode:

`[est_chatbot]`

For an inline version that doesn't float:
`[est_chatbot inline="true"]`

== Frequently Asked Questions ==

= Do I need a separate backend service? =

Yes, this plugin requires a backend chatbot service to handle the AI conversations and appointment booking logic. The plugin acts as the frontend interface that communicates with your backend service.

= How does the Go High Level integration work? =

When a visitor books an appointment through the chatbot, their information is sent to your Go High Level CRM via a webhook. You need to provide your GHL webhook URL in the plugin settings.

= Can I customize the chatbot's appearance? =

Yes, you can customize the position (4 corner options) and the primary theme color through the plugin settings. Additional styling can be done through custom CSS.

= Is the chatbot mobile-friendly? =

Yes, the chatbot is fully responsive and optimized for mobile devices with touch-friendly interactions.

= Can I disable the chatbot on specific pages? =

Currently, the chatbot appears on all pages when enabled. You can use the shortcode method for more granular control over where it appears.

== Screenshots ==

1. Chatbot widget in action on a website
2. Plugin settings page in WordPress admin
3. Mobile view of the chatbot interface
4. Appointment booking flow example

== Changelog ==

= 1.0.0 =
* Initial release
* AI-powered conversation handling
* Appointment booking functionality
* Go High Level webhook integration
* Customizable appearance and positioning
* Mobile-responsive design
* Shortcode support

== Upgrade Notice ==

= 1.0.0 =
Initial release of the est Financial Chatbot plugin.

== Support ==

For support and questions, please contact est Financial at info@est.com.au or visit https://est.com.au

== Technical Requirements ==

* WordPress 5.0 or higher
* PHP 7.4 or higher
* jQuery (included with WordPress)
* Active internet connection for API communication
* Valid SSL certificate recommended for secure communication

== API Integration ==

This plugin communicates with external services:

* Your deployed chatbot backend service for AI conversation handling
* Go High Level CRM system for appointment booking integration

Please ensure you have proper permissions and agreements in place for these integrations.

== Privacy ==

This plugin may collect and transmit user conversation data to your configured backend service and Go High Level CRM. Please ensure your privacy policy covers this data collection and complies with applicable privacy laws.

