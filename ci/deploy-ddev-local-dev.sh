#!/bin/bash
git status
ddev drush cex -y
# ddev drush content:export taxonomy_term --translate --assets --bundle="prompts_type" -y
# ddev drush content:export taxonomy_term --translate --assets --bundle="prompts" -y
git add .
git status
git commit -m "many changes"
git push origin main