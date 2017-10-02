#!/bin/bash

##see: http://stackoverflow.com/questions/192249/how-do-i-parse-command-line-arguments-in-bash
# Use -gt 1 to consume two arguments per pass in the loop
# Use -gt 0 to consume one or more arguments per pass in the loop

DB_USER='unit_chris'
DB_PASS='unit_chris'
DB_NAME='unit_chris'
CREATE_DB=false
RUN_PHAN=false
RUN_LINT=true
while [[ $# -gt 0 ]]
do
    key="$1"

        case $key in

            -g|--group)
                GROUP="$2"
                shift # past argument
            ;;

            -d|--database)
                DB_NAME="$2"
                shift # past argument
            ;;

            -u|--user)
                DB_USER="$2"
                shift # past argument
            ;;

            -p|--password)
                DB_PASS="$2"
                shift # past argument
            ;;

            --create-database)
                CREATE_DB=true
            ;;

            --no-lint)
                RUN_LINT=false
            ;;

            --run-phan)
                RUN_PHAN=true
            ;;

            --update-composer)
                UPDATE=true
            ;;

            *)
                    # unknown option
            ;;
        esac

    shift # past argument or value
done

if [ ! -d './vendor' ]; then
    UPDATE=true
fi

if [ "$UPDATE" = true ]; then
    composer update
    composer install
fi

if [ "$RUN_LINT" = true ]; then
    ./vendor/bin/parallel-lint --exclude vendor/ .
fi

if [ "$RUN_PHAN" = true ]; then
    ./vendor/bin/phan .
fi

vendor/bin/security-checker security:check ./composer.lock

CONFIG_TEMPLATE_FILE='./vendor/WordPress/wordpress-develop/wp-tests-config-sample.php';
CONFIG_FILE='./vendor/WordPress/wordpress-develop/wp-tests-config.php';

cp $CONFIG_TEMPLATE_FILE $CONFIG_FILE
sed -i "s|youremptytestdbnamehere|${DB_NAME}|g" $CONFIG_FILE
sed -i "s|yourusernamehere|${DB_USER}|g" $CONFIG_FILE
sed -i "s|yourpasswordhere|${DB_PASS}|g" $CONFIG_FILE

if [ "$CREATE_DB" = true ]; then
    mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"
fi

if [ -z "$GROUP" ]; then
    ./vendor/bin/phpunit --coverage-html ./tests/logs/coverage/
else
    ./vendor/bin/phpunit --coverage-html ./tests/logs/coverage/ --group $GROUP
fi


#phpunit --coverage-html ./tests/logs/coverage/
