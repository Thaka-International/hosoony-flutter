# 🚀 الأوامر المطلوبة لإكمال خطة النشر التلقائي

## ✅ تم إنجازه:

1. **تم إنشاء ملف `.cpanel.yml`** في مجلد `hosoony-git` ✅
2. **تم إعداد الريبو** مع فرع `production` ✅
3. **تم رفع التعديلات** إلى GitHub بنجاح ✅

## 🔧 الأوامر المطلوبة الآن:

### في Terminal (على جهازك):

```bash
# الانتقال إلى مجلد الريبو
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony-git

# إضافة ملف .cpanel.yml إلى Git
git add .cpanel.yml

# إنشاء commit
git commit -m "chore: add cPanel deployment to public_html from production

- إضافة ملف .cpanel.yml للنشر التلقائي
- تكوين rsync لنسخ الملفات إلى public_html
- إضافة أوامر Laravel بعد النشر
- إصلاح صلاحيات المجلدات
- تسجيل نجاح النشر

التاريخ: $(date)"

# رفع التعديلات إلى GitHub
git push origin production
```

## 📋 الخطوات التالية في cPanel:

### الخطوة 3: ربط cPanel بالفرع الجديد
1. اذهب إلى **Git™ Version Control** في cPanel
2. افتح الريبو الموجود: `/home/thme/repos/hosoony`
3. في **Basic Information**:
   - **Checked-Out Branch**: اختر `production`
4. اضغط **Update**

### الخطوة 4: تشغيل نشر يدوي أول مرة
1. اذهب إلى **Pull or Deploy**
2. اضغط **Pull** أولاً
3. ثم اضغط **Deploy HEAD Commit**
4. راقب سجل **Logs** للتأكد من:
   - نجاح rsync إلى `/home/thme/public_html`
   - نجاح أوامر artisan

### الخطوة 5: تفعيل Webhook للنشر التلقائي
1. في cPanel: **Git™ Version Control** » **Manage Repository** » **Pull or Deploy**
2. انسخ رابط **Deployment via Webhook**
3. في GitHub: **Settings** » **Webhooks** » **Add webhook**
4. **Payload URL**: ضع رابط الـ Webhook
5. **Content type**: `application/json`
6. **Which events?**: "Just the push event"
7. اضغط **Add webhook**

## 🎯 النتيجة المتوقعة:

- ✅ **نشر تلقائي** عند كل push إلى production
- ✅ **إدارة نسخ محترفة** عبر Git
- ✅ **سهولة الرجوع** عند الحاجة
- ✅ **مصدر حقيقة واحد** للكود

---
**🚀 نفذ الأوامر أعلاه في Terminal ثم اتبع الخطوات في cPanel!**















