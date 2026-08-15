#!/usr/bin/env bash

targetDir=$HOME/.local/bin
targetFilePath=$HOME/.local/bin/header-injector

# first make sure the target dir exists
mkdir -p "$targetDir"

echo "installing versions finder dep"
curl -L https://raw.githubusercontent.com/neblabs/versions-finder/main/install.sh | bash

curl -sSL https://github.com/neblabs/header-injector/releases/latest/download/header-injector.phar -o "$targetFilePath"

sudo chmod +x "$targetFilePath"

# warn if not in path
if ! [[ "$targetDir" == *"/.local/bin"* ]]; then
    echo [warn] Installed to "$targetFilePath" but it "doesn't" seem to be in your PATH.
fi
