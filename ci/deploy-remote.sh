#!/bin/bash
drush config:set system.maintenance message "Maintenance en cours..." -y
drush state:set system.maintenance_mode 1 --input-format=integer
git fetch --all
git clean -f
git reset --hard HEAD
git pull origin main
vendor/bin/drush cr
sudo -u www-data composer install --no-dev
vendor/bin/drush updb -y
vendor/bin/drush cim -y
# vendor/bin/drush content:import taxonomy_term --translate --assets --bundle="prompts_type" -y
# vendor/bin/drush content:import taxonomy_term --translate --assets --bundle="prompts" -y
vendor/bin/drush cr
drush state:set system.maintenance_mode 0 --input-format=integer