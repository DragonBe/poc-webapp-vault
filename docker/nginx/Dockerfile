FROM nginx:latest

RUN rm /etc/nginx/conf.d/default.conf
COPY conf/nginx.conf /etc/nginx/conf.d/site.conf
VOLUME /var/www/html/site/web
VOLUME /var/www/html/site/data
VOLUME /var/www/html/site/vendor
EXPOSE 80
