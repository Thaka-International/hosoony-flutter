#!/bin/bash

# Script to fix Android app icons according to Google Play requirements
# This script creates properly sized icons for each density

set -e

echo "🎨 إصلاح أيقونات Android"
echo "========================"
echo ""

cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter

# Check if source icon exists
SOURCE_ICON="assets/icons/android-chrome-512x512.png"
if [ ! -f "$SOURCE_ICON" ]; then
    echo "❌ الأيقونة الأصلية غير موجودة: $SOURCE_ICON"
    echo "يرجى التأكد من وجود أيقونة بحجم 512x512"
    exit 1
fi

echo "✅ الأيقونة الأصلية موجودة: $SOURCE_ICON"
echo ""

# Icon sizes for each density (in pixels)
# mdpi: 48x48
# hdpi: 72x72
# xhdpi: 96x96
# xxhdpi: 144x144
# xxxhdpi: 192x192

echo "📐 إنشاء أيقونات بأحجام صحيحة..."
echo ""

# Create icons for each density
for density in mdpi:48 hdpi:72 xhdpi:96 xxhdpi:144 xxxhdpi:192; do
    IFS=':' read -r density_name size <<< "$density"
    output_dir="android/app/src/main/res/mipmap-${density_name}"
    output_file="${output_dir}/ic_launcher.png"
    
    # Create directory if it doesn't exist
    mkdir -p "$output_dir"
    
    # Resize and convert icon (remove alpha channel to make it opaque)
    echo "  📱 إنشاء أيقونة ${density_name} (${size}x${size})..."
    
    # Use sips to resize and convert to RGB (remove transparency)
    sips -s format png \
         -z $size $size \
         "$SOURCE_ICON" \
         --out "$output_file.tmp" > /dev/null 2>&1
    
    # Convert to RGB (remove alpha) using sips
    sips -s format png \
         -s formatOptions low \
         "$output_file.tmp" \
         --out "$output_file" > /dev/null 2>&1
    
    # Remove temp file
    rm -f "$output_file.tmp"
    
    # Verify the icon was created
    if [ -f "$output_file" ]; then
        actual_size=$(sips -g pixelWidth -g pixelHeight "$output_file" 2>/dev/null | grep pixelWidth | awk '{print $2}')
        echo "    ✅ تم إنشاء: $output_file (${actual_size}x${actual_size})"
    else
        echo "    ❌ فشل إنشاء: $output_file"
    fi
done

echo ""
echo "✅ تم إصلاح جميع الأيقونات!"
echo ""
echo "📋 الأيقونات المنشأة:"
for density in mdpi:48 hdpi:72 xhdpi:96 xxhdpi:144 xxxhdpi:192; do
    IFS=':' read -r density_name size <<< "$density"
    icon_file="android/app/src/main/res/mipmap-${density_name}/ic_launcher.png"
    if [ -f "$icon_file" ]; then
        actual_size=$(sips -g pixelWidth "$icon_file" 2>/dev/null | grep pixelWidth | awk '{print $2}')
        echo "  ✅ mipmap-${density_name}/ic_launcher.png (${actual_size}x${actual_size})"
    fi
done

echo ""
echo "🚀 الخطوة التالية:"
echo "   flutter clean"
echo "   flutter build appbundle --release"

