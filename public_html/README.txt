--- Overview -----------------------------------------------------------
The Localisation Knowledge Repository (LKR) is a pre-translation quality 
assurance system designed to facilitate the development of usable, 
readable and translatable source language digital content. It is a 
web application.

It has been developed based on the research of Lorcan Ryan, Univesity
of Limerick. It has been developed by Eoin Ó Conchúir and David O 
Carroll.

The system is copyright University of Limerick 2010. It has been 
developed as part of the Centre for Next Generation Localisation (CNGL).

--- System requirements ------------------------------------------------
This program requires PHP 4+, phpMyAdmin and Apache.

In order to access the centralised LocConnect service, PEAR is also 
required.

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

--- Environment configuration ------------------------------------------
MySQL:
* Create a MySQL database for LKR, and a MySQL user to access the 
database.
* Import the database structure from /lkr.structure.sql.

Edit /public_html/includes/conf.php to configure environment variables.
Set the database connection details.

Ensure that /public_html/files/raw/ and /public_html/files/segmented/
are both writable by the web server.

Confirgure the URL to the CNLF server.
