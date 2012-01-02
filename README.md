# WordPress Heroku

This project is a template for getting [WordPress](http://wordpress.org/) up and running on [Heroku](http://www.heroku.com/). It comes with [PostgreSQL for WordPress](http://wordpress.org/extend/plugins/postgresql-for-wordpress/) pre-installed in order to use Heroku's existing Postgres backend.

Installation
============

Clone the repository from Github

    $ git clone git://github.com/mhoofman/wordpress-heroku.git
    
With the [Heroku gem](http://devcenter.heroku.com/articles/heroku-command), create your app

    $ cd wordpress-heroku
    $ heroku create --stack cedar
    > Creating strange-turtle-1234... done, stack is cedar
    > http://strange-turtle-1234.herokuapp.com/ | git@heroku.com:strange-turtle-1234.git
    > Git remote heroku added

Add a database to your app

    $ heroku addons:add shared-database:5mb
    > -----> Adding shared-database:5mb to strange-turtle-1234... done, v3 (free)

Create a new branch for any configuration/setup changes needed

    $ git checkout -b production
    
Copy the `wp-config.php`

    $ cp wp-config-sample.php wp-config.php

Clear `.gitignore` and commit `wp-config.php`

    $ >.gitignore
    $ git add .
    $ git commit -m "zomg wordpress"
    
Deploy to Heroku

    $ git push heroku production:master
    > -----> Heroku receiving push
    > -----> PHP app detected
    > -----> Bundling Apache v2.2.19
    > -----> Bundling PHP v5.3.6
    > -----> Discovering process types
    >        Procfile declares types -> (none)
    >        Default types for PHP   -> web
    > -----> Compiled slug size is 24.9MB
    > -----> Launcing... done, v5
    >        http://strange-turtle-1234.herokuapp.com deployed to Heroku
    >
    > To git@heroku:strange-turtle-1234.git
    > * [new branch]    production -> master 

After deployment WordPress has a few more steps to setup and thats it!

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
