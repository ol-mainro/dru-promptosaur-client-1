#!/bin/bash
git status
ddev drush cex -y
# ddev drush content:export taxonomy_term --translate --assets --bundle="prompts_type" -y
# ddev drush content:export taxonomy_term --translate --assets --bundle="prompts" -y
git add .
git status
git commit -m "many changes"
git push origin main
ssh root@138.68.174.12 "cd /var/www/websites.promptosaur.xyz; chmod +x ./ci/deploy-remote.sh; ./ci/deploy-remote.sh;"
ssh root@138.68.174.12 "cd /var/www/psaur_test_instances/1; chmod +x ./ci/deploy-remote.sh; ./ci/deploy-remote.sh;"