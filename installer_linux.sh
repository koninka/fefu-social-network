#!/bin/bash 

#php.ini be sure that you set timezone

command_exists(){
	if command -v $1 >/dev/null; then
		return 0;
	else
		return 1;
	fi
}   

command_required(){
	if ! command_exists $1; then 
		echo "$1 is required. Install $1 before continue. Aborting."; 
		exit 1;
	fi
} 

make_composer_install(){
	if ! [ -f composer.phar ] && ! command_exists composer ; then 
		echo "composer not installed run script with --install flag";
		exit 1;
	fi
	if command_exists composer ; then
		composer install;
	else
		php composer.phar install;
	fi
}

install_composer(){
	if ! [ -f composer.phar ] && ! command_exists composer  ; then
		echo "Please wait, getting composer installer";
		php -r "readfile('https://getcomposer.org/installer');" | php;
	fi
	make_composer_install;
}

clone_repo(){
	command_required git;
	git init;
	git remote add upstream https://github.com/koninka/fefu-social-network.git;
	git fetch upstream;
	git checkout upstream/master;
}

wait_ans(){
	echo "$2[y/n]"
	while :
	do
		read ans;
		ans=`echo "$ans" | tr '[:upper:]' '[:lower:]'`;
		if [[ $ans == 'y' ]]; then
			$1;
			break;
		elif [[ $ans == 'n' ]]; then
			break;
		fi
	done
}

check_configuration(){
	php app/check.php;
	echo "Note::If you get any errors please fix them";
	echo "Note::If you get warnings feel free to ignore them";
	echo "Note::To check configuration again run that script with --check_config flag"
	echo "If you feel that you are ready use that script with --run_server flag or use 'php app/console server:run'"
}

install_php(){
	sudo apt-get install php5 php5-cli php5-intl php-apc php5-curl
}

install_mysql(){
	sudo apt-get install mysql-client mysql-server php5-mysql
}

install_git(){
	sudo apt-get install git
}

install(){
	case "$dependence" in 
		php)
			install_php;
			;;
		mysql)
			install_mysql;
			;;
		git)
			install_git;
			;;
	esac
}

check_and_install(){
	dependence="$1";
	if ! command_exists $1; then
		wait_ans install "$1 is not installed. Would you like to install it?"
	else
		echo "$1 is installed";
	fi
}

check_dependencies(){
	#check_and_install php;
	#command_required php;
	install_php;
	wait_ans install_mysql "do you want to install mysql packages?";
	if ! command_exists mysql; then
		echo "mysql wasn't installed. 
		If you want to use other db_driver, you must install it manually. 
		To change your db_driver change row database_driver in ./app/config/parameters.yml";
	fi
	check_and_install git;
}

run_server(){
	php app/console server:run;
}

case "$1" in 
-h|--help)
	echo "options:"
	echo "-h,  --help                      show that message"
	echo "-cc, --check_config              check configuration of system"
	echo "-i,  --install or without flags  run installation of project"
	echo "-r,  --required                  check and install required packages"
	echo "-rn, --run_server                runs 'php app/console server:run'"
	;;
-cc|--check_config)
	check_configuration;
	;;
-u|--update)
	make_composer_install;
	;;
-r|--required)
	check_dependencies;
	;;
-rn|--run_server)
	run_server;
	;;
-i|--install|*)
	check_dependencies;
	if ! [ -d .git ]; then
		wait_ans clone_repo "Not a git repo. Clone upstream repo to current directory?";
	fi;
	install_composer;
	php app/console doctrine:database:create;
	php app/console doctrine:schema:create;
	check_configuration;	
	;;
esac

