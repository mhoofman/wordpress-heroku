service postgresql start
psql -U postgres -c "CREATE DATABASE wordpress;"
psql -U postgres -c  "CREATE USER wordpress WITH PASSWORD 'wordpress'; GRANT ALL PRIVILEGES ON DATABASE wordpress to wordpress;"
usr/sbin/apache2 -D FOREGROUND
tail
