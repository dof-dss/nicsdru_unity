# Unity sites codebase

[![CircleCI](https://circleci.com/gh/dof-dss/nicsdru_unity.svg?style=svg)](https://circleci.com/gh/dof-dss/nicsdru_unity)

This source code is for the Unity sites. It is built with Drupal 8 in a multisite configuration.

It is hosted on platform.sh.

Continuous Integration and Deployment services are provided by Circle CI.

## Getting started

We recommend Lando for local development. To get started, ensure you have the following installed:

1. Lando [https://docs.devwithlando.io/](https://docs.devwithlando.io/)
2. Composer [https://getcomposer.org/](https://getcomposer.org/)
3. Platform CLI tool [https://docs.platform.sh/development/cli.html](https://docs.platform.sh/development/cli.html)

- Clone this repo
- at the command line, 'cd' into your new directory
- `composer install`
- `lando start`

Or, if available, you may also fetch the database and import this:

`platform db:dump` (you'll need to select project, environment and required site e.g. 'uregni')

`lando db-import -d <sitename/database> -f <downloaded sql file>` (where 'site name' may be 'uregni', 'liofa' etc)

## Running migrations

You will first need to get hold of a Drupal 7 database dump for your chosen site to act as the source of the migration.
We will take Uregni as an example and assume that we have a dump file 'uregni.sql' , this file should be placed in the
imports/data directory.
Ideally, you should also get hold of a Drupal 7 'files' directory and place it in the appropriate imports/files
directory e.g. imports/files/sites/uregni. Note that the path './imports/files/sites/uregni/files/styles' should exist.

1. Import the database into the Drupal 7 database your chosen site. Using our example site this will be 'uregni_legacy'.
Note that the -d sitename must have a '_legacy' suffix, please make sure that you do not overwrite your Drupal 8 database by mistake !:
`lando db-import -d <sitename>_legacy -f <downloaded db>`

2. Install the migrate_upgrade module (listed as 'Drupal Upgrade' at /admin/modules)

3. Make sure that you are in the appropriate site directory e.g. web/sites/uregni and run this command:
`lando drush migrate-upgrade --legacy-db-url=mysql://drupal8:drupal8@database/uregni_legacy --legacy-root=/app/imports/files --configure-only`
(change the db connection database name and the file path if you are migrating another site)

4. Install the migrate_tools module

5. You should now have a long list of migrations in the database, which may be seen by running this command:
`lando drush migrate-status` (from the appropriate site directory e.g. web/sites/uregni)

6. You could choose one of these migrations and run it as follows:
`lando drush migrate-import upgrade_d7_node_type` (from the appropriate site directory e.g. web/sites/uregni)

7. You could then roll back the migration as follows:
`lando drush migrate-rollback upgrade_d7_node_type` (from the appropriate site directory e.g. web/sites/uregni)


## Code workflow

Like the popular git-flow workflow, but without the more complex elements:

- `development` bleeding-edge. All feature branches originate from here.
- `master` stable, automatically deployed to platform.sh. Release tags should originate from here.

Preferred feature branch naming convention: `TICKET_REF-short-desc`, for example: `D8UN-123-event-listing`

We highly recommend developers use a tool such as [Talisman](https://github.com/thoughtworks/talisman) to ensure they do not commit potentially sensitive material into the codebase.

API keys, auth tokens or other credentials values *must* be stored as environment variables and never stored in the codebase itself.

## Talisman pre-commit hooks

- We *strongly* recommend developers use Talisman when working on this project.
- Talisman validates the outgoing change set for things that look suspicious - such as authorization tokens and private keys.

[Installation instructions](https://github.com/thoughtworks/talisman/#installation-as-a-global-hook-template)

## Project structure

Some key project directories and/or files:

```
└── .circleci/ (Circle CI configuration and supporting files)
├── .lando/ (Lando configuration files)
├── .lando.yml + .lando.local.yml (Lando service definition files)
├── .platform/ (platform.sh routes and services config)
├── .platform.app.yaml (platform.sh application config)
├── docker-compose.yml (used by Lando for non-supported services)
├── composer.json (defines project dependencies)
├── composer.lock (what composer install uses when running, ensure this is always in sync with composer.json)
├── config/ (configuration management folder)
├── phpcs.sh (shell script to simplify invocation of PHPCS tool)
├── vendor/ (third party dependencies and libraries; sourced by composer)
├── web/ (docroot folder)
├── web/core (Drupal core; don't alter except via composer patches)
├── web/modules/contrib (community modules; don't alter except via composer patches)
├── web/modules/custom (custom code; sourced from other repository by composer)
├── web/modules/origins (common internal custom modules; sourced from other repository by composer)
├── web/themes/custom/nicsdru_origins_theme (custom base theme) composer)
└── web/sites/sites.php (Drupal multi site config file)
```

## Contribution

All changes **must** be submitted with an appropriate pull request (PR) in GitHub. Direct commits to `master` or `development` are not normally permitted.

## Adding new sites to the multi site codebase

- Set up a new database in .platform/services.yaml (just like 'uregni' or 'liofa')
- Add your new db to the 'relationships' section of .platform.app.yaml
- Add new short site name to the 'deploy' hook loop in .platform.app.yaml (you will see the other sites listed)
- Add new routes to .platform/routes.yaml, one for domain name of the new site and another for the www redirect
(use 'uregni' as an example)
- Create a new directory for your site under web/sites. Note that the directory name should be the first part of the
domain name (short sitename) up until the first dot, so if your domain name is 'uregni.gov.uk' then the directory
name should be just 'uregni'
- Copy a settings.php file into your new web/sites/<short sitename> directory from web/sites/uregni
- Create a new directory /config/sync/<short sitename> and place a .gitkeep file in it so that git recognises the new directory
- Edit the top level .lando.yml file and add a new local site url (with '.lndo.site' suffix) under proxy/appserver
e.g. uregni.gov.uk.lndo.site
- If necessary, add a new Solr core in .lando.yml (just copy uregni_solr and give it a different name, this will be the 'Solr host' when
configuring the server in search_api)
- If necessary, add a new Solr core for Platform.sh in .platform/service.yaml (add a core and an endpoint by copying the config for
'uregni_index' and 'uregni') - after doing this add a new relationship in .platform.app.yaml (following the example of 'uregnisolr')
- When creating your solr server in search_api use 'standard' connector, 'solr' as solr host, and short sitename as solr core - also
under 'Advanced Server Configuration' set the solr.install.dir to '../../..'
- Edit web/sites/sites.lando.php and add a new mapping from your local url (with '.lndo.site' suffix) to the short site name
- N.B. After adding a new site, you will need to run 'lando rebuild' before you can access your new site

Under multi site, Lando commands may be run as follows:
lando drush -l uregni cr
lando -h uregni mysql

After connecting to the Platform server using 'platform ssh', drush commands may be run as follows (in the /app/web directory):
../vendor/bin/drush -l uregni cr

Also, if you run platform CLI commands like 'platform sql' you will be asked to choose between the multi sites.

- N.B. "There can be only one" - because the local Lando site URLs take the form 'uregni.gov.uk.lndo.site' and do not include the
Lando app name, you may only have one set of sites installed on your local machine i.e. it is not possible to clone this repo
into /apps/unity and run 'lando start' and then subsequently clone it into /apps/unity2 and run 'lando start' again as the
site URLs will be duplicated and Lando will attempt to set up 'uregni.gov.uk.lndo.site' pointing to both.


# Licence
Unless stated otherwise, the codebase is released under [the MIT License](http://www.opensource.org/licenses/mit-license.php). This covers both the codebase and any sample code in the documentation.
