#!/bin/bash

platforms=("linux-arm64" "linux-amd64" "osx-universal" "windows-amd64" "windows-arm64")
platformLibFiles=("libduckdb.so" "libduckdb.so" "libduckdb.dylib" "duckdb.dll" "duckdb.dll")
release="1.3.2"

rm -rf ./lib

counter=0
for platform in "${platforms[@]}"; do
  mkdir -p "/tmp/${platform}"
  mkdir -p "./lib/${platform}"
  curl -sSL "https://github.com/duckdb/duckdb/releases/download/v${release}/libduckdb-${platform}.zip" > "libduckdb-${platform}.zip"
  unzip "libduckdb-${platform}.zip" -d "/tmp/${platform}"
  rm -f "libduckdb-${platform}.zip"

  sed -i.bak '/#include <std/d'  "/tmp/${platform}/duckdb.h"
  (echo "#define FFI_SCOPE \"DUCKDB\""; echo "#define FFI_LIB \"/tmp/${platform}/${platformLibFiles[${counter}]}\"") >> "/tmp/${platform}/duckdb-ffi.h"
  cpp -P -C -D"attribute(ARGS)=" "/tmp/${platform}/duckdb.h" >> "/tmp/${platform}/duckdb-ffi.h"
  sed -i \~ "s/#define FFI_LIB \"\/tmp\/${platform}\/${platformLibFiles[${counter}]}\"/#define FFI_LIB \"lib\/${platform}\/${platformLibFiles[${counter}]}\"/" /tmp/${platform}/duckdb-ffi.h
  cp "/tmp/${platform}/${platformLibFiles[${counter}]}" "./lib/${platform}/${platformLibFiles[${counter}]}"
  cp "/tmp/${platform}/duckdb-ffi.h" "./lib/${platform}/duckdb-ffi.h"
  rm -rf "/tmp/${platform}"
  counter=${counter}+1
done
