![GitHub release](https://flat.badgen.net/github/release/arthurperton/statamic-wordpress-users)
![Statamic](https://flat.badgen.net/badge/Statamic/3.0+/FF269E)

# WordPress Users
With this Statamic addon you can import your WordPress users and let them log in with their original passwords.

## Features

* Import your WordPress users into Statamic
* Users can log in with their original passwords right away
* Optionally map roles to Statamic roles and groups
* Full passwords conversion on first login
* The addon can be safely uninstalled after all users have logged in once

## How To
### Installation

You can install the addon using composer

 `composer require arthurperton/wordpress-users`
 
 or you can just browse to the addon in the Control Panel and click install.

### Where to Find It

Once installed, you will find the *WordPress Users* menu item under the *Users* section in the Control Panel. 

The addon is visible to superuser only by default. If you also like regular users to access the addon too, you can do so. There is a permission available for that.

### Export Users From WordPress

The easiest way to export your users is using  [this free plugin](https://wordpress.org/plugins/import-users-from-csv-with-meta/). After you've installed it, just go the Export tab and click download.

### Import – Step 1

Upload the CSV export file you just created.

### Import – Step 2

Configure the fields. If you used the recommended plugin, these should be filled in correctly for you already.

### Import – Step 3 (optional)

Configure the way user roles are imported. For each WordPress role you can select any of the roles and groups you created in Statamic.

### Now Click the Import Button!

The users will be imported right away. 

If any of the users cannot not be imported (because their email address is already in use for example) you can review the details first before continuing.

### Report

After the import you'll see a report with the number of users that were succesfully imported. It also shows the number of users that have logged in into their new account already.

You can always import more users later on if you want to.

## Requirements

* Statamic 3
* PHP &gt;= 7.4

## License
WordPress Users is **commercial software** but has an open-source codebase. If you want to use it in production, you'll need to [buy a license from the Statamic Marketplace](https://statamic.com/addons/arthurperton/wordpress-users).
>WordPress Users is **NOT** free software.

## Credits
Developed by [Arthur Perton](https://www.webenapp.nl)