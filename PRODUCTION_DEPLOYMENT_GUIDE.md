# 🚀 دليل تطبيق اقتراح النشر التلقائي

## 📋 تحليل الاقتراح

**✅ ممتاز جداً!** هذا الاقتراح يحل المشاكل التالية:
- **مصدر حقيقة واحد**: GitHub repo بدلاً من ملفات متفرقة
- **نشر تلقائي**: عبر webhook من GitHub إلى cPanel
- **إدارة نسخ**: فرع production ثابت مع tags
- **أمان**: لا تعديل يدوي في public_html
- **سهولة الرجوع**: rollback سهل عبر Git

## 🔧 الخطوات المطلوبة منك

### الخطوة 1: إعداد فرع الإنتاج على GitHub

**المكان**: على جهازك (Terminal داخل Cursor)

```bash
# تشغيل السكريبت التلقائي
cd /Users/ibraheem.designer/Documents/hosoony2
chmod +x setup_production_branch.sh
./setup_production_branch.sh
```

**أو يدوياً:**
```bash
# إنشاء مجلد جديد للعمل مع Git
mkdir -p hosoony-git
cd hosoony-git

# Clone الريبو من GitHub
git clone git@github.com:Thaka-International/hosoony.git .

# جلب كل الفروع
git fetch --all --prune

# إنشاء فرع production من prod-snapshot-20251015
git checkout -B production origin/prod-snapshot-20251015

# دفع الفرع إلى GitHub
git push -u origin production

# إنشاء Tag ثابت
git tag -a prod-20251015 -m "Production snapshot 2025-10-15"
git push origin prod-20251015
```

### الخطوة 2: ربط cPanel بالفرع الجديد

**المكان**: لوحة cPanel » Git™ Version Control

1. **افتح الريبو الموجود**:
   - Repository Path: `/home/thme/repos/hosoony`

2. **من تبويب Basic Information**:
   - في Checked-Out Branch اختر `production` بدلاً من `master`
   - اضغط Update

3. **من تبويب Pull or Deploy**:
   - جرّب Pull ثم Deploy للتأكد أن العملية تعمل بدون أخطاء

### الخطوة 3: إعداد النشر التلقائي

**المكان**: على جهازك (ثم Push إلى GitHub)

```bash
# انتقل إلى مجلد العمل الجديد
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony-git

# أضف ملف .cpanel.yml
cp /Users/ibraheem.designer/Documents/hosoony2/.cpanel.yml .

# أضف التغييرات إلى Git
git add .cpanel.yml
git commit -m "chore: add cPanel deployment to public_html from production"
git push origin production
```

### الخطوة 4: تشغيل نشر يدوي أول مرة

**المكان**: لوحة cPanel » Git™ Version Control » Pull or Deploy

1. اضغط **Pull** (إن لزم)
2. اضغط **Deploy HEAD Commit**
3. راقب سجل **Logs** للتأكد من:
   - نجاح rsync إلى `/home/thme/public_html`
   - نجاح أوامر artisan
   - نجاح composer install

### الخطوة 5: تفعيل Webhook للنشر التلقائي

**المكان**: cPanel ثم GitHub

#### في cPanel:
1. اذهب إلى **Git™ Version Control** » **Manage Repository** » **Pull or Deploy**
2. ستجد رابط **Deployment via Webhook**
3. انسخ **Webhook URL**

#### في GitHub:
1. اذهب إلى **repo** » **Settings** » **Webhooks** » **Add webhook**
2. **Payload URL**: ضع رابط الـ Webhook المنسوخ من cPanel
3. **Content type**: `application/json`
4. **Which events?**: اختر "Just the push event"
5. اضغط **Add webhook**

## 🎯 سير العمل اليومي بعد الإعداد

### على جهازك:
```bash
# انتقل إلى مجلد العمل
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony-git

# عدّل على فرع تطويرك
git checkout -b feature/fix-x
# ... تعديلاتك
git commit -am "fix: x"
git push origin feature/fix-x

# اعمل Pull Request إلى production وادمجه
# ثم:
git checkout production
git pull --ff-only
```

### النتيجة:
**عند الدمج إلى production** → **GitHub يرسل Webhook** → **cPanel ينشر إلى public_html**

## 🔒 ملاحظات أمان/تشغيل

### ✅ المهم:
- **مصدر الحقيقة واحد**: الريبو على GitHub
- **لا تعدّل يدوياً** في public_html (لكي لا تضيع التغييرات)
- **احتفظ بفرع prod-snapshot-20251015** أو حوّله إلى Tag—لا تحذفه الآن

### 🔄 Rollback:
```bash
# في جهازك
git revert <bad-commit-sha>
git push origin production
```
**وسيعمل نشر تلقائي**

## 📁 الملفات المنشأة

- `setup_production_branch.sh` - سكريبت إعداد فرع الإنتاج
- `.cpanel.yml` - ملف النشر التلقائي
- `PRODUCTION_DEPLOYMENT_GUIDE.md` - هذا الدليل

## ⚠️ تحذيرات مهمة

1. **تأكد من إعداد SSH keys** للوصول إلى GitHub
2. **اختبر النشر اليدوي أولاً** قبل تفعيل Webhook
3. **راقب سجلات النشر** في cPanel
4. **احتفظ بنسخة احتياطية** من public_html قبل البدء

## 🎉 النتيجة المتوقعة

بعد تطبيق هذا الاقتراح:
- ✅ **نشر تلقائي** عند كل push إلى production
- ✅ **إدارة نسخ محترفة** عبر Git
- ✅ **سهولة الرجوع** عند الحاجة
- ✅ **مصدر حقيقة واحد** للكود
- ✅ **لا تعديل يدوي** في الإنتاج

---
**🚀 هذا الاقتراح ممتاز ويجب تطبيقه فوراً!**















