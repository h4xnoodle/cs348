Rebecca Putinski 20271463
CS348 Project Part 2

===============================

READ ME
=======
To use this HMS, follow the installation instructions in the design document or see below.

Install http://www.zend.com/en/products/server-ce/index. Make note of which port (usually 80)
that the server will be listening on. This package includes PHP 5, Apache, and a plethora of extensions.
We will make use of the 'pdo_ibm' extension for this HMS.

Go to Server Setup -> Extensions when the software is installed. Scroll & enable 'pdo_ibm'.
Place the unzipped .php files into Program Files\Zend\Apache2\htdocs.

Access localhost:<port>/index.php. <port> is the port number the server is listening on (usually 80).
Follow the instructions on index.php to create the tables (execute createTables.sql).
Click the link on index.php to insert an employee for usage purposes.

Starting using the HMS! 


TROUBLESHOOTING
===============
Connection issues? Ensure the connection details in config.php are correct for your setup.