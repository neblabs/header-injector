#!/usr/bin/env bash

targetDir=$HOME/.local/bin
targetFilePath=$HOME/.local/bin/header-injector

# first make sure the target dir exists
mkdir -p "$targetDir"

curl -sSL https://github.com/neblabs/header-injector/releases/latest/download/header-injector.phar -o "$targetFilePath"

sudo chmod +x "$targetFilePath"
