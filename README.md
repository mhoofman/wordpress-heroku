# WordPress Heroku

This project is a template for getting [WordPress](http://wordpress.org/) up and running on [Heroku](http://www.heroku.com/). It comes with [PostgreSQL for WordPress](http://wordpress.org/extend/plugins/postgresql-for-wordpress/) pre-installed in order to use Heroku's existing Postgres backend.

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

Usage
========

* Because a file cannot be written to Heroku's file system, updating and installing plugins or themes should be done locally and then pushed to Heroku.
* To run WordPress locally on Mac OS X try [MAMP](http://codex.wordpress.org/Installing_WordPress_Locally_on_Your_Mac_With_MAMP).

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
