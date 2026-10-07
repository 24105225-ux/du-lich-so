#!/bin/sh
set -eu

export APP_KEY="${APP_KEY:-base64:$(php -r 'echo base64_encode(random_bytes(32));')}"
export APP_ENV="${APP_ENV:-local}"
export APP_DEBUG="${APP_DEBUG:-true}"

php artisan optimize:clear
exec php artisan serve --host=0.0.0.0 --port=8000
