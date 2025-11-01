# Fix Filament Admin Panel 403 Errors

## Problem
The Filament admin panel is showing 403 Forbidden errors in production because the User model doesn't implement the `FilamentUser` contract.

## Solution
Update the User model to implement `FilamentUser` and add the `canAccessPanel` method.

## Steps

### 1. Update User Model on Server
```bash
cd /home/thme/public_html

# Backup current User model
cp app/Models/User.php app/Models/User.php.backup

# Update the User model with FilamentUser contract
cat > app/Models/User.php << 'EOF'
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Scopes\GenderScope;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasApiTokens;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'gender',
        'role',
        'class_id',
        'phone',
        'guardian_name',
        'guardian_phone',
        'locale',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Temporarily disabled to fix memory issues
        // static::addGlobalScope(new GenderScope);
    }

    /**
     * Get the devices for the user.
     */
    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Get the class for the user.
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Alias for class() method for Filament compatibility.
     */
    public function classModel()
    {
        return $this->class();
    }

    /**
     * Get the gamification points for the user.
     */
    public function gamificationPoints()
    {
        return $this->hasMany(GamificationPoint::class, 'student_id');
    }

    /**
     * Get the subscriptions for the user.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'student_id');
    }

    /**
     * Get the payments for the user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the daily logs for the user.
     */
    public function dailyLogs()
    {
        return $this->hasMany(DailyLog::class, 'student_id');
    }

    /**
     * Get the sessions taught by the user.
     */
    public function taughtSessions()
    {
        return $this->hasMany(Session::class, 'teacher_id');
    }

    /**
     * Get the students in the user's class (for teachers).
     */
    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is teacher.
     */
    public function isTeacher(): bool
    {
        return in_array($this->role, ['teacher', 'teacher_support']);
    }

    /**
     * Check if user is student.
     */
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user can access Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Only allow admin users to access the admin panel
        return $this->hasRole('admin') && $this->isActive();
    }

    /**
     * Get user's full name with gender prefix.
     */
    public function getFullNameAttribute(): string
    {
        $prefix = $this->gender === 'male' ? 'الأستاذ' : 'الأستاذة';
        return $prefix . ' ' . $this->name;
    }

    /**
     * Boot method to sync role with spatie/permission
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            try {
                $user->assignRole($user->role);
            } catch (\Exception $e) {
                // Role might not exist yet, ignore error
            }
        });

        static::updated(function ($user) {
            if ($user->isDirty('role')) {
                try {
                    $user->syncRoles([$user->role]);
                } catch (\Exception $e) {
                    // Role might not exist yet, ignore error
                }
            }
        });
    }
}
EOF
```

### 2. Clear Laravel Caches
```bash
# Clear all caches to ensure changes take effect
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Test Admin Panel Access
```bash
# Test if admin panel is accessible
curl -I https://thakaa.me/admin/login
# Should return: HTTP/1.1 200 OK
```

### 4. Verify Admin User Access
```bash
# Check admin user in Tinker
php artisan tinker
```

In Tinker:
```php
// Check admin user
$admin = \App\Models\User::where('email', 'admin@hosoony.com')->first();

// Check if they can access admin panel
echo "Has admin role: " . ($admin->hasRole('admin') ? 'Yes' : 'No') . "\n";
echo "Is active: " . ($admin->isActive() ? 'Yes' : 'No') . "\n";

// Test canAccessPanel method
use Filament\Facades\Filament;
$panel = Filament::getPanel('admin');
echo "Can access panel: " . ($admin->canAccessPanel($panel) ? 'Yes' : 'No') . "\n";

exit
```

## Expected Result

After implementing the `FilamentUser` contract:

1. **Admin Panel Access**: `https://thakaa.me/admin/login` should work without 403 errors
2. **Proper Authentication**: Only users with `admin` role and `active` status can access
3. **Security**: Non-admin users will be properly blocked from admin panel

## Test Login

Try logging into the admin panel:
- **URL**: `https://thakaa.me/admin/login`
- **Email**: `admin@hosoony.com`
- **Password**: `password`

## Alternative: Pull Latest Changes

If you prefer to pull the latest changes from Git:
```bash
cd /home/thme/public_html
git pull origin master
php artisan optimize:clear
```

**This fix implements the proper FilamentUser contract to resolve the 403 Forbidden errors in the admin panel!**


















