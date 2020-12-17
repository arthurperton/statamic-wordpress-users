# Instructions

Using the WordPress Users addon for Statamic is easy. All steps can be done from the browser.

## Installation

Just browse to the addon in the Control Panel and click install.

And if you'd rather use composer:

 `composer require arthurperton/wordpress-users`

## Where to Find It

Once installed, you will find the *WordPress Users* menu item under the *Users* section in the Control Panel. 

The addon is visible to superusers only by default. If you also like regular users to access the addon too, you can do so. There is a permission available for that.

## Export Users From WordPress

The easiest way to export your users from your WordPress website is using [this free plugin](https://wordpress.org/plugins/import-users-from-csv-with-meta/). After you've installed it, just go the Export tab and click download.

## Import

You'll be guided through the import in three steps.

### Import – Step 1

Upload the CSV export file you just downloaded from your WordPress website.

### Import – Step 2

Configure the fields. If you used the recommended plugin, these should be filled in correctly for you already.

### Import – Step 3 (optional)

Configure the way user roles are imported. For each WordPress role you can select any of the roles and groups you created in Statamic.

### Now Click the Import Button!

The users will be imported right away. 

If any of the users cannot not be imported (because their email address is already in use for example) you can review the details first before continuing or canceling the import.

## Report

After the import you'll see a report with the number of users that were succesfully imported. It also shows the number of users that have logged in into their new account already.

You can always import more users later on if you want to.

## Users

The users will be visible as regular Statamic users. 

In fact they are regular Statamic users, just their passwords are still stored in the WordPress format. They can log in using their WordPress email and password. This password will be converted to the Statamic format on first login. 

You can safely uninstall the addon after all users have logged in once.