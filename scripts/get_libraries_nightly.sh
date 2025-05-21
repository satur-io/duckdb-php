#!/bin/bash

platforms=("osx" "linux-amd64" "linux-arm64" "windows" "windows")
platformLibZip=("osx-universal" "linux-amd64" "linux-arm64" "windows-amd64" "windows-arm64")
platformLibFiles=("libduckdb.dylib" "libduckdb.so" "libduckdb.so" "duckdb.dll" "duckdb.dll")

rm -rf ./lib_nightly
mkdir ./lib_nightly

counter=0
for platform in "${platforms[@]}"; do
  mkdir -p "/tmp/${platform}"
  mkdir -p "./lib_nightly/${platformLibZip[${counter}]}"
  curl -sSL "https://artifacts.duckdb.org/latest/duckdb-binaries-${platform}.zip" > "libduckdb-binary-${platform}.zip"
  unzip "libduckdb-binary-${platform}.zip" -d "/tmp/${platform}-binaries"
  unzip "/tmp/${platform}-binaries/libduckdb-${platformLibZip[${counter}]}" -d "/tmp/${platform}"
  rm -f "libduckdb-binary-${platform}.zip"

  sed -i.bak '/#include <std/d'  "/tmp/${platform}/duckdb.h"
  (echo "#define FFI_SCOPE \"DUCKDB\""; echo "#define FFI_LIB \"/tmp/${platform}/${platformLibFiles[${counter}]}\"") >> "/tmp/${platform}/duckdb-ffi.h"
  cpp -P -C -D"attribute(ARGS)=" "/tmp/${platform}/duckdb.h" >> "/tmp/${platform}/duckdb-ffi.h"
  cp "/tmp/${platform}/${platformLibFiles[${counter}]}" "./lib_nightly/${platformLibZip[${counter}]}/${platformLibFiles[${counter}]}"
  cp "/tmp/${platform}/duckdb-ffi.h" "./lib_nightly/${platformLibZip[${counter}]}/duckdb-ffi.h"
  rm -rf "/tmp/${platform}" "/tmp/${platform}-binaries"
  counter=${counter}+1
done
