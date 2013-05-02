# WordPress Heroku

This project is a template for installing and running [WordPress](http://wordpress.org/) on [Heroku](http://www.heroku.com/). The repository comes bundled with [PostgreSQL for WordPress](http://wordpress.org/extend/plugins/postgresql-for-wordpress/) and [WP Read-Only](http://wordpress.org/extend/plugins/wpro/).

Installation
============

Clone the repository from Github

    $ git clone git://github.com/mhoofman/wordpress-heroku.git

With the [Heroku gem](http://devcenter.heroku.com/articles/heroku-command), create your app

    $ cd wordpress-heroku
    $ heroku create
    > Creating strange-turtle-1234... done, stack is cedar
    > http://strange-turtle-1234.herokuapp.com/ | git@heroku.com:strange-turtle-1234.git
    > Git remote heroku added

Add a database to your app

    $ heroku addons:add heroku-postgresql:dev
    > Adding heroku-postgresql:dev to strange-turtle-1234... done, v2 (free)
    > Attached as HEROKU_POSTGRESQL_COLOR
    > Database has been created and is available
    > Use `heroku addons:docs heroku-postgresql:dev` to view documentation

Promote the database (replace COLOR with the color name from the above output)

    $ heroku pg:promote HEROKU_POSTGRESQL_COLOR
    > Promoting HEROKU_POSTGRESQL_COLOR to DATABASE_URL... done

Create a new branch for any configuration/setup changes needed

    $ git checkout -b production

Copy the `wp-config.php`

    $ cp wp-config-sample.php wp-config.php

Update unique keys and salts in `wp-config.php` on lines 48-55. Wordpress can provide random values [here](https://api.wordpress.org/secret-key/1.1/salt/).

    define('AUTH_KEY',         'put your unique phrase here');
    define('SECURE_AUTH_KEY',  'put your unique phrase here');
    define('LOGGED_IN_KEY',    'put your unique phrase here');
    define('NONCE_KEY',        'put your unique phrase here');
    define('AUTH_SALT',        'put your unique phrase here');
    define('SECURE_AUTH_SALT', 'put your unique phrase here');
    define('LOGGED_IN_SALT',   'put your unique phrase here');
    define('NONCE_SALT',       'put your unique phrase here');

Clear `.gitignore` and commit `wp-config.php`

    $ >.gitignore
    $ git add .
    $ git commit -m "Initial WordPress commit"

Deploy to Heroku

    $ git push heroku production:master
    > -----> Heroku receiving push
    > -----> PHP app detected
    > -----> Bundling Apache v2.2.22
    > -----> Bundling PHP v5.3.10
    > -----> Discovering process types
    >        Procfile declares types -> (none)
    >        Default types for PHP   -> web
    > -----> Compiled slug size is 13.8MB
    > -----> Launcing... done, v5
    >        http://strange-turtle-1234.herokuapp.com deployed to Heroku
    >
    > To git@heroku:strange-turtle-1234.git
    > * [new branch]    production -> master

After deployment WordPress has a few more steps to setup and thats it!

Media Uploads
===
[WP Read-Only](http://wordpress.org/extend/plugins/wpro/) plugin is included in the repository allowing the use of [S3](http://aws.amazon.com/s3/) for media uploads.

1. Activate the plugin under 'Plugins', if not already activated.
2. Input your Amazon S3 credentials in 'Settings'->'WPRO Settings'.


Usage
========

* Because a file cannot be written to Heroku's file system, updating and installing plugins or themes should be done locally and then pushed to Heroku.

Setting up a local environment
===========

## Mac OS X

* To run WordPress locally on Mac OS X try [MAMP](http://codex.wordpress.org/Installing_WordPress_Locally_on_Your_Mac_With_MAMP).
* This template requires Postgres as the local database so install [Postgres.app](http://postgresapp.com/)
* Open psql, from the menubar elephant icon, and run...

```
CREATE DATABASE wordpress;
CREATE USER wordpress WITH PASSWORD 'wordpress';
GRANT ALL PRIVILEGES ON DATABASE wordpress to wordpress;
```

* Open /Applications/MAMP/Library/bin/envvars and add `export DATABASE_URL="postgres://wordpress:wordpress@localhost:5432/wordpress"`
* Start MAMP and open http://localhost/wp-admin/ in a browser.

## Linux, or manual Apache config

* Install Postgres according to your package manager or from source
* Execute the following commands in psql interactive shell...

```
CREATE DATABASE wordpress;
CREATE USER wordpress WITH PASSWORD 'wordpress';
GRANT ALL PRIVILEGES ON DATABASE wordpress to wordpress;
```

* In your Apache config, add a `SetEnv` directive like `SetEnv DATABASE_URL postgres://wordpress:wordpress@localhost:5432/wordpress`
* Change the first line of your `wp-config.php` to use `$_SERVER["DATABASE_URL"]` if `DATABASE_URL` not found in `$_ENV`:

```
if (isset($_ENV["DATABASE_URL"]))
  $db = parse_url($_ENV["DATABASE_URL"]);
else
  $db = parse_url($_SERVER["DATABASE_URL"]);

```

* (Re)start Apache, and open http://localhost/wp-admin in a browser.


Updating
========

Updating your WordPress version is just a matter of merging the updates into
the branch created from the installation.

    $ git pull # Get the latest

Using the same branch name from our installation:

    $ git checkout production
    $ git merge master # Merge latest
    $ git push heroku production:master

WordPress needs to update the database. After push, navigate to:

    http://your-app-url.herokuapp.com/wp-admin

WordPress will prompt for updating the database. After that you'll be good
to go.

Custom Domains
==============

Heroku allows you to add custom domains to your site hosted with them.  To add your custom domain, enter in the follow commands.

    $ heroku domains:add www.example.com
    > Added www.example.com as a custom domain name to myapp.heroku.com

You'll also want to cover the non "www" side of the url.

    $ heroku domains:add example.com
    > Added example.com as a custom domain name to myapp.heroku.com

Once Heroku recognizes your custom domain(s) you'll then need to setup separate DNS A records for the following ip addresses to point to your domain:

    75.101.163.44
    75.101.145.87
    174.129.212.2

Once the DNS A records propagate, then simply test out your change by hitting the url in the browser to make sure you are good to go.  If you are in need of cheap DNS hosting then I would recommend [DNSimple](https://dnsimple.com/r/571e28804df06f).

The last step is updating your WordPress installation to recognize the new domain.  You'll need to open up the WordPress Admin Dashboard and go to Settings --> General.  From there just update the URL for the WordPress address and you're done.

If you find yourself running into problems, there is a guide posted in the Heroku Docs which can be found [here](https://devcenter.heroku.com/articles/custom-domains).
