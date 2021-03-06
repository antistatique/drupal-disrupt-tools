# Developing on Disrupt Tools

* Issues should be filed at
https://www.drupal.org/project/issues/disrupt_tools
* Pull requests can be made against
https://github.com/antistatique/drupal-disrupt-tools/pulls

## 🔧 Prerequisites

First of all, you need to have the following tools installed globally
on your environment:

  * drush
  * Latest dev release of Drupal 8.x.

## 🏆 Tests

  ```bash
  $ cd core
  $ ../../vendor/bin/phpunit --group disrupt_tools
  ```

## 🚔 Check Drupal coding standards & Drupal best practices

You need to run composer before using PHPCS. Then register the Drupal
and DrupalPractice Standard with PHPCS:
`./vendor/bin/phpcs --config-set installed_paths
`pwd`/vendor/drupal/coder/coder_sniffer`

### Command Line Usage

Check Drupal coding standards:

  ```
  $ ./vendor/bin/phpcs --standard=Drupal --colors
  --extensions=php,module,inc,install,test,profile,theme,css,info,md
  --ignore=*/vendor/* ./
  ```

Check Drupal best practices:

  ```
  $ ./vendor/bin/phpcs --standard=DrupalPractice --colors
  --extensions=php,module,inc,install,test,profile,theme,css,info,md
  --ignore=*/vendor/* ./
  ```

Automatically fix coding standards

  ```
  $ ./vendor/bin/phpcbf --standard=Drupal --colors
  --extensions=php,module,inc,install,test,profile,theme,css,info
  --ignore=*/vendor/* ./
  ```

### Enforce code standards with git hooks

Maintaining code quality by adding the custom post-commit hook to yours.

  ```
  $ cat ./scripts/hooks/post-commit >> ./.git/hooks/post-commit
  ```
