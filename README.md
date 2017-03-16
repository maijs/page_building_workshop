# Drupal 8 page building workshop

The goal of the workshop is to utilize Page manager and Panels to build pages in a Drupal 8 project.

## Usage

First of all, create a Mysql database. Create your project using the following commands:

```
composer create-project maijs/drupal-project:8.x-dev page_building_workshop --stability dev --no-interaction
cd page_building_workshop
composer install
cd web
drush site-install standard --db-url=mysql://[username]:[password]@[host]/[database-name]
```
