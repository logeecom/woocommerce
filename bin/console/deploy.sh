#!/bin/bash

# Cleanup any leftovers
rm -f ./channelengine-wc.zip
rm -fR ./deploy

# Create deployment source
echo "Copying plugin source..."
mkdir ./deploy
cp -R ./src ./deploy/channelengine-wc

# Ensure proper composer dependencies
echo "Installing composer dependencies..."
rm -fR ./deploy/channelengine-wc/vendor
composer install --no-dev --working-dir=$PWD/deploy/channelengine-wc/

# Remove unnecessary files from final release archive
cd deploy
echo "Removing unnecessary files from final release archive..."
rm -rf channelengine-wc/tests
rm -rf channelengine-wc/vendor/channelengine/integration-core/tests
rm -fR channelengine-wc/vendor/channelengine/integration-core/.git
rm -fR channelengine-wc/vendor/channelengine/integration-core/.gitignore
rm -fR channelengine-wc/vendor/channelengine/integration-core/run-tests.sh
rm -fR channelengine-wc/resources/channelengine/.gitkeep
rm -fR channelengine-wc/resources/fonts/.gitkeep

# Create plugin archive
echo "Creating new archive..."
zip -q -r channelengine-wc.zip channelengine-wc

cd ../
if [ ! -d ./dist/ ]; then
        mkdir ./dist/
fi

version="$1"
if [ "$version" != "" ]; then
    if [ ! -d ./dist/"$version"/ ]; then
        mkdir ./dist/"$version"/
    fi

    mv ./deploy/channelengine-wc.zip ./dist/${version}/
    touch "./dist/$version/Release notes $version.txt"
    echo "New release created under: $PWD/dist/$version"
else
    if [ ! -d ./dist/dev/ ]; then
        mkdir ./dist/dev/
    fi
    mv ./deploy/channelengine-wc.zip ./dist/dev/
    echo "New plugin archive created: $PWD/dist/dev/channelengine-wc.zip"
fi

rm -fR ./deploy
