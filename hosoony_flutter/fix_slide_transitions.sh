#!/bin/bash

# Script to fix SlideTransition issues in all pages
# Changes Animation<double> _slideAnimation to Animation<Offset> _slideAnimation
# And fixes the Tween definitions

FILES=(
  "lib/features/payments/pages/subscriptions_page.dart"
  "lib/features/admin/pages/admin_system_page.dart"
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
  if [ -f "$file" ]; then
    echo "Processing: $file"
    # This is just a helper script - actual fixes will be done via search_replace
  fi
done

