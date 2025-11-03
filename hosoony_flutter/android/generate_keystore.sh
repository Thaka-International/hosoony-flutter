#!/bin/bash

# Hosoony Flutter App - Keystore Generation Script
# This script generates a release keystore for signing Android releases

echo "🔐 Generating Release Keystore for Hosoony App"
echo "=============================================="
echo ""

# Create keystore directory if it doesn't exist
mkdir -p android/keystore

# Set keystore path
KEYSTORE_PATH="android/keystore/hosoony-release-key.jks"

# Check if keystore already exists
if [ -f "$KEYSTORE_PATH" ]; then
    echo "⚠️  WARNING: Keystore already exists at $KEYSTORE_PATH"
    read -p "Do you want to overwrite it? (yes/no): " overwrite
    if [ "$overwrite" != "yes" ]; then
        echo "❌ Keystore generation cancelled."
        exit 1
    fi
fi

# Prompt for keystore information
echo "Please provide the following information:"
echo ""
read -sp "Enter keystore password (min 6 characters): " STORE_PASSWORD
echo ""
read -sp "Re-enter keystore password: " STORE_PASSWORD_CONFIRM
echo ""

if [ "$STORE_PASSWORD" != "$STORE_PASSWORD_CONFIRM" ]; then
    echo "❌ Passwords do not match!"
    exit 1
fi

if [ ${#STORE_PASSWORD} -lt 6 ]; then
    echo "❌ Password must be at least 6 characters!"
    exit 1
fi

read -sp "Enter key password (can be same as keystore password): " KEY_PASSWORD
echo ""
read -p "Enter your full name (for certificate): " FULL_NAME
read -p "Enter your organization name (e.g., Thaka International): " ORGANIZATION
read -p "Enter your organizational unit (e.g., Development): " ORG_UNIT
read -p "Enter your city: " CITY
read -p "Enter your state/province: " STATE
read -p "Enter your country code (2 letters, e.g., SA): " COUNTRY

echo ""
echo "Generating keystore..."

# Generate keystore
keytool -genkey -v -keystore "$KEYSTORE_PATH" \
    -alias hosoony-release-key \
    -keyalg RSA \
    -keysize 2048 \
    -validity 10000 \
    -storepass "$STORE_PASSWORD" \
    -keypass "$KEY_PASSWORD" \
    -dname "CN=$FULL_NAME, OU=$ORG_UNIT, O=$ORGANIZATION, L=$CITY, ST=$STATE, C=$COUNTRY"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Keystore generated successfully!"
    echo "📍 Location: $KEYSTORE_PATH"
    echo ""
    echo "⚠️  IMPORTANT:"
    echo "   1. Store the keystore file and passwords in a secure location"
    echo "   2. DO NOT commit the keystore file to version control"
    echo "   3. Add 'android/keystore/' to .gitignore"
    echo "   4. Update android/key.properties with your passwords"
    echo ""
    echo "Next steps:"
    echo "   1. Copy android/key.properties.template to android/key.properties"
    echo "   2. Update android/key.properties with your passwords"
    echo "   3. Build your release with: flutter build appbundle --release"
else
    echo ""
    echo "❌ Failed to generate keystore. Please check the errors above."
    exit 1
fi


