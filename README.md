VDolgah - educational social network
========================

VDolgah - social network is co-developed by the students of the groups B8403a and B8403g of the Far Eastern Federal University who are studying mathematics and computer science in 2014 for educational purposes.

Installation
------------------------

This section contains information on how to download and install this project.

### Requirements

PHP version must be at least 5.5.

### Getting the (development) source files

Install git (http://git-scm.com/) onto your system. Then run a clone:

    git clone git://github.com/koninka/fefu-social-network.git

This will download the latest sources into a directory named 'fefu-social-network'.

### Install Composer

[Composer][1] needs to manage dependencies.

If you don't have Composer yet, download it following the instructions on
http://getcomposer.org/ or just run the following command:

    curl -s http://getcomposer.org/installer | php

Then switch to the `path/to/fefu-social-network` directory:

    cd path/to/fefu-social-network

Then, use the `install` command to install all the necessary dependencies:

    composer install

or

    php composer.phar install

### Checking your System Configuration

Before starting coding, make sure that your local system is properly configured for Symfony.

Execute the `check.php` script from the command line:

    php app/check.php

The script returns a status code of `0` if all mandatory requirements are met, `1` otherwise.

Access the `config.php` script from a browser:

    http://localhost/path-to-project/web/config.php

If you get any warnings or recommendations, fix them before moving on.

If you still have errors see the [Requirements for Running Symfony][2].

Enjoy!

[1]:  http://getcomposer.org/
[2]:  http://symfony.com/doc/current/reference/requirements.html
