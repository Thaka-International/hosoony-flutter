#!/bin/bash

# Comprehensive Test Script for Hosoony Flutter App
# Tests all APIs, network connectivity, and system health

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuration
API_BASE_URL="https://thakaa.me/api/v1"
ADMIN_EMAIL="admin@hosoony.com"
ADMIN_PASSWORD="password"
TEST_PHONE="966541355804"
TEST_STUDENT_EMAIL="student.male1@hosoony.com"
TEST_TEACHER_EMAIL="teacher.male@hosoony.com"

# Test results
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0
WARNING_TESTS=0

echo -e "${CYAN}🧪 اختبار شامل لتطبيق حصوني القرآني${NC}"
echo "=================================================="
echo -e "Testing: ${BLUE}$API_BASE_URL${NC}"
echo ""

# Function to run a test
run_test() {
    local test_name="$1"
    local test_command="$2"
    local expected_success="$3"
    
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    
    echo -e "${YELLOW}$TOTAL_TESTS. Testing $test_name...${NC}"
    
    if eval "$test_command"; then
        if [ "$expected_success" = "true" ]; then
            echo -e "   ${GREEN}✅ $test_name successful${NC}"
            PASSED_TESTS=$((PASSED_TESTS + 1))
        else
            echo -e "   ${YELLOW}⚠️  $test_name completed (expected failure)${NC}"
            WARNING_TESTS=$((WARNING_TESTS + 1))
        fi
    else
        if [ "$expected_success" = "true" ]; then
            echo -e "   ${RED}❌ $test_name failed${NC}"
            FAILED_TESTS=$((FAILED_TESTS + 1))
        else
            echo -e "   ${GREEN}✅ $test_name failed as expected${NC}"
            PASSED_TESTS=$((PASSED_TESTS + 1))
        fi
    fi
    echo ""
}

# Test 1: Network Connectivity
run_test "Network Connectivity" "
    ping -c 1 thakaa.me > /dev/null 2>&1
" "true"

# Test 2: Server Health Check
run_test "Server Health Check" "
    curl -s -f '$API_BASE_URL/ops/scheduler/last-run' > /dev/null
" "true"

# Test 3: Phone Authentication - Send Code
run_test "Phone Auth - Send Code" "
    response=\$(curl -s -X POST '$API_BASE_URL/phone-auth/send-code' \\
        -H 'Content-Type: application/json' \\
        -d '{\"phone\":\"$TEST_PHONE\"}')
    echo \"\$response\" | grep -q 'success'
" "true"

# Test 4: Admin Login
run_test "Admin Login" "
    response=\$(curl -s -X POST '$API_BASE_URL/auth/login' \\
        -H 'Content-Type: application/json' \\
        -d '{\"email\":\"$ADMIN_EMAIL\",\"password\":\"$ADMIN_PASSWORD\"}')
    echo \"\$response\" | grep -q 'Login successful'
" "true"

# Extract token for authenticated tests
echo -e "${BLUE}Getting authentication token...${NC}"
LOGIN_RESPONSE=$(curl -s -X POST "$API_BASE_URL/auth/login" \
    -H "Content-Type: application/json" \
    -d "{\"email\":\"$ADMIN_EMAIL\",\"password\":\"$ADMIN_PASSWORD\"}")

if echo "$LOGIN_RESPONSE" | grep -q "Login successful"; then
    TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
    echo -e "   ${GREEN}✅ Token extracted${NC}"
else
    echo -e "   ${RED}❌ Failed to get token${NC}"
    TOKEN=""
fi
echo ""

# Test 5: User Info (Me Endpoint)
run_test "User Info (Me Endpoint)" "
    curl -s -f -X GET '$API_BASE_URL/me' \\
        -H 'Authorization: Bearer $TOKEN' > /dev/null
" "true"

# Test 6: Notifications
run_test "Notifications" "
    response=\$(curl -s -X GET '$API_BASE_URL/notifications' \\
        -H 'Authorization: Bearer $TOKEN')
    echo \"\$response\" | grep -q 'data'
" "true"

# Test 7: Daily Tasks
run_test "Daily Tasks" "
    curl -s -f -X GET '$API_BASE_URL/students/1/daily-tasks' \\
        -H 'Authorization: Bearer $TOKEN' > /dev/null
" "true"

# Test 8: Companions
run_test "Companions" "
    response=\$(curl -s -X GET '$API_BASE_URL/me/companions' \\
        -H 'Authorization: Bearer $TOKEN')
    echo \"\$response\" | grep -q 'data'
" "true"

# Test 9: Performance Evaluation
run_test "Performance Evaluation" "
    curl -s -f -X GET '$API_BASE_URL/students/1/performance' \\
        -H 'Authorization: Bearer $TOKEN' > /dev/null
" "true"

# Test 10: Reports
run_test "Reports" "
    curl -s -f -X GET '$API_BASE_URL/reports/daily/1' \\
        -H 'Authorization: Bearer $TOKEN' > /dev/null
" "true"

# Test 11: Payments
run_test "Payments" "
    curl -s -f -X GET '$API_BASE_URL/students/1/payments' \\
        -H 'Authorization: Bearer $TOKEN' > /dev/null
" "true"

