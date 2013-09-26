# WordPress Heroku

This project is a template for installing and running [WordPress](http://wordpress.org/) on [Heroku](http://www.heroku.com/). The repository comes bundled with [PostgreSQL for WordPress](http://wordpress.org/extend/plugins/postgresql-for-wordpress/) and [WP Read-Only](http://wordpress.org/extend/plugins/wpro/).

## Installation

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

## Media Uploads

[WP Read-Only](http://wordpress.org/extend/plugins/wpro/) plugin is included in the repository allowing the use of [S3](http://aws.amazon.com/s3/) for media uploads.

1. Activate the plugin under 'Plugins', if not already activated.
2. Input your Amazon S3 credentials in 'Settings'->'WPRO Settings'.


## Usage

Because a file cannot be written to Heroku's file system, updating and installing plugins or themes should be done locally and then pushed to Heroku.

## Setting up a local environment

### Mac OS X

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

### Linux, or manual Apache config

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

### Postgres Database syncing

In your local environment you often need to import the current production database. So far I haven't found any good tool compareable to RoR "db:migrate" to sync databases. For now the best workflow for doing changes is:

1. Setup empty local wordpress (see above)
2. Export heroku pg db
3. Import db into local wordpress
4. Do your changes, install plugins, etc.
5. Test
6. Put live site into maintainance mode (no new posts, comments, etc)
7. redo from step 2
8. export local pg db
9. import local pg into heroku db


#### Export Postgres Heroku to local

To import and export Postgres we use the tool pg_dump and herokus pgbackups function ( https://devcenter.heroku.com/articles/heroku-postgres-import-export )

```
$ cd YOURAPP-folder/
YOURAPP-folder$ sudo heroku pgbackups:capture -a YOURAPP --expire
[sudo] password for startup: 

HEROKU_POSTGRESQL_BLUE_URL (DATABASE_URL)  ----backup--->  b003

Pending...  |
```

Once this is done you will see it and you will be able to download the dump from a temporary url into a local file. 
```
Capturing... done
Storing... done

YOURAPP-folder$ curl -o latest.dump `heroku pgbackups:url`
```

This file is a compressed pg_dump file, which we can now import to our local db:

```
YOURAPP-folder$ pg_restore --verbose --clean --no-acl --no-owner -h localhost -U wordpress -d wordpress latest.dump
```



#### Export local dev copy to Heroku
## Updating

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

## Custom Domains

For settings up a custom domain refer to the [Heroku Docs](https://devcenter.heroku.com/articles/custom-domains).
