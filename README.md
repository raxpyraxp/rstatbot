# rstatbot

This is a simple Telegram bot for checking host availability. Bot can check availability for list of hosts.
This bot is written for myself, but I decided to share it.

## Requirements

Any \*nix server with PHP 5.x installed.

Because of webhook usage, there are some Telegram requirements:

* Domain name
* SSL certificate (You can use Let's Encrypt or self-signed)

## Installation

1. Drop files into your webserver directory.
2. Go to the Telegram, find the *BotFather* bot and create a new bot for yourself.
3. *BotFather* will offer you a token for your bot, so take it and write it into *config.php*.
4. Write your nickname into $allowed\_users. Otherwise it will say you that access is denied.
5. Change the list of hosts with your needs.
6. Make a request to https://api.telegram.org/botYOUR:TOKEN/setWebhook?url=https://yoursite.com/path/to/bot/receive.php to set a webhook onto your host.
7. Find your bot in Telegram, start communication with it and send a /status command. After a while, the bot will drop you a status for every host in list.

## License

You are free to do with it whatever you want.
