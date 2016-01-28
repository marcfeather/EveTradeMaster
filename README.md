#EveTradeMaster (www.evetrademaster.com)
ETM is a web based, lightweight profit tracker and asset manager for the popular MMORPG Eve Online.

#Features:
-Automatically track all your assets, contracts, transactions and market orders over several characters

-Automatically sincronizes your data trough the specified API Keys

-Tracks down and calculates profits made by resells trough a FIFO (first in first out) queue system

-Can track profits made trough multiple characters

-Displays useful statistics such as best sellers, best customers, fastest turnovers and much more

-Keeps track of a history of your combined networth (assets, wallet, sell orders and escrow)

-Sends automatic reports by e-mail with your earnings and highlightning interesting information

-Can simulate prices between different systems and regions using Eve-Central's API (to be replaced by Eve Online's CREST API soon)

-Illustrative graphs to represent your progress

-Lightweight, responsive web design allows you to easily filter, rearrange and highlight important data on any device

#Requirements:
-A properly configured Ubuntu 14.04 web server (64 bit) running Apache httpd with SSH 

-A working e-mail account

-Minimum of 1GB MB RAM for >200 users (less also works, but you need to fiddle a lot more with MySQL's memory useage and swap space)

-An SSD disk is highly recommended due to the heavy I/O nature of this application

-Access to Eve Online's Static Data Export (SDE) and Image export collection

#Dependencies:
-Apache 2.2

-PHP 5.6 with CURL, SimpleXML, JSON extensions

-Bootstrap 3.3 (HTML 5 and CSS 3)

-jQuery 1.3 with datatables plugin

-PHEALNG library (for web requests and local caching)

-PHPMailer library (for password requests and sending reports)

-Fusioncharts JS library (for nice looking graphs)

-Composer (for installing packages)

-POSTFIX (for server cron reports)

#External APIs

-Eve Online's XML API (for fetching user data)

-Eve Central's API (for fetching item prices across the universe)

-Eve Online's CREST API (for fetching real-time item price data - in development)

#Cronjobs
In order to properly update data, backup your database and send automated reports you'll need to add the following jobs to cron:

53 11 * * * php /var/www/html/pages/scripts/internal/autoexec_pricedata.php

21 12 * * * php /var/www/html/pages/scripts/internal/autoexec_outposts.php

00 10 * * * sh /var/backup.sh

00 11 * * * php /var/www/html/pages/scripts/internal/exec_update.php

00 12 * * 7 php /var/www/html/pages/scripts/internal/autoexec_mailer_week.php

00 13 31 * * php /var/www/html/pages/scripts/internal/autoexec_mailer_month.php

53 00 * * * php /var/www/html/pages/scripts/internal/autoexec_pricedata.php

backup.sh is just a simple mysqldump

#Apache and MYSQL configuration

You'll need to set specific permissions to your Apache web user (typically www-data).

It's recommended to keep certain folders private, while others should only be accessed by specific IPs for management (such as PhpMyAdmin) or MySQL's listening ports.

You should also probably fiddle a little with MySQL's memory values. mysqltuner is a good package for this.

This is however up to you to decide and sharing entire configuration files here is outside the scope of this guide. It's only meant to setup a local instance of the program, and for this you only need to configure /pages/scripts/classes/link.php with the correct MySQL username and password.

#Database
The database is fully MySQL compatible. Unfortunately I can't guarantee much else.

It's recommended to create a different user with reduced permissions when using the application (i.e not root), so a malicious user is unable to gain access to critical commands.

The database provided here already contains every item, NPC faction, NPC corporation, player made outpost, solar system and region in Eve Online as of 28th January. Further updates will require updating these tables. The database schema I use is different from Eve Online's official SDE (though it obviously contains all the relevant data).

The database file contains a stored procedure (calendar dates) which needs to be initialized before starting up the program.
It also contains some views which aren't used by the main program but may prove to be useful for testing purposes.

#Warning
This product is still in beta and has several features comming in the near future.

Most of the code is still in development state and is pending refactoring in some classes. There is lots to be done for this to be a real release still, but this is currently my work in progress.

#License
All logos and images related to Eve Online are copyrighted and property of CCP Games. I do not take any ownership for these.

It also used multiple 3rd party libraries which have their own licensing.



