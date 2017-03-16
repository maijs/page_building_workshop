# Drupal 8 page building workshop

The goal of the workshop is to utilize Page manager and Panels to build pages in a Drupal 8 project.

## Prerequisites

You should have the following installed on your system:

- PHP and composer
- Mysql

## Setup

Clone the repository.

```
git clone https://github.com/maijs/page_building_workshop.git
```

Install composer dependencies.

```
cd page_building_workshop
composer install
```

Create Mysql Database.

```
mysql -u [USERNAME] -p -e "create database page_building_workshop"
```

Enter your Mysql password and database will be created.

Install Drupal using Mysql database.

```
cd web
drush site-install standard -y --account-name=admin --account-pass=admin1234 --db-url=mysql://[username]:[password]@[host]/page_building_workshop
```

Enable `migrate_source_example_csv` module and migrate the content to have content to work with.

```
drush en migrate_source_example_csv panels page_manager page_manager_ui -y
drush mi --group=migrate_source_example_csv

```

Run Drupal with the PHP built-in server, by default it will be accessible under http://127.0.0.1:8888
```
drush runserver
```

Further instructions will be given during the workshop.
