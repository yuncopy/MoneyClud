FROM hub.c.163.com/public/nginx:1.2.1
MAINTAINER yuncopy <yuncopy@sina.com>

RUN apt-get update && \
apt-get install -y vim && \
mkdir -p /home/www && \
cd /etc/nginx/ && cp -a nginx.conf nginx.confbak && \
useradd nginx

WORKDIR /home/www

COPY nginx.conf /etc/nginx/nginx.conf

