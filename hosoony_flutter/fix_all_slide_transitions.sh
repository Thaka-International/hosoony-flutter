#!/bin/bash

# Helper script to fix remaining SlideTransition files
# Pattern: Change Animation<double> to Animation<Offset> and fix Tween definitions

fix_file() {
  local file="$1"
  if [ ! -f "$file" ]; then
    echo "File not found: $file"
    return 1
  fi
  
  # Change Animation<double> _slideAnimation to Animation<Offset> _slideAnimation
  sed -i '' 's/late Animation<double> _slideAnimation;/late Animation<Offset> _slideAnimation;/g' "$file"
  
  # Change Tween<double> to Tween<Offset> with correct values
  sed -i '' 's/_slideAnimation = Tween<double>(/_slideAnimation = Tween<Offset>(/g' "$file"
  sed -i '' '/_slideAnimation = Tween<Offset>/,/)$/{
    s/begin: 30\.0,/begin: const Offset(0, -0.3),/g
    s/end: 0\.0,/end: Offset.zero,/g
  }' "$file"
  
  # Remove nested Tween<Offset> in SlideTransition position
  sed -i '' '/position: Tween<Offset>/,/\)\.animate(_slideAnimation)/{
    /position: Tween<Offset>/d
    /begin: const Offset(0, 0\.3),/d
    /end: Offset\.zero,/d
    /\)\.animate(_slideAnimation)/s/.*/position: _slideAnimation,/
  }' "$file"
  
  echo "Fixed: $file"
}

# List of files to fix
FILES=(
  "lib/features/admin/pages/admin_statistics_page.dart"
  "lib/features/admin/pages/admin_users_page.dart"
  "lib/features/support/pages/support_reports_page.dart"
  "lib/features/support/pages/support_inquiries_page.dart"
  "lib/features/support/pages/support_students_page.dart"
  "lib/features/support/pages/support_home_page.dart"
  "lib/features/teacher/pages/teacher_evaluation_page.dart"
  "lib/features/teacher/pages/teacher_add_task_page.dart"
  "lib/features/teacher/pages/teacher_students_page.dart"
  "lib/features/teacher/pages/teacher_schedule_page.dart"
  "lib/features/teacher/pages/teacher_home_page.dart"
  "lib/features/notifications/pages/notifications_page.dart"
  "lib/features/reports/pages/advanced_reports_page.dart"
)

for file in "${FILES[@]}"; do
  fix_file "$file"
done

echo "Done!"
