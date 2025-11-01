# Flutter Task Submission Update

## Summary
Updated the Flutter app to properly submit completed tasks to the API using the newly updated `submitDailyLog` endpoint in `hosoony2-git`.

## Changes Made

### 1. **daily_tasks_page_new.dart**
- Added `_logDate` and `_classId` state variables to store API response data
- Updated `_loadDailyTasks()` to extract and store `log_date` and `class_id` from API response
- Created new `_submitTaskToAPI()` method to handle task submission
- Updated `_completeTask()` to call `_submitTaskToAPI()` with proper error handling

### 2. **api_service.dart**
- Updated `getDailyTasks()` to return `class_id` in the response
- Updated `submitDailyTasks()` to properly throw errors instead of catching them silently
- Removed the local fallback mechanism

## API Contract

### Request Format
```json
{
  "log_date": "2025-10-26",
  "class_id": 123,
  "tasks": [
    {
      "id": 456,
      "task_key": "hifz_surah_al_fatiha",
      "status": "completed",
      "completed": true,
      "notes": null,
      "quantity": 1,
      "duration_minutes": 15,
      "proof_type": "none"
    }
  ]
}
```

### Response Format
```json
{
  "success": true,
  "daily_log_id": 789,
  "finish_order": 5,
  "points_awarded": 100,
  "message": "Daily log submitted successfully"
}
```

## How It Works

1. **User completes a task**: User finishes a daily task with timer
2. **Local state update**: Task is marked as completed in local state immediately
3. **API submission**: Task data is sent to `/students/daily-tasks/submit` endpoint
4. **Backend processing**:
   - Controller validates the request (`id`, `status`, etc.)
   - Controller passes validated `tasks` as `items` to `DailyTasksService::submitWebDailyLog()`
   - Service uses `task_key` to lookup `DailyTaskDefinition`
   - Service creates/updates `DailyLogItem` with completion status, duration, etc.
   - Service calculates and awards points based on completion order
5. **Response handling**: App shows success/error message to user

## Key Points

- **No changes to git folder** (hosoony2-git) as per user requirements
- **Compatible with existing API** structure in hosoony2-git
- **Error handling**: If API fails, task is still completed locally with warning message
- **Timer integration**: Duration in minutes is sent to API based on elapsed time
- **Status tracking**: Task status is tracked through `_taskStatus` map

## Files Modified

1. `hosoony_flutter/lib/features/student/pages/daily_tasks_page_new.dart`
   - Lines 33-34: Added `_logDate` and `_classId` state variables
   - Lines 87-101: Updated `_loadDailyTasks()` to extract and store API metadata
   - Lines 387-421: Added new `_submitTaskToAPI()` method
   - Lines 256-271: Updated `_completeTask()` to call API submission

2. `hosoony_flutter/lib/services/api_service.dart`
   - Line 121: Added `class_id` to returned data from `getDailyTasks()`
   - Lines 137-140: Changed error handling to re-throw instead of catching

## Testing Checklist

- [ ] Complete a task and verify it submits to API
- [ ] Verify error handling when API is unavailable
- [ ] Check that timer duration is correctly sent
- [ ] Verify success message is shown to user
- [ ] Confirm task remains marked as completed even if API fails