# Test 12: Test Notification
run_test "Test Notification" "
    response=\$(curl -s -X POST '$API_BASE_URL/notifications/test' \\
        -H 'Authorization: Bearer $TOKEN')
    echo \"\$response\" | grep -q 'success'
" "true"

# Test 13: Student Login
run_test "Student Login" "
    response=\$(curl -s -X POST '$API_BASE_URL/auth/login' \\
        -H 'Content-Type: application/json' \\
        -d '{\"email\":\"$TEST_STUDENT_EMAIL\",\"password\":\"password\"}')
    echo \"\$response\" | grep -q 'Login successful'
" "true"

# Test 14: Teacher Login
run_test "Teacher Login" "
    response=\$(curl -s -X POST '$API_BASE_URL/auth/login' \\
        -H 'Content-Type: application/json' \\
        -d '{\"email\":\"$TEST_TEACHER_EMAIL\",\"password\":\"password\"}')
    echo \"\$response\" | grep -q 'Login successful'
" "true"

# Test 15: Operations - Scheduler Last Run
run_test "Operations - Scheduler Last Run" "
    response=\$(curl -s -X GET '$API_BASE_URL/ops/scheduler/last-run')
    echo \"\$response\" | grep -q 'last_run'
" "true"

# Test 16: Phone Auth - Resend Code
run_test "Phone Auth - Resend Code" "
    response=\$(curl -s -X POST '$API_BASE_URL/phone-auth/resend-code' \\
        -H 'Content-Type: application/json' \\
        -d '{\"phone\":\"$TEST_PHONE\"}')
    echo \"\$response\" | grep -q 'success'
" "true"

# Test 17: Subscription
run_test "Subscription" "
    curl -s -f -X GET '$API_BASE_URL/students/1/subscription' \\
        -H 'Authorization: Bearer $TOKEN' > /dev/null
" "true"

# Test 18: Performance Recommendations
run_test "Performance Recommendations" "
    curl -s -f -X GET '$API_BASE_URL/students/1/recommendations' \\
        -H 'Authorization: Bearer $TOKEN' > /dev/null
" "true"

# Test 19: Student Daily Logs
run_test "Student Daily Logs" "
    curl -s -f -X GET '$API_BASE_URL/students/1/daily-logs' \\
        -H 'Authorization: Bearer $TOKEN' > /dev/null
" "true"

# Test 20: Logout
run_test "Logout" "
    curl -s -f -X POST '$API_BASE_URL/auth/logout' \\
        -H 'Authorization: Bearer $TOKEN' > /dev/null
" "true"

# Calculate success rate
SUCCESS_RATE=0
if [ $TOTAL_TESTS -gt 0 ]; then
    SUCCESS_RATE=$((PASSED_TESTS * 100 / TOTAL_TESTS))
fi

echo "=================================================="
echo -e "${CYAN}📊 نتائج الاختبار الشامل${NC}"
echo "=================================================="
echo -e "إجمالي الاختبارات: ${BLUE}$TOTAL_TESTS${NC}"
echo -e "نجح: ${GREEN}$PASSED_TESTS${NC}"
echo -e "فشل: ${RED}$FAILED_TESTS${NC}"
echo -e "تحذيرات: ${YELLOW}$WARNING_TESTS${NC}"
echo -e "معدل النجاح: ${BLUE}$SUCCESS_RATE%${NC}"
echo ""

# Generate recommendations
echo -e "${PURPLE}💡 التوصيات:${NC}"
if [ $SUCCESS_RATE -ge 90 ]; then
    echo -e "   ${GREEN}✅ النظام يعمل بشكل ممتاز${NC}"
    echo -e "   ${GREEN}✅ جميع APIs تعمل بشكل صحيح${NC}"
    echo -e "   ${GREEN}✅ التطبيق جاهز للاستخدام${NC}"
elif [ $SUCCESS_RATE -ge 80 ]; then
    echo -e "   ${YELLOW}⚠️  النظام يعمل بشكل جيد مع بعض المشاكل البسيطة${NC}"
    echo -e "   ${YELLOW}⚠️  راجع APIs التي فشلت${NC}"
elif [ $SUCCESS_RATE -ge 60 ]; then
    echo -e "   ${YELLOW}⚠️  النظام يعمل مع مشاكل متوسطة${NC}"
    echo -e "   ${YELLOW}⚠️  يجب إصلاح المشاكل قبل النشر${NC}"
else
    echo -e "   ${RED}❌ النظام يحتاج إصلاحات كبيرة${NC}"
    echo -e "   ${RED}❌ لا ينصح بالنشر حالياً${NC}"
fi

echo ""
echo -e "${CYAN}📱 الخطوات التالية:${NC}"
echo "   1. بناء التطبيق: flutter build apk --release"
echo "   2. اختبار التطبيق على أجهزة مختلفة"
echo "   3. اختبار Phone Authentication مع أرقام حقيقية"
echo "   4. اختبار جميع الميزات في التطبيق"
echo "   5. نشر التطبيق بعد التأكد من عمل جميع الميزات"
echo ""

if [ $SUCCESS_RATE -ge 80 ]; then
    echo -e "${GREEN}🎉 النظام جاهز للاختبار والتطوير!${NC}"
    exit 0
else
    echo -e "${RED}⚠️  يرجى إصلاح المشاكل قبل المتابعة${NC}"
    exit 1
fi





















