#!/bin/bash

# Cleanup any leftovers
rm -f ./channelengine-woocommerce.zip
rm -fR ./deploy

# Create deployment source
echo "Copying plugin source..."
mkdir ./deploy
cp -R ./src ./deploy/channelengine-woocommerce

# Ensure proper composer dependencies
echo "Installing composer dependencies..."
rm -fR ./deploy/channelengine-woocommerce/vendor
composer install --no-dev --working-dir=$PWD/deploy/channelengine-woocommerce/

# Remove unnecessary files from final release archive
cd deploy
echo "Removing unnecessary files from final release archive..."
rm -rf channelengine-woocommerce/tests
rm -rf channelengine-woocommerce/vendor/channelengine/integration-core/tests

# Create plugin archive
echo "Creating new archive..."
zip -q -r channelengine-woocommerce.zip channelengine-woocommerce

cd ../
if [ ! -d ./dist/ ]; then
        mkdir ./dist/
fi

version="$1"
if [ "$version" != "" ]; then
    if [ ! -d ./dist/"$version"/ ]; then
        mkdir ./dist/"$version"/
    fi

    mv ./deploy/channelengine-woocommerce.zip ./dist/${version}/
    touch "./dist/$version/Release notes $version.txt"
    echo "New release created under: $PWD/dist/$version"
else
    if [ ! -d ./dist/dev/ ]; then
        mkdir ./dist/dev/
    fi
    mv ./deploy/channelengine-woocommerce.zip ./dist/dev/
    echo "New plugin archive created: $PWD/dist/dev/channelengine-woocommerce.zip"
fi

rm -fR ./deploy
