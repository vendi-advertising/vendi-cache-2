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
RUN_SEC=true
UPDATE_COMPOSER=false
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
                UPDATE_COMPOSER=true
            ;;

            *)
                    # unknown option
            ;;
        esac

    shift # past argument or value
done

maybe_update_composer()
{
    if [ "$UPDATE_COMPOSER" = true ]; then
        composer update && composer install
        if [ $? -ne 0 ]; then
        {
            echo "Error with composer... exiting";
            exit 1;
        }
        fi
    fi
}

maybe_run_linter()
{
    if [ "$RUN_LINT" = true ]; then
        ./vendor/bin/parallel-lint --exclude vendor/ .
        if [ $? -ne 0 ]; then
        {
            echo "Error with PHP linter... exiting";
            exit 1;
        }
        fi
    fi
}

maybe_run_phan()
{
    if [ "$RUN_PHAN" = true ]; then
        ./vendor/bin/phan .
        if [ $? -ne 0 ]; then
        {
            echo "Error with PHP PHAN... exiting";
            exit 1;
        }
        fi
    fi
}

maybe_run_security_check()
{
    if [ "$RUN_SEC" = true ]; then
        vendor/bin/security-checker security:check ./composer.lock
        if [ $? -ne 0 ]; then
        {
            echo "Error with security checker... exiting";
            exit 1;
        }
        fi
    fi
}

setup_wordpress_config()
{
    CONFIG_TEMPLATE_FILE='./vendor/WordPress/wordpress-develop/wp-tests-config-sample.php';
    CONFIG_FILE='./vendor/WordPress/wordpress-develop/wp-tests-config.php';

    if [ ! -f "$CONFIG_TEMPLATE_FILE" ]; then
        {
            echo "Cannot find WordPress config template... exiting";
            exit 1;
        }
    fi

    cp $CONFIG_TEMPLATE_FILE $CONFIG_FILE &&
    sed -i "s|youremptytestdbnamehere|${DB_NAME}|g" $CONFIG_FILE &&
    sed -i "s|yourusernamehere|${DB_USER}|g" $CONFIG_FILE &&
    sed -i "s|yourpasswordhere|${DB_PASS}|g" $CONFIG_FILE
    if [ $? -ne 0 ]; then
    {
        echo "Error with local test configuration... exiting";
        exit 1;
    }
    fi
}

maybe_create_database()
{
    if [ "$CREATE_DB" = true ]; then
        mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"
        if [ $? -ne 0 ]; then
        {
            echo "Error creating database... exiting";
            exit 1;
        }
        fi
    fi
}

run_php_unit()
{
    if [ -z "$GROUP" ]; then
        ./vendor/bin/phpunit --coverage-html ./tests/logs/coverage/
    else
        ./vendor/bin/phpunit --coverage-html ./tests/logs/coverage/ --group $GROUP
    fi
}

if [ ! -d './vendor' ]; then
    UPDATE_COMPOSER=true
fi

maybe_update_composer;
maybe_run_linter;
maybe_run_phan;
maybe_run_security_check;

setup_wordpress_config;

maybe_create_database;

run_php_unit;
