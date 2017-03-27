##########################################################
#                                                        #
#  1. Build the image:                                   #
#     docker build -t wordpress-heroku .                 #
#                                                        #
#  2. Run the container:                                 #
#     docker run -ti --rm -v $(pwd):/var/www/html      \ #
#       -p 80:80 wordpress-heroku                        #
#                                                        #
#  3. Visit localhost:80 in browser                      #
#                                                        #
##########################################################


FROM debian:latest
MAINTAINER Berin larson004@gmail.com

RUN apt-get update && apt-get -y install apache2 libapache2-mod-php5
RUN apt-get -y install postgresql php5-pgsql
RUN a2enmod php5

ENV APACHE_RUN_USER postgres
ENV APACHE_RUN_GROUP postgres
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid
ENV APACHE_RUN_DIR /var/run/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2

EXPOSE 80
EXPOSE 5432

RUN echo "listen_addresses='*'" >> /etc/postgresql/9.4/main/postgresql.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY docker/pg_hba.conf /etc/postgresql/9.4/main/pg_hba.conf

# Copying entrypoint script and setting proper permission.
COPY docker/entrypoint.sh /
RUN chmod +x /entrypoint.sh

ENTRYPOINT /entrypoint.sh
