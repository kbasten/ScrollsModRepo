ScrollsModRepo
==============

Scrolls Mod Repo for Summoner : Scrolls Modloader

How to install
--------------

The Mod Repo for Summoner runs on any server with Apache, PHP and MySQL installed. Fork this repo, create a database, run install/database.sql to create the required files. Then fill out the database credentials and your repository name in config.php. It's required to run the repository on the root path of your domain (or subdomain).

In your Apache config file for the repo, make sure to set the DocumentRoot to the public_html directory, and make sure the .htaccess file in that folder is used.

Additionally, you can replace public_html/favicon.ico and public_html/favicon.png with icons of your choosing and they will be displayed in the Mods menu in-game. Make sure to use the same size for the icons as the default ones.

If you're having trouble installing the Scrolls Mod Repo for Summoner, contact kbasten on Scrollsguide.
