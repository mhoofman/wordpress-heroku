# Add include for overrides
echo "Include /app/www/conf/httpd.conf" >> /app/apache/conf/httpd.conf

# Heroku boot.sh
for var in `env|cut -f1 -d=`; do
  echo "PassEnv $var" >> /app/apache/conf/httpd.conf;
done
touch /app/apache/logs/error_log
touch /app/apache/logs/access_log
tail -F /app/apache/logs/error_log &
tail -F /app/apache/logs/access_log &
export LD_LIBRARY_PATH=/app/php/ext
export PHP_INI_SCAN_DIR=/app/www
echo "Launching apache"
exec /app/apache/bin/httpd -DNO_DETACH
