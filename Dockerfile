# Setting the php in the docker container
FROM ghcr.io/islamic-network/php:8.3-unit-dev

# Copy files
COPY . /var/www/

# Run Composer
RUN cd /var/www && composer install --no-dev

# Delete stuff we do not need
RUN rm -rf /var/www/.git
RUN rm -rf /var/www/.gitignore

# install stuff that we want
RUN apt update
RUN apt install wget -y
RUN apt install dpkg
RUN apt install unzip
RUN apt install curl -y

RUN cd /home && \
    wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb && \
    apt install ./google-chrome-stable_current_amd64.deb -y && \
    wget https://storage.googleapis.com/chrome-for-testing-public/134.0.6998.35/linux64/chromedriver-linux64.zip && \
    unzip chromedriver-linux64.zip && \
    cd chromedriver-linux64 && \
    mv chromedriver /usr/bin/chromedriver && \
    chown root:root /usr/bin/chromedriver && \
    chmod +x /usr/bin/chromedriver

WORKDIR /var/www


