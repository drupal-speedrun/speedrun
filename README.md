Root project for the Drupalcon Dublin Speedrun session.

## Install

* Copy `docroot/sites/default/default.settings.local.php` to `docroot/sites/default/settings.local.php` and make any necesarry changes to the $databases array.
* If you don't want to automatically install the features that we will build live, comment out the feature extensions in `docroot/sites/default/lightning.extend.yml`.
* From within docroot, run `$ drush site-install`.

