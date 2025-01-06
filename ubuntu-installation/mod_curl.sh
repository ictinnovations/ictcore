#!/bin/bash

# Debugging: Check if directory exists
MODULE_PATH="/usr/local/src/freeswitch-1.10.11.-release/src/mod/applications/mod_curl"

if [ -d "$MODULE_PATH" ]; then
  echo "Directory exists: $MODULE_PATH"
else
  echo "Error: Directory does not exist: $MODULE_PATH"
  exit 1
fi

# Continue with installation
cd "$MODULE_PATH"
./configure
./configure --enable-core-odbc-support --enable-core-pgsql-support
make
make install

