## local
cd /Users/ROMAIN/Sites/rag-project/cms
chmod +x ./ci/deploy-ddev-local-dev.sh; ./ci/deploy-ddev-local-dev.sh

## remote
cd /var/www/websites.promptosaur.xyz
chmod +x ./ci/deploy-remote.sh; ./ci/deploy-remote.sh

romain-dortier.websites.promptosaur.xyz
ai-blog.websites.promptosaur.xyz
humains.websites.promptosaur.xyz
alice-ecole.websites.promptosaur.xyz
tourisme-scientifique.websites.promptosaur.xyz

*/15 * * * * /var/www/websites.promptosaur.xyz/vendor/bin/drush --quiet cron
10 * * * * /var/www/psaur_test_instances/1/vendor/bin/drush --quiet cron

certbot certonly --webroot -w /var/www/websites.promptosaur.xyz/web/ -d clap-studio.websites.promptosaur.xyz -d audio-content-creator.websites.promptosaur.xyz