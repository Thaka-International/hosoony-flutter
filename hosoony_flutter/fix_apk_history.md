# 🔧 حل مشكلة ملفات APK الكبيرة في Git History

## المشكلة
ملفات APK موجودة في git history وتسبب فشل push لأنها كبيرة جداً (>100MB).

## الحل: استخدام git filter-repo (الأفضل)

### تثبيت git-filter-repo:
```bash
# باستخدام pip
pip install git-filter-repo

# أو باستخدام Homebrew
brew install git-filter-repo
```

### إزالة ملفات APK من التاريخ:
```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter

# إزالة جميع ملفات .apk من التاريخ
git filter-repo --path-glob '*.apk' --invert-paths --force

# أو إزالة ملفات محددة
git filter-repo --path hosoony_app.apk --path hosoony_fixed_login.apk --invert-paths --force
```

## الحل البديل: استخدام BFG Repo-Cleaner

### تثبيت BFG:
```bash
brew install bfg
```

### إزالة ملفات APK:
```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter

# إنشاء backup أولاً
git clone --mirror . ../hosoony_flutter_backup.git

# إزالة ملفات APK
bfg --delete-files "*.apk" .

# تنظيف
git reflog expire --expire=now --all
git gc --prune=now --aggressive
```

## الحل السريع: Force Push بعد إزالة من آخر commit

إذا كانت الملفات في آخر commit فقط:

```bash
cd /Users/ibraheem.designer/Documents/hosoony2/hosoony_flutter

# إزالة من آخر commit
git reset --soft HEAD~1
git reset HEAD *.apk ../*.apk
git commit -m "إزالة ملفات APK"

# Force push
git push flutter-repo master --force
```

## ⚠️ تحذيرات

1. **Force push يعيد كتابة التاريخ** - تأكد من أن لا أحد آخر يعمل على نفس branch
2. **احفظ backup** قبل إعادة كتابة التاريخ
3. **أخبر الفريق** قبل force push على shared branches

## ✅ بعد الإصلاح

تأكد من أن `.gitignore` يحتوي على:
```
*.apk
*.aab
*.ipa
**/*.apk
```

وأن الملفات الجديدة لن تُضاف لـ git.

