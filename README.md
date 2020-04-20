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

- Make a copy of .lando.example.yml naming it .lando.local.yml and edit with your own unique project name.
- `lando start`

Or, if available, you may also fetch the database and import this:

`platform db:dump --stdout unity-db.sql | lando db-import unity-db.sql` (you'll need to specify project ID and environment ID)

Avoid retrieving any managed files. You should be accessing these via the stage_file_proxy module. If this isn't working,
 check the configuration for it.

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

## Adding new sites to the multi site codebase for Lando
- Create a new directory for your site under web/sites. Note that the directory name should be the first part of the 
  domain name (short sitename) up until the first dot, so if your domain name is 'uregni.gov.uk' then the directory 
  name should be just 'uregni'.
- Copy a settings.php file into your new web/sites/<short sitename> directory from web/sites/uregni
- Edit the top level .lando.yml file and add a new local site url under proxy/appserver e.g. uregni.gov.uk.lndo.site
- Edit the top level .lando.yml file and add a new database under 'services' (see 'uregni' as an example and make sure that you set 
all of the credentials to 'drupal8' as has been done with the other sites)
- Edit web/sites/sites.php and add a new mapping from your local url to the short site name.
- Create a new directory /config/sync/<short sitename> and place a .gitkeep file in it so that git recognises the new directory



## Contribution

All changes **must** be submitted with an appropriate pull request (PR) in GitHub. Direct commits to `master` or `development` are not normally permitted.

# Licence
Unless stated otherwise, the codebase is released under [the MIT License](http://www.opensource.org/licenses/mit-license.php). This covers both the codebase and any sample code in the documentation.
