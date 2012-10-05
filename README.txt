Localisation Knowledge Repository (LKR)

--- Introduction ------------------------------------------

Researcher: Lorcan Ryan
Developers: Dr. Eoin Ó Conchúir, David O Carroll

The Localisation Knowledge Repository (LKR) is a pre-translation quality assurance system designed to facilitate the development of usable, readable and translatable source language digital content.

The LKR operates as a web application enabling authors and project managers to improve the quality of source language content, in order to reduce the time and resources required to localise it for international audiences.

--- Requirements ------------------------------------------
This program requires PHP 4+, PHPMyAdmin and Apache
PEAR is also required to communicate with the CNLF server

Installing PEAR (and HTTP_Request2) on Windows XP/Vista/7
* Open a command prompt and navigate to the PHP folder
* Run the pear.bat file
* Then, using the pear command, install Net_URL2. The command:
	pear install Net_URL2-0.3.1
* Then install HTTP_Request2 using the command:
	pear install HTTP_Request2-0.5.2
* If you encounter any problems get more info at http://pear.php.net/

Installing PEAR (and HTTP_Request2) on Ubuntu 10.04.
* Make sure PHP is already installed.
* Install the package php-pear, and restart Apache.
* PEAR is a package manager, and requires additional sub-packages to be
installed.
* In the terminal
	sudo pear install Net_URL2
* If a failed error shows, follow its instructions for adding
"channel:..." to the end of the previous command.
* In the terminal: 
	sudo pear install HTTP_Request2
* Edit /etc/php5/apache2/php.ini, changing the include_path to as
follows:
	include_path = ".:/usr/share/php:/usr/share/php/PEAR"
* If this is not installed on a lampp installation then there may be a conflict between some of the modules enabled by default. If you have problems viewing certain pages (author view for example) then disable MultiViews.

--- Environment configuration ------------------------------------------
MySQL:
Create a MySQL database for LKR. 
Import the database structure from 

Edit includes/conf.php to configure environment variables.

The LocConnect server url is located in conf.php and must be updated when the domain name changes
