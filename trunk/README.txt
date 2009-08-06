Let's assume this is installed here:

	/var/www/html/phpMonitor

Switch to that directory

	cd /var/www/html/phpMonitor

Is MySQL installed?  This is the fedora/centos/redhat way.
If it's already installed you can skip this part.

	yum -y install mysql-server mysql php-mysql
	chkconfig mysqld on
	service mysqld start

Setup database.

	mysql -u root < db/initialize.sql

Setup the access username/password for the database.
Be sure to replace yoursecretpassword with your own secret password.
Also, if the web server is not running on the same machine as the MySQL
database server, you'll need to change localhost to the hostname or ip address
of the web server.  If you have multiple web servers, you may need to repeat
this once for each web server.

	mysql -u root mysql
	grant all privileges on phpmonitor.* to phpmonitor@localhost identified by 'yoursecretpassword' with grant option;
	revoke super on *.* from phpmonitor@localhost identified by 'yoursecretpassword';
	flush privileges;
	quit

Set security on important scripts - only (you) can access them.
If you want to run it under a different user thats fine too - just change the owner
from root:root to your username:groupname.

	chown root:root killAllMonitors.sh phpMonitorCron.php
	chmod 744 killAllMonitors.sh phpMonitorCron.php

Setup crontab for monitor jobs.

	crontab -e

Add these lines to it - this assumes same install dir and that you want to log to /var/log.

	0 0 * * * rm -f /var/log/phpMonitor.log >/dev/null 2>&1
	* * * * * /var/www/html/phpMonitor/phpMonitorCron.php >> /var/log/phpMonitor.log 2>&1

Setup your options.  Copy dbSettings.include.php.sample to dbSettings.include.php, then
edit dbSettings.include.php and change the connection parameters to match your
configuration.

Create an Apache config file for the web app.  Under fedora/centos/redhat, it would be named
/etc/httpd/conf.d/phpMonitor.conf.  Put the following configuration into it,
adjusting port number, path, etc, as needed:

	<VirtualHost *:82>
	DocumentRoot /var/www/html/phpMonitor
	ServerName phpMonitor
	</VirtualHost>

Assuming you used the above configuration, point your browser to the phpMonitor web app:

    http://<server-name>:82

Log in using username admin, password admin.  Click on settings.  Change the username and
password to what you want it to be.  Click "Save Settings".  You will be logged back out.
Log in again using the new username and password you configured.

Now you can begin adding monitors.  Click "Add Monitor".  Select the monitor type you want
to add from the drop-down list.  Edit the settings to your needs, then click "Save Settings".
Now click the "Monitors" link.  You should see your new monitor listed there.
