VDolgah Social Network
========================

VDolgah is a social network co-developed by students of B8403a and B8403g groups of the Far Eastern Federal University for educational purpose.

Installation
------------------------

### Requirements

PHP v5.5 or higher.
MySQL v5.5 or higher.

### Getting Source Files

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

## Easy install:

### Ubuntu/Debian/Mint

Download [`installer_linux.sh`][installer_linux]. Then run `installer_linux.sh -h` to see more info about usage.



Enjoy!

[1]:  http://getcomposer.org/
[2]:  http://symfony.com/doc/current/reference/requirements.html
[installer_linux]: https://github.com/koninka/fefu-social-network/blob/master/installer_linux.sh