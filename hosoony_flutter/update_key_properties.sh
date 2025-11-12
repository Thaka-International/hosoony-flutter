#!/bin/bash

# Script to update key.properties with actual passwords

echo "🔐 تحديث ملف key.properties"
echo "=============================="
echo ""

KEY_PROPERTIES_FILE="android/key.properties"

# Check if file exists
if [ ! -f "$KEY_PROPERTIES_FILE" ]; then
    echo "❌ ملف key.properties غير موجود!"
    echo "إنشاء ملف جديد..."
    cp android/key.properties.template "$KEY_PROPERTIES_FILE"
fi

echo "الرجاء إدخال كلمات المرور التي استخدمتها عند إنشاء keystore:"
echo ""

read -sp "كلمة مرور Keystore: " STORE_PASSWORD
echo ""
read -sp "كلمة مرور المفتاح (Key password): " KEY_PASSWORD
echo ""

# Update the file
cat > "$KEY_PROPERTIES_FILE" << EOF
storePassword=$STORE_PASSWORD
keyPassword=$KEY_PASSWORD
keyAlias=hosoony-release-key
storeFile=../keystore/hosoony-release-key.jks
EOF

echo ""
echo "✅ تم تحديث ملف key.properties بنجاح!"
echo ""




