#!/bin/bash

echo -n "Would you like to do 'git pull' (\"y\" or \"n\", default: \"n\"): "
read answer
if [ "$answer" = "y" ]; then
   git stash
   if ! git pull --rebase origin master; then
      echo "Fix conflicts and then run this script again"
      exit
   fi
   git stash apply
fi

echo -n "Would you like to update dependencies from composer.json (\"y\" or \"n\", default: \"n\"): "
read answer
if [ "$answer" = "y" ]; then
   composer update
fi

php app/console doctrine:database:drop --force
php app/console doctrine:database:create
php app/console doctrine:generate:entities --no-backup Network
php app/console doctrine:schema:create
php app/console doctrine:fixtures:load
php app/console assets:install web --symlink

echo -n "Set the environment name for the cache clearing (\"prod\" or \"dev\", default: \"dev\"): "
read env
if [ "$env" != "prod" ]; then
   env="dev"
fi

echo -n "Do you want to switches off debug mode(\"y\" or \"n\", default: \"y\"): "
read has_debug
if [ "$has_debug" != "n" ]; then
   has_debug="y"
fi

sudo chmod -R a+rw .
if egrep -i "^www-data" /etc/group > /dev/null; then
  sudo chown $USER:www-data .
fi

if [ "$has_debug" = "y" ]; then
   php app/console cache:clear --env=$env
else
   php app/console cache:clear --env=$env --no-debug
fi
