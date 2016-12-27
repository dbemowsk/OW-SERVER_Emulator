# OW-SERVER_Emulator

This is a file I created based off of a perl script by jheizer found here:
https://github.com/jheizer/MiPi-1wire/blob/master/mipi-1wire.pl

I designed this to work with my Vera plus home controller using the EDS One Wire Server
plugin for Vera.  This generates the details.xml file used for the emulation.  This can 
be used on a Raspberry Pi, Orange Pi, Beaglebone or any other type of server that can 
run the 1-Wire File System (OWFS).

To install:
First, install apache2 web server.  There are plenty of instructions for this on the web
for whatever device you are running it on.  Next, install php5 or newer.For this too, 
there are plenty of instructions for your device.  You can install LAMP if you want, but 
the MySql portion of this is not necessary.  Next, make sure you have OWFS installed for 
your 1-Wire devices.  Now place the owserver.php file in the root web folder, usually 
/var/www/html.  For the last step, I just set a cron job to run every minute that calls 
the owserver.php file, thus generating the details.xml file in the root folder.

For debugging output from the file, do the following:
owserver.php -d

This will output the generated xml code so you can see if your devices are being generated.
This uses the OWFS commands owdir and owread from a shell_exec command, so you may want to
test to make sure these are working.  Simply type "owdir" from the command line and it 
should show you a list of your 1-wire devices.

Now you can install the EDS One Wire Server Plugin for Vera.  Create your OWServer device 
using the instructions at http://code.mios.com/trac/mios_ow-server#no1 and enter the IP 
address of your newly created web server device.  Remember, this requires the json.lua file
mentioned in the above link.

UPDATE:  I had issues in the UI7 version of Vera trying to add my 1-Wire sensors.  I tracked 
the problem down to the J_OWServer.js file.  The file was trying to use a Hash object to 
define the ajax parameters for the OWpluginRegister function.  I changed this to use a 
standard javascript Object and updated the code to create the parameters using this Object.
I have included the new J_OWServer.js filr in the repo.  All other files are standard to the
EDS One Wire Server Plugin.
