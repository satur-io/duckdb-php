#!/bin/bash
set -euo pipefail

platforms=("linux-arm64" "linux-amd64" "osx-universal" "windows-amd64" "windows-arm64")
default_release=$(php -r "include 'config.php'; echo constant('DUCKDB_PHP_LIB_DEFAULT_VERSION');")
supported_releases=$(php -r "include 'config.php'; echo implode(' ', constant('DUCKDB_PHP_SUPPORTED_VERSIONS'));")

releases=()
if [[ "${1:-}" == "--all" || "${1:-}" == "-a" ]]; then
  read -r -a releases <<< "${supported_releases}"
else
  release="${1:-$default_release}"
  releases=("${release}")
fi

for release in "${releases[@]}"; do
  rm -rf "./header/${release}"

  for platform in "${platforms[@]}"; do
    tmp_dir="/tmp/duckdb-${release}-${platform}"
    mkdir -p "${tmp_dir}"
    mkdir -p "./header/${release}/${platform}"

    curl -sSL "https://github.com/duckdb/duckdb/releases/download/v${release}/libduckdb-${platform}.zip" > "libduckdb-${platform}.zip"
    unzip "libduckdb-${platform}.zip" -d "${tmp_dir}"
    rm -f "libduckdb-${platform}.zip"

    sed -i.bak '/#include <std/d'  "${tmp_dir}/duckdb.h"
    cpp -w -P -C -D"attribute(ARGS)=" "${tmp_dir}/duckdb.h" > "${tmp_dir}/duckdb-ffi.h"
    cp "${tmp_dir}/duckdb-ffi.h" "./header/${release}/${platform}/duckdb-ffi.h"
    rm -rf "${tmp_dir}"
  done

  if [[ "${release}" == "${default_release}" ]]; then
    for platform in "${platforms[@]}"; do
      mkdir -p "./header/${platform}"
      cp "./header/${release}/${platform}/duckdb-ffi.h" "./header/${platform}/duckdb-ffi.h"
    done
  fi
done
