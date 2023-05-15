#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    # Initialize the application
    echo "Waiting for the database to be up..."
    timeout 15 bash -c 'until echo > /dev/tcp/localhost/13000; do sleep 0.5; done'

    bin/console cache:clear
    bin/console cache:warmup

    bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing

    if [ -n "$DEFAULT_USERNAME" -a -n "$DEFAULT_PASSWORD" ]; then
        bin/console app:user:create "$DEFAULT_USERNAME" -p "$DEFAULT_PASSWORD"
    fi

    # Start the web server
	set -- apache2-foreground "$@"
else
    exec "$@"
fi
