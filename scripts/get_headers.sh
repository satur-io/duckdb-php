#!/bin/bash

platforms=("linux-arm64" "linux-amd64" "osx-universal" "windows-amd64" "windows-arm64")
release=$(php -r "include 'config.php'; echo constant('DUCKDB_PHP_LIB_VERSION');")

rm -rf ./header

counter=0
for platform in "${platforms[@]}"; do
  mkdir -p "/tmp/${platform}"
  mkdir -p "./header/${platform}"
  curl -sSL "https://github.com/duckdb/duckdb/releases/download/v${release}/libduckdb-${platform}.zip" > "libduckdb-${platform}.zip"
  unzip "libduckdb-${platform}.zip" -d "/tmp/${platform}"
  rm -f "libduckdb-${platform}.zip"

  sed -i.bak '/#include <std/d'  "/tmp/${platform}/duckdb.h"
  cpp -P -C -D"attribute(ARGS)=" "/tmp/${platform}/duckdb.h" >> "/tmp/${platform}/duckdb-ffi.h"
  cp "/tmp/${platform}/duckdb-ffi.h" "./header/${platform}/duckdb-ffi.h"
  rm -rf "/tmp/${platform}"
  counter=${counter}+1
done
