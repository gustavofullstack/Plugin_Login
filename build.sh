#!/bin/bash

# Configuration
PLUGIN_SLUG="udi-custom-login"
VERSION="1.0.4"
BUILD_DIR="build"
ZIP_NAME="${PLUGIN_SLUG}-v${VERSION}.zip"

# clean previous build
rm -rf "$BUILD_DIR"
rm -f "$ZIP_NAME"

echo "🚀 Starting build for $ZIP_NAME..."

# Create build directory structure
mkdir -p "$BUILD_DIR/$PLUGIN_SLUG"

# Copy files using rsync with excludes from .distignore
echo "📂 Copying files..."
rsync -av --progress . "$BUILD_DIR/$PLUGIN_SLUG" --exclude-from='.distignore' --exclude="$BUILD_DIR"

# Install production dependencies
echo "📦 Installing production dependencies..."
cd "$BUILD_DIR/$PLUGIN_SLUG"

# Manually copy composer files since they are in .distignore
if [ -f "../../composer.json" ]; then
    cp ../../composer.json .
    if [ -f "../../composer.lock" ]; then
        cp ../../composer.lock .
    fi

    echo "   Running composer install..."
    if [ -f "../../composer.phar" ]; then
        php ../../composer.phar install --no-dev --optimize-autoloader --no-progress
    else
        composer install --no-dev --optimize-autoloader --no-progress
    fi
    
    # ✂️ SURGICAL CLEANUP OF GOOGLE SDK
    echo "✂️  Stripping unused Google Services..."
    GOOGLE_SERVICE_DIR="vendor/google/apiclient-services/src/Google/Service"
    
    if [ -d "$GOOGLE_SERVICE_DIR" ]; then
        # Identify services we MIGHT need. 
        # Typically "Oauth2" is needed for Login.
        # We will DELETE everything else.
        
        find "$GOOGLE_SERVICE_DIR" -mindepth 1 -maxdepth 1 -type d -not -name "Oauth2" -exec rm -rf {} +
        
        echo "✅ Google Services cleaned. Kept only Oauth2."
    else
        echo "⚠️  Google Service directory not found. Skipping cleanup."
    fi
    
    # Remove composer files from final build artifact to keep it clean (optional, keeping json is fine for versioning)
    rm composer.json composer.lock
else
    echo "⚠️  ../../composer.json not found, skipping dependency install."
fi

# Clean up any system files that might have snuck in
find . -name ".DS_Store" -delete

cd ../..

# Zip it up
echo "pkg Compressing to $ZIP_NAME..."
cd "$BUILD_DIR"
zip -r -q "../$ZIP_NAME" "$PLUGIN_SLUG"
cd ..

# Cleanup build dir
rm -rf "$BUILD_DIR"

echo "✅ Build Complete: $ZIP_NAME"
echo "🎉 Ready for upload!"
