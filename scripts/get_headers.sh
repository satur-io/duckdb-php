#!/bin/bash

platforms=("linux-arm64" "linux-amd64" "osx-universal" "windows-amd64" "windows-arm64")
release=$(php -r "include 'config.php'; echo constant('DUCKDB_PHP_LIB_VERSION');")

rm -rf ./header

counter=0
for platform in "${platforms[@]}"; do
  mkdir -p "/tmp/${platform}"
  mkdir -p "./header/${platform}"
  curl -sSL "https://github.com/duckdb/duckdb/releases/download/v${release}/duckdb-shared-libs-${platform}.tar.gz" > "duckdb-shared-libs-${platform}.tar.gz"
  tar -xzf "duckdb-shared-libs-${platform}.tar.gz" -C "/tmp/${platform}"
  rm -f "duckdb-shared-libs-${platform}.tar.gz"

  sed -i.bak '/#include <std/d'  "/tmp/${platform}/duckdb.h"
  cpp -w -P -C -D"attribute(ARGS)=" -D"__attribute__(ARGS)=" -D"__declspec(ARGS)=" "/tmp/${platform}/duckdb.h" >> "/tmp/${platform}/duckdb-ffi.h"
  cp "/tmp/${platform}/duckdb-ffi.h" "./header/${platform}/duckdb-ffi.h"
  rm -rf "/tmp/${platform}"
  counter=${counter}+1
done
