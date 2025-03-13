# Setting the php in the docker container
FROM ghcr.io/islamic-network/php:8.3-cli

# install stuff that we want
RUN  ACCEPT_EULA=Y && apt -y update && apt install -y wget unzip curl

# Copy files
COPY . /var/www/

# Run Composer
RUN cd /var/www && composer install --no-dev

# Delete stuff we do not need
RUN rm -rf /var/www/.git
RUN rm -rf /var/www/.gitignore

WORKDIR /var/www


