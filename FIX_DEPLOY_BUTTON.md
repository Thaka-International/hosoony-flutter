# 🔧 حل مشكلة زر "Deploy HEAD Commit" غير مفعل

## 🔍 الأسباب المحتملة:

### 1. مشكلة في ربط الفرع
- الفرع `production` لم يتم ربطه بشكل صحيح
- cPanel لا يرى الفرع الجديد

### 2. مشكلة في ملف .cpanel.yml
- الملف غير موجود أو به أخطاء
- مسار الملف غير صحيح

### 3. مشكلة في صلاحيات Git
- cPanel لا يستطيع الوصول للريبو
- مشكلة في SSH keys

## 🚀 الحلول خطوة بخطوة:

### الحل الأول: إعادة ربط الفرع

**في cPanel:**
1. اذهب إلى **Git™ Version Control**
2. افتح الريبو الموجود: `/home/thme/repos/hosoony`
3. في **Basic Information**:
   - تأكد من أن **Checked-Out Branch** هو `production`
   - إذا لم يكن موجود، اختر `master` أولاً
4. اضغط **Update**
5. اذهب إلى **Pull or Deploy**
6. اضغط **Pull** أولاً
7. بعد نجاح Pull، جرب **Deploy HEAD Commit**

### الحل الثاني: التحقق من ملف .cpanel.yml

**في Terminal:**
```bash
# انتقل إلى مجلد العمل
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony-git

# تحقق من وجود الملف
ls -la .cpanel.yml

# إذا لم يكن موجود، أنشئه
cat > .cpanel.yml << 'EOF'
---
deployment:
  tasks:
    - export DEPLOYPATH=/home/thme/public_html
    - /bin/git --work-tree=$PWD --git-dir=.git pull --ff-only origin production
    - /bin/rsync -a --delete --exclude=".git" --exclude=".cpanel" --exclude="node_modules" --exclude="storage/logs" --exclude="vendor" ./ $DEPLOYPATH/
    - cd $DEPLOYPATH && /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction
    - /usr/local/bin/php -d detect_unicode=0 $DEPLOYPATH/artisan optimize:clear
    - /usr/local/bin/php $DEPLOYPATH/artisan config:cache
    - /usr/local/bin/php $DEPLOYPATH/artisan route:cache
    - /usr/local/bin/php $DEPLOYPATH/artisan view:cache
    - chmod -R 755 $DEPLOYPATH/storage
    - chmod -R 755 $DEPLOYPATH/bootstrap/cache
    - echo "✅ تم النشر بنجاح في $(date)" >> $DEPLOYPATH/deployment.log
EOF

# أضف الملف إلى Git
git add .cpanel.yml
git commit -m "chore: add cPanel deployment configuration"
git push origin production
```

### الحل الثالث: إعادة إنشاء الريبو في cPanel

**في cPanel:**
1. اذهب إلى **Git™ Version Control**
2. **احذف الريبو الموجود** (إذا لزم)
3. **أنشئ ريبو جديد**:
   - **Repository URL**: `git@github.com:Thaka-International/hosoony.git`
   - **Repository Path**: `/home/thme/repos/hosoony`
   - **Branch**: `production`
4. اضغط **Create**
5. انتظر حتى يكتمل الإنشاء
6. اذهب إلى **Pull or Deploy**
7. اضغط **Pull** ثم **Deploy HEAD Commit**

### الحل الرابع: التحقق من صلاحيات Git

**في Terminal:**
```bash
# تحقق من SSH keys
ssh -T git@github.com

# إذا فشل، أعد إعداد SSH keys
ssh-keygen -t ed25519 -C "your_email@example.com"
cat ~/.ssh/id_ed25519.pub
# أضف المفتاح إلى GitHub
```

## 🔍 تشخيص المشكلة:

### تحقق من سجلات cPanel:
1. اذهب إلى **Git™ Version Control**
2. افتح الريبو
3. اذهب إلى **Logs**
4. ابحث عن أخطاء في آخر عملية Pull/Deploy

### تحقق من حالة الفرع:
```bash
# في Terminal
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony-git
git branch -a
git status
git log --oneline -5
```

## 🎯 الحل السريع:

### جرب هذا التسلسل:
1. **في cPanel**: اذهب إلى الريبو → **Pull or Deploy** → اضغط **Pull**
2. **انتظر** حتى يكتمل Pull
3. **اضغط Deploy HEAD Commit**
4. **إذا لم يعمل**: جرب **Deploy Specific Commit** واختر آخر commit

### إذا استمرت المشكلة:
1. **احذف الريبو** من cPanel
2. **أنشئ ريبو جديد** مع الفرع `production`
3. **اختبر النشر** مرة أخرى

## ⚠️ تحذيرات:

- **لا تحذف public_html** أثناء التجربة
- **احتفظ بنسخة احتياطية** من الملفات المهمة
- **راقب سجلات الأخطاء** في cPanel

## 📞 إذا استمرت المشكلة:

أرسل لي:
1. **لقطة شاشة** من صفحة Git™ Version Control
2. **سجلات الأخطاء** من تبويب Logs
3. **حالة الفرع** من Terminal

---
**🔧 جرب الحلول بالترتيب المذكور أعلاه!**















