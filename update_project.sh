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
   composer install
fi

sudo chmod -R a+rw .
if egrep -i "^www-data" /etc/group > /dev/null; then
   sudo chown $USER:www-data .
fi

if [[ ! -z "$VDOLGAH_PROD" && $VDOLGAH_PROD -eq 1 ]]; then
   php app/console cache:clear --env=prod --no-debug
   os=$(uname)
   if [ "$os" = "Linux" ]; then
      sed -i 's/app_dev/app/g' web/.htaccess
   elif [ "$os" = "Darwin" ]; then
      sed -i '' 's/app_dev/app/g' web/.htaccess
   fi
else
   php app/console cache:clear --env=dev
fi

php app/console doctrine:database:drop --force
php app/console doctrine:database:create
php app/console doctrine:schema:create
php app/console doctrine:fixtures:load
php app/console assets:install web --symlink

echo "Done!"
