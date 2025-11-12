#!/bin/bash

# سكريبت لإنشاء أيقونات iOS من أيقونة واحدة
# الاستخدام: ./generate_app_icons.sh path/to/icon.png

SOURCE_ICON="$1"
OUTPUT_DIR="Runner/Assets.xcassets/AppIcon.appiconset"

if [ -z "$SOURCE_ICON" ]; then
    echo "❌ يرجى تحديد مسار الأيقونة:"
    echo "   ./generate_app_icons.sh path/to/icon.png"
    exit 1
fi

if [ ! -f "$SOURCE_ICON" ]; then
    echo "❌ الملف غير موجود: $SOURCE_ICON"
    exit 1
fi

echo "📱 إنشاء أيقونات iOS..."

# إنشاء مجلد الإخراج إذا لم يكن موجوداً
mkdir -p "$OUTPUT_DIR"

# استخدام sips (مدمج في macOS)
if command -v sips &> /dev/null; then
    echo "✅ استخدام sips..."
    
    # iPhone
    sips -z 40 40 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-20x20@2x.png"
    sips -z 60 60 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-20x20@3x.png"
    sips -z 29 29 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-29x29@1x.png"
    sips -z 58 58 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-29x29@2x.png"
    sips -z 87 87 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-29x29@3x.png"
    sips -z 80 80 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-40x40@2x.png"
    sips -z 120 120 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-40x40@3x.png"
    sips -z 120 120 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-60x60@2x.png"
    sips -z 180 180 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-60x60@3x.png"
    
    # iPad
    sips -z 20 20 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-20x20@1x.png"
    sips -z 40 40 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-20x20@2x.png"
    sips -z 29 29 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-29x29@1x.png"
    sips -z 58 58 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-29x29@2x.png"
    sips -z 40 40 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-40x40@1x.png"
    sips -z 80 80 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-40x40@2x.png"
    sips -z 76 76 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-76x76@1x.png"
    sips -z 152 152 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-76x76@2x.png"
    sips -z 167 167 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-83.5x83.5@2x.png"
    
    # App Store
    sips -z 1024 1024 "$SOURCE_ICON" --out "$OUTPUT_DIR/Icon-App-1024x1024@1x.png"
    
    echo "✅ تم إنشاء جميع الأيقونات!"
    echo ""
    echo "📝 الآن في Xcode:"
    echo "1. افتح Runner.xcworkspace"
    echo "2. اذهب إلى Runner → Assets.xcassets → AppIcon"
    echo "3. Xcode قد يكتشف الأيقونات تلقائياً"
    echo "4. أو اسحب كل أيقونة إلى مكانها المناسب"
    
elif command -v convert &> /dev/null; then
    echo "✅ استخدام ImageMagick..."
    # نفس الأوامر ولكن بـ convert
    convert "$SOURCE_ICON" -resize 40x40 "$OUTPUT_DIR/Icon-App-20x20@2x.png"
    # ... إلخ
else
    echo "❌ لم يتم العثور على sips أو ImageMagick"
    echo "📝 استخدم أداة online: https://www.appicon.co/"
    exit 1
fi









