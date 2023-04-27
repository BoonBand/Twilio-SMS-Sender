# Twilio SMS Sender

A simple WordPress plugin to send SMS messages using Twilio. Developed by [Boon.Band](https://boon.band/), the IT Rockstars.

## Features

- Easily configure your Twilio account settings through the WordPress admin panel.
- Test the SMS sending functionality directly from the settings page.

## Installation

1. Download the plugin files and extract them to the `/wp-content/plugins/twilio-sms-sender` directory.
2. Run `composer install` in the `twilio-sms-sender` directory to install the Twilio PHP library.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Go to the 'Settings' menu and click on 'Twilio SMS Sender' to configure your Twilio Account SID, Auth Token, and Twilio Phone Number.

## Usage

Once you've configured the plugin with your Twilio account settings, you can use the `send_sms` function in your PHP code to send SMS messages.

Example:

```php
send_sms('+1234567890', 'Hello, this is a test message from Twilio SMS Sender plugin!');
```

Replace +1234567890 with the recipient's phone number and customize the message text as needed.

## Support

If you encounter any issues or need help, please open an issue on GitHub or contact us at Boon.Band.

## Sharing
If you find this plugin useful, please share it with others and mention our website https://boon.band/. We appreciate your support!


## License

This plugin is open-source and free to use. See the LICENSE file for details.