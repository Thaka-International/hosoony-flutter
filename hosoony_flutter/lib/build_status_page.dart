import 'package:flutter/material.dart';
import 'dart:io';

class BuildStatusPage extends StatefulWidget {
  const BuildStatusPage({super.key});

  @override
  State<BuildStatusPage> createState() => _BuildStatusPageState();
}

class _BuildStatusPageState extends State<BuildStatusPage> {
  bool _isChecking = true;
  String _status = 'جاري التحقق...';
  String _apkPath = '';
  String _apkSize = '';
  String _lastModified = '';
  bool _apkExists = false;

  @override
  void initState() {
    super.initState();
    _checkBuildStatus();
  }

  Future<void> _checkBuildStatus() async {
    setState(() {
      _isChecking = true;
      _status = 'جاري التحقق من حالة البناء...';
    });

    try {
      // Get the current working directory and construct the full path
      final currentDir = Directory.current.path;
      final apkPath = '$currentDir/build/app/outputs/flutter-apk/app-debug.apk';
      final apkFile = File(apkPath);
      
      if (await apkFile.exists()) {
        final stat = await apkFile.stat();
        final sizeInMB = (stat.size / (1024 * 1024)).toStringAsFixed(1);
        
        setState(() {
          _apkExists = true;
          _apkPath = apkPath;
          _apkSize = '$sizeInMB MB';
          _lastModified = stat.modified.toString().split('.')[0];
          _status = '✅ تم بناء APK بنجاح!';
        });
      } else {
        setState(() {
          _apkExists = false;
          _status = '❌ APK غير موجود - البناء لم يكتمل';
        });
      }
    } catch (e) {
      setState(() {
        _apkExists = false;
        _status = '❌ خطأ في التحقق: $e';
      });
    } finally {
      setState(() {
        _isChecking = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('حالة البناء'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _checkBuildStatus,
          ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Status Card
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  children: [
                    if (_isChecking)
                      const CircularProgressIndicator()
                    else
                      Icon(
                        _apkExists ? Icons.check_circle : Icons.error,
                        size: 64,
                        color: _apkExists ? Colors.green : Colors.red,
                      ),
                    const SizedBox(height: 16),
                    Text(
                      _status,
                      style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                        color: _apkExists ? Colors.green : Colors.red,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            
            // APK Details
            if (_apkExists) ...[
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'تفاصيل APK',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      _buildDetailRow('المسار:', _apkPath),
                      _buildDetailRow('الحجم:', _apkSize),
                      _buildDetailRow('آخر تعديل:', _lastModified),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),
            ],
            
            // Actions
            ElevatedButton.icon(
              onPressed: _checkBuildStatus,
              icon: const Icon(Icons.refresh),
              label: const Text('إعادة التحقق'),
            ),
            const SizedBox(height: 16),
            
            if (_apkExists)
              ElevatedButton.icon(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('APK جاهز للتثبيت!'),
                      backgroundColor: Colors.green,
                    ),
                  );
                },
                icon: const Icon(Icons.install_mobile),
                label: const Text('APK جاهز للتثبيت'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.green,
                  foregroundColor: Colors.white,
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 100,
            child: Text(
              label,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.grey,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontFamily: 'monospace',
              ),
            ),
          ),
        ],
      ),
    );
  }
}