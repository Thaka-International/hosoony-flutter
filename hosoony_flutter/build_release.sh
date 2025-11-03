#!/bin/bash

# Hosoony Flutter - Release Build Script
# Builds release versions for both Android and iOS

set -e  # Exit on error

echo "🚀 Hosoony Flutter - Release Build"
echo "=================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if Flutter is installed
if ! command -v flutter &> /dev/null; then
    echo -e "${RED}❌ Flutter is not installed or not in PATH${NC}"
    exit 1
fi

# Clean previous builds
echo "🧹 Cleaning previous builds..."
flutter clean

# Get dependencies
echo "📦 Getting dependencies..."
flutter pub get

# Build selection
echo ""
echo "Select build type:"
echo "1) Android App Bundle (AAB) - For Google Play Store"
echo "2) Android APK - For testing"
echo "3) iOS IPA - For App Store"
echo "4) Both Android AAB and iOS IPA"
read -p "Enter choice (1-4): " choice

case $choice in
    1)
        echo ""
        echo -e "${GREEN}📱 Building Android App Bundle...${NC}"
        
        # Check if keystore exists
        if [ ! -f "android/key.properties" ]; then
            echo -e "${YELLOW}⚠️  Warning: key.properties not found${NC}"
            echo "Creating from template..."
            cp android/key.properties.template android/key.properties
            echo -e "${YELLOW}Please edit android/key.properties with your keystore details${NC}"
            exit 1
        fi
        
        flutter build appbundle --release
        echo ""
        echo -e "${GREEN}✅ Android App Bundle built successfully!${NC}"
        echo "📍 Location: build/app/outputs/bundle/release/app-release.aab"
        ;;
    
    2)
        echo ""
        echo -e "${GREEN}📱 Building Android APK...${NC}"
        flutter build apk --release
        echo ""
        echo -e "${GREEN}✅ Android APK built successfully!${NC}"
        echo "📍 Location: build/app/outputs/flutter-apk/app-release.apk"
        ;;
    
    3)
        echo ""
        echo -e "${GREEN}🍎 Building iOS IPA...${NC}"
        
        # Check if on macOS
        if [[ "$OSTYPE" != "darwin"* ]]; then
            echo -e "${RED}❌ iOS builds require macOS${NC}"
            exit 1
        fi
        
        flutter build ipa --release
        echo ""
        echo -e "${GREEN}✅ iOS IPA built successfully!${NC}"
        echo "📍 Location: build/ios/ipa/hosoony_flutter.ipa"
        ;;
    
    4)
        echo ""
        echo -e "${GREEN}📱 Building Android App Bundle...${NC}"
        
        if [ ! -f "android/key.properties" ]; then
            echo -e "${YELLOW}⚠️  Warning: key.properties not found${NC}"
            echo "Creating from template..."
            cp android/key.properties.template android/key.properties
            echo -e "${YELLOW}Please edit android/key.properties with your keystore details${NC}"
            exit 1
        fi
        
        flutter build appbundle --release
        echo ""
        
        if [[ "$OSTYPE" == "darwin"* ]]; then
            echo -e "${GREEN}🍎 Building iOS IPA...${NC}"
            flutter build ipa --release
            echo ""
            echo -e "${GREEN}✅ Both builds completed!${NC}"
            echo "📍 Android AAB: build/app/outputs/bundle/release/app-release.aab"
            echo "📍 iOS IPA: build/ios/ipa/hosoony_flutter.ipa"
        else
            echo -e "${YELLOW}⚠️  Skipping iOS build (requires macOS)${NC}"
            echo -e "${GREEN}✅ Android build completed!${NC}"
            echo "📍 Location: build/app/outputs/bundle/release/app-release.aab"
        fi
        ;;
    
    *)
        echo -e "${RED}❌ Invalid choice${NC}"
        exit 1
        ;;
esac

echo ""
echo "📋 Next Steps:"
echo "1. Review the build output above"
echo "2. Test the build on a device"
echo "3. Upload to Google Play Console or App Store Connect"
echo "4. See STORE_PUBLISHING_GUIDE.md for detailed instructions"
echo ""


