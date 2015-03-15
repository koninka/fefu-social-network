#!/bin/bash

sudo php app/console assets:install --symlink
php app/console server:run 0.0.0.0:6543 -vvv