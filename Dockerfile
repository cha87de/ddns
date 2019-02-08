# select operating system
FROM ubuntu:18.04

# install operating system packages 
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update ; apt-get install -y git make gettext bind9 dnsutils apache2 libapache2-mod-php

# use bpkg to handle complex bash entrypoints
RUN curl -Lo- "https://raw.githubusercontent.com/bpkg/bpkg/master/setup.sh" | bash
RUN bpkg install cha87de/bashutil -g

# copy php app
RUN rm -rf /var/www/html
ADD www /var/www/html

# add config and init files 
ADD config /etc/docker-config
ADD init /opt/docker-init

VOLUME /opt/data/

# start from init folder
WORKDIR /opt/docker-init
ENTRYPOINT ["./entrypoint"]
