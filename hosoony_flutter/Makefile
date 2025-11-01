# Hosoony Flutter App - Makefile

API_BASE_URL ?= http://127.0.0.1:8000
ADMIN_EMAIL ?= admin@hosoony.com
ADMIN_PASSWORD ?= password

.PHONY: pub analyze test api-smoke run-android run-ios run-macos build-apk-debug build-apk-release clean generate build-run

# Install dependencies
pub:
	flutter pub get

# Run static analysis
analyze:
	flutter analyze

# Run tests
test:
	flutter test

# Generate code (Freezed, JSON serialization, etc.)
generate:
	flutter packages pub run build_runner build --delete-conflicting-outputs

# API smoke test
api-smoke:
	@echo "🧪 Running API smoke test..."
	@./scripts/api_smoke.sh

# Run on Android
run-android:
	flutter run -d android --dart-define=API_BASE_URL=$(API_BASE_URL)

# Run on iOS
run-ios:
	flutter run -d ios --dart-define=API_BASE_URL=$(API_BASE_URL)

# Run on macOS
run-macos:
	flutter run -d macos --dart-define=API_BASE_URL=$(API_BASE_URL)

# Build debug APK
build-apk-debug:
	flutter build apk --debug --dart-define=API_BASE_URL=$(API_BASE_URL)

# Build release APK
build-apk-release:
	flutter build apk --release --dart-define=API_BASE_URL=$(API_BASE_URL)

# Clean build artifacts
clean:
	flutter clean

# Complete build and run sequence
build-run: pub analyze api-smoke build-apk-debug
	@echo "🎉 Build completed successfully!"
	@echo "📱 APK location: build/app/outputs/flutter-apk/app-debug.apk"
	@echo "🚀 Ready to install on Android device!"


