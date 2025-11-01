# Copy .env File to Root Directory

## Problem
The `.env` file is missing from `/home/thme/public_html/.env`.

## Solution: Copy .env File

### Step 1: Copy .env from Repository
```bash
# Copy .env file from repository to public_html
cp /home/thme/repos/hosoony/hosoony-backend/.env /home/thme/public_html/.env
```

### Step 2: Verify .env File
```bash
# Check if .env file exists
ls -la /home/thme/public_html/.env

# Check .env content
cat /home/thme/public_html/.env | head -10
```

### Step 3: Update APP_URL in .env
```bash
# Update APP_URL to match the domain
sed -i 's|APP_URL=.*|APP_URL=https://thakaa.me|' /home/thme/public_html/.env

# Verify the change
grep "APP_URL" /home/thme/public_html/.env
```

### Step 4: Set Correct Permissions
```bash
# Set permissions for .env file
chmod 644 /home/thme/public_html/.env

# Verify permissions
ls -la /home/thme/public_html/.env
```

### Step 5: Test Laravel Application
```bash
# Test main application
curl https://thakaa.me/

# Test admin panel
curl https://thakaa.me/admin

# Test API
curl https://thakaa.me/api/v1
```

### Step 6: Check Laravel Logs (if needed)
```bash
# Check Laravel logs for any errors
tail -f /home/thme/public_html/storage/logs/laravel.log
```

## Alternative: Create .env from Template
If the .env file doesn't exist in the repository:

```bash
# Copy from template
cp /home/thme/repos/hosoony/hosoony-backend/ENV_TEMPLATE.txt /home/thme/public_html/.env

# Update APP_URL
sed -i 's|APP_URL=.*|APP_URL=https://thakaa.me|' /home/thme/public_html/.env

# Set permissions
chmod 644 /home/thme/public_html/.env
```

## Expected Results
- .env file exists in `/home/thme/public_html/.env`
- APP_URL is set to `https://thakaa.me`
- Laravel application loads properly
- No more "No such file or directory" errors
