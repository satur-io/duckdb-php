FROM php:8.4-cli

RUN apt -y update \
    && DEBIAN_FRONTEND=noninteractive apt-get -y --no-install-recommends install libffi-dev time bc curl unzip \
    && docker-php-ext-configure ffi --with-ffi \
    && docker-php-ext-install ffi \
    && docker-php-ext-install bcmath \
    && apt install -y valgrind \
    && apt clean \
    && rm -rf /tmp/* /var/lib/apt/lists/* /var/cache/apt/archives/*

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /usr/src/duckdb

RUN ln -s $(which time) /usr/bin/gtime

RUN curl --fail --location --progress-bar --output duckdb_cli-linux-aarch64.zip https://github.com/duckdb/duckdb/releases/download/v1.2.0/duckdb_cli-linux-aarch64.zip && unzip duckdb_cli-linux-aarch64.zip
RUN ln -s ${PWD}/duckdb /usr/bin/duckdb

WORKDIR /usr/src/duckdb

RUN /usr/bin/composer install
