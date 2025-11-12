#!/bin/bash

# Android 7-Inch Tablet Emulator Setup Script
# This script helps you create a 7-inch Android tablet emulator

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}Android 7-Inch Tablet Emulator Setup${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if ANDROID_HOME is set
if [ -z "$ANDROID_HOME" ]; then
    echo -e "${YELLOW}⚠️  ANDROID_HOME is not set. Trying to find Android SDK...${NC}"
    
    # Common locations for Android SDK
    if [ -d "$HOME/Library/Android/sdk" ]; then
        export ANDROID_HOME="$HOME/Library/Android/sdk"
        echo -e "${GREEN}✅ Found Android SDK at: $ANDROID_HOME${NC}"
    elif [ -d "$HOME/Android/Sdk" ]; then
        export ANDROID_HOME="$HOME/Android/Sdk"
        echo -e "${GREEN}✅ Found Android SDK at: $ANDROID_HOME${NC}"
    else
        echo -e "${RED}❌ Could not find Android SDK.${NC}"
        echo -e "${YELLOW}Please set ANDROID_HOME environment variable:${NC}"
        echo -e "${YELLOW}export ANDROID_HOME=\$HOME/Library/Android/sdk${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✅ ANDROID_HOME is set to: $ANDROID_HOME${NC}"
fi

# Check if avdmanager exists
AVDMANAGER=""
if [ -f "$ANDROID_HOME/cmdline-tools/latest/bin/avdmanager" ]; then
    AVDMANAGER="$ANDROID_HOME/cmdline-tools/latest/bin/avdmanager"
elif [ -f "$ANDROID_HOME/tools/bin/avdmanager" ]; then
    AVDMANAGER="$ANDROID_HOME/tools/bin/avdmanager"
else
    echo -e "${RED}❌ avdmanager not found. Please install Android SDK Command-line Tools.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Found avdmanager at: $AVDMANAGER${NC}"
echo ""

# Check if emulator exists
if [ ! -f "$ANDROID_HOME/emulator/emulator" ]; then
    echo -e "${RED}❌ Android Emulator not found.${NC}"
    echo -e "${YELLOW}Please install Android Emulator from Android Studio SDK Manager.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Android Emulator found${NC}"
echo ""

# AVD Configuration
AVD_NAME="7_inch_tablet_android_11"
SYSTEM_IMAGE="system-images;android-31;google_apis;x86_64"
DEVICE_PROFILE="7.0in WSVGA Tablet"

echo -e "${BLUE}Configuration:${NC}"
echo -e "  AVD Name: ${YELLOW}$AVD_NAME${NC}"
echo -e "  System Image: ${YELLOW}$SYSTEM_IMAGE${NC}"
echo -e "  Device Profile: ${YELLOW}$DEVICE_PROFILE${NC}"
echo ""

# Check if system image is installed
echo -e "${BLUE}Checking if system image is installed...${NC}"
if ! "$AVDMANAGER" list system-images | grep -q "$SYSTEM_IMAGE"; then
    echo -e "${YELLOW}⚠️  System image not found. Installing...${NC}"
    echo -e "${YELLOW}This may take a while...${NC}"
    
    SDKMANAGER=""
    if [ -f "$ANDROID_HOME/cmdline-tools/latest/bin/sdkmanager" ]; then
        SDKMANAGER="$ANDROID_HOME/cmdline-tools/latest/bin/sdkmanager"
    elif [ -f "$ANDROID_HOME/tools/bin/sdkmanager" ]; then
        SDKMANAGER="$ANDROID_HOME/tools/bin/sdkmanager"
    fi
    
    if [ -n "$SDKMANAGER" ]; then
        "$SDKMANAGER" "$SYSTEM_IMAGE" --channel=0
    else
        echo -e "${RED}❌ sdkmanager not found. Please install the system image manually from Android Studio.${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}✅ System image is already installed${NC}"
fi

echo ""

# Check if AVD already exists
if "$AVDMANAGER" list avd | grep -q "$AVD_NAME"; then
    echo -e "${YELLOW}⚠️  AVD '$AVD_NAME' already exists.${NC}"
    read -p "Do you want to delete and recreate it? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${BLUE}Deleting existing AVD...${NC}"
        "$AVDMANAGER" delete avd -n "$AVD_NAME"
        echo -e "${GREEN}✅ AVD deleted${NC}"
    else
        echo -e "${YELLOW}Skipping AVD creation. Using existing AVD.${NC}"
        echo ""
        echo -e "${GREEN}✅ Setup complete!${NC}"
        echo ""
        echo -e "${BLUE}To launch the emulator, run:${NC}"
        echo -e "${YELLOW}$ANDROID_HOME/emulator/emulator -avd $AVD_NAME${NC}"
        exit 0
    fi
fi

# Create AVD
echo -e "${BLUE}Creating AVD...${NC}"
echo -e "${YELLOW}Note: You may be prompted to accept licenses.${NC}"
echo ""

# Create AVD (non-interactive)
echo "no" | "$AVDMANAGER" create avd \
    -n "$AVD_NAME" \
    -k "$SYSTEM_IMAGE" \
    -d "$DEVICE_PROFILE" \
    -c 2048M || {
    echo -e "${RED}❌ Failed to create AVD.${NC}"
    echo -e "${YELLOW}You may need to create it manually using Android Studio.${NC}"
    exit 1
}

echo ""
echo -e "${GREEN}✅ AVD created successfully!${NC}"
echo ""

# Customize AVD config for 7-inch tablet
AVD_CONFIG_DIR="$HOME/.android/avd/${AVD_NAME}.avd"
if [ -d "$AVD_CONFIG_DIR" ]; then
    CONFIG_FILE="$AVD_CONFIG_DIR/config.ini"
    if [ -f "$CONFIG_FILE" ]; then
        echo -e "${BLUE}Customizing AVD configuration for 7-inch tablet...${NC}"
        
        # Backup original config
        cp "$CONFIG_FILE" "$CONFIG_FILE.backup"
        
        # Update or add screen configuration
        if grep -q "hw.lcd.width" "$CONFIG_FILE"; then
            sed -i '' 's/hw.lcd.width=.*/hw.lcd.width=1024/' "$CONFIG_FILE"
        else
            echo "hw.lcd.width=1024" >> "$CONFIG_FILE"
        fi
        
        if grep -q "hw.lcd.height" "$CONFIG_FILE"; then
            sed -i '' 's/hw.lcd.height=.*/hw.lcd.height=600/' "$CONFIG_FILE"
        else
            echo "hw.lcd.height=600" >> "$CONFIG_FILE"
        fi
        
        if grep -q "hw.lcd.density" "$CONFIG_FILE"; then
            sed -i '' 's/hw.lcd.density=.*/hw.lcd.density=160/' "$CONFIG_FILE"
        else
            echo "hw.lcd.density=160" >> "$CONFIG_FILE"
        fi
        
        echo -e "${GREEN}✅ AVD configuration updated${NC}"
    fi
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}✅ Setup Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}Your 7-inch tablet emulator is ready!${NC}"
echo ""
echo -e "${YELLOW}To launch the emulator:${NC}"
echo -e "  $ANDROID_HOME/emulator/emulator -avd $AVD_NAME"
echo ""
echo -e "${YELLOW}Or use Flutter:${NC}"
echo -e "  cd $(pwd)"
echo -e "  flutter devices"
echo -e "  flutter run"
echo ""
echo -e "${YELLOW}To list all AVDs:${NC}"
echo -e "  $AVDMANAGER list avd"
echo ""




