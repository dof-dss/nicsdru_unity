# CircleCI

This repository uses CircleCI dynamic configuration.

- `config.yml` verifies the Solr configsets, downloads the shared jobs from
  `dof-dss/webdev-ci`, supplies the project versions and paths, and continues
  the pipeline.
- `workflow-config.yml` selects the shared jobs used by this project.
- `phpstan.neon` configures the disallowed-function analysis.

The pipeline runs on PHP 8.3 against Drupal 11.4. Composer remains the source
of truth for exact package versions.

Validate the setup configuration with:

```shell
circleci config validate .circleci/config.yml
```
