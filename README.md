# Localisation Knowledge Repository (LKR)
The Localisation Knowledge Repository (LKR) is a pre-translation quality assurance system designed to facilitate the development of usable, readable and translatable source language digital content.
The LKR operates as a web application enabling authors and project managers to improve the quality of source language content, in order to reduce the time and resources required to localise it for international audiences.

Researcher: Lorcan Ryan

Developers: Dr. Eoin Ó Conchúir, David O Carroll

## License notice
This software is licensed under the terms of the GNU LESSER GENERAL PUBLIC LICENSE Version 3, 29 June 2007 For full terms see License.txt or http://www.gnu.org/licenses/lgpl-3.0.txt

## Requirements 
This program requires PHP 4+, PHPMyAdmin and Apache
PEAR is also required to communicate with the CNLF server

## Installing PEAR (and HTTP_Request2) on Windows XP/Vista/7
* Open a command prompt and navigate to the PHP folder
* Run the pear.bat file
* Then, using the pear command, install Net_URL2. The command:
	pear install Net_URL2-0.3.1
* Then install HTTP_Request2 using the command:
	pear install HTTP_Request2-0.5.2
* If you encounter any problems get more info at http://pear.php.net/

## Installing PEAR (and HTTP_Request2) on Ubuntu 10.04.
* Make sure PHP is already installed.
* Install the package php-pear, and restart Apache.
* PEAR is a package manager, and requires additional sub-packages to be installed.
* In the terminal 
 ```sudo pear install Net_URL2```
* If a failed error shows, follow its instructions for adding "channel:..." to the end of the previous command.
* In the terminal: 
```	sudo pear install HTTP_Request2```
* Edit /etc/php5/apache2/php.ini, changing the include_path to as
follows:
```	include_path = ".:/usr/share/php:/usr/share/php/PEAR"```

## Environment configuration
MySQL:
Create a MySQL database for LKR. 
Import the database structure from 

Edit includes/conf.php to configure environment variables.

The LocConnect server url is located in conf.php and must be updated when the domain name changes

## Live demo
* http://demo.solas.uni.me/lkr/

## References
* http://ulir.ul.ie/bitstream/handle/10344/4268/Ryan_2014_digital.pdf?sequence=6

## Acknowledgement
This research is supported by the Science Foundation Ireland (Grant 12/CE/I2267) as part of Centre for Next Generation Localisation (CNGL) www.cngl.ie at the Localisation Research Centre, Department of Computer Science and Information Systems, University of Limerick, Limerick, Ireland. It was also supported, in part, by "FP7-ICT-2011-7 - Language technologies" Project "MultilingualWeb-LT (LT-Web) - Language Technology in the Web" (287815 - CSA).
