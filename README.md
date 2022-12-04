#
# 
# Requirements
#
#

- Docker
- Python 3 + PIP

#
# 
# Installation
#
#

## Create virtual hosts file (optional)

Add *127.0.0.1 api.cc.local* to hosts file
## Create .env/options

Copy *cp .env.example .env* and modify.
Copy *cp options.example.php options.php* and modify.

## Setup docker image(s):

```bash
docker-compose up -d --build
cmd /c "docker exec -i ccapi-db mysql -u root -pPASSWORD_HERE cc < ./database/init.sql"
```

## Setup PHP:

```bash
php run serve
```

## Add PHP run script to cronjob (optional)

```bash
chmod +x /PATH_TO_WWW/run
crontab -e
*/1 * * * * /usr/bin/php /PATH_TO_WWW/run crontab
```

#
# 
# Development
#
#

```bash
php run serve
```
