# Final Document Root Fix

## Current Issue
Getting directory listing instead of Laravel application, indicating document root is not pointing to the correct Laravel public directory.

## Root Cause Analysis
The document root is currently set to `/home/thme/public_html/api/public`, but Laravel is not being served properly.

## Solution: Move Laravel to Root Directory

### Step 1: Move Laravel Files to Root
```bash
# Move all Laravel files to public_html root
cd /home/thme/public_html
mv api/* .
mv api/.* . 2>/dev/null || true

# Remove the empty api directory
rmdir api
```

### Step 2: Update .env File
```bash
nano .env
```

Change the APP_URL line to:
```env
APP_URL=https://thakaa.me
```

Save and exit (Ctrl+X, then Y, then Enter)

### Step 3: Fix Storage Link
```bash
cd public
rm storage
ln -s ../storage/app/public storage
```

### Step 4: Set Permissions
```bash
chmod 644 .env
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Step 5: Update Document Root in cPanel
In cPanel:
1. Go to **Subdomains** or **Addon Domains**
2. Find `thakaa.me`
3. Change Document Root from `/public_html/api/public` to `/public_html`
4. Save changes

### Step 6: Test Laravel
```bash
curl https://thakaa.me/
curl https://thakaa.me/admin
curl https://thakaa.me/api/v1
```

## Expected Results
- Main page: Laravel welcome page or app
- Admin panel: Filament admin interface
- API: JSON responses

## Alternative: Keep Current Structure
If you prefer to keep the current structure, try this:

```bash
# Create .htaccess in public_html root
cat > /home/thme/public_html/.htaccess << 'EOF'
RewriteEngine On
RewriteRule ^(.*)$ api/public/$1 [L]
EOF

# Update document root to /public_html
```

## Flutter App Configuration
Update Flutter app to use:
- API URL: `https://thakaa.me/api/v1`
- Admin URL: `https://thakaa.me/admin`
