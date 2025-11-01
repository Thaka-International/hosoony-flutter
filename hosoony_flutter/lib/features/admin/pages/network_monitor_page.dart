import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:connectivity_plus/connectivity_plus.dart';
import 'dart:io';
import 'dart:async';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';

class NetworkMonitorPage extends ConsumerStatefulWidget {
  const NetworkMonitorPage({super.key});

  @override
  ConsumerState<NetworkMonitorPage> createState() => _NetworkMonitorPageState();
}

class _NetworkMonitorPageState extends ConsumerState<NetworkMonitorPage> {
  StreamSubscription<ConnectivityResult>? _connectivitySubscription;
  ConnectivityResult _connectivityResult = ConnectivityResult.none;
  bool _isOnline = false;
  String _connectionType = 'غير معروف';
  String _serverResponse = 'لم يتم الاختبار';
  bool _isServerReachable = false;
  int _responseTime = 0;
  List<NetworkLog> _networkLogs = [];
  Timer? _pingTimer;

  @override
  void initState() {
    super.initState();
    _initConnectivity();
    _startPingTimer();
  }

  @override
  void dispose() {
    _connectivitySubscription?.cancel();
    _pingTimer?.cancel();
    super.dispose();
  }

  Future<void> _initConnectivity() async {
    _connectivitySubscription = Connectivity().onConnectivityChanged.listen(
      (ConnectivityResult result) {
        setState(() {
          _connectivityResult = result;
          _isOnline = result != ConnectivityResult.none;
          _connectionType = _getConnectionType(result);
        });
        _addNetworkLog('تغيير حالة الاتصال: $_connectionType');
      },
    );

    // Get initial connectivity status
    final initialResult = await Connectivity().checkConnectivity();
    setState(() {
      _connectivityResult = initialResult;
      _isOnline = initialResult != ConnectivityResult.none;
      _connectionType = _getConnectionType(initialResult);
    });
  }

  String _getConnectionType(ConnectivityResult result) {
    switch (result) {
      case ConnectivityResult.wifi:
        return 'WiFi';
      case ConnectivityResult.mobile:
        return 'بيانات الجوال';
      case ConnectivityResult.ethernet:
        return 'إيثرنت';
      case ConnectivityResult.none:
        return 'غير متصل';
      default:
        return 'غير معروف';
    }
  }

  void _startPingTimer() {
    _pingTimer = Timer.periodic(const Duration(seconds: 30), (timer) {
      _pingServer();
    });
    // Initial ping
    _pingServer();
  }

  Future<void> _pingServer() async {
    final stopwatch = Stopwatch()..start();
    
    try {
      final response = await ApiService.getSchedulerLastRun();
      stopwatch.stop();
      
      setState(() {
        _isServerReachable = true;
        _responseTime = stopwatch.elapsedMilliseconds;
        _serverResponse = 'متصل - ${stopwatch.elapsedMilliseconds}ms';
      });
      
      _addNetworkLog('اختبار الخادم: نجح (${stopwatch.elapsedMilliseconds}ms)');
    } catch (e) {
      stopwatch.stop();
      
      setState(() {
        _isServerReachable = false;
        _responseTime = 0;
        _serverResponse = 'فشل: $e';
      });
      
      _addNetworkLog('اختبار الخادم: فشل - $e');
    }
  }

  void _addNetworkLog(String message) {
    setState(() {
      _networkLogs.insert(0, NetworkLog(
        timestamp: DateTime.now(),
        message: message,
      ));
      
      // Keep only last 50 logs
      if (_networkLogs.length > 50) {
        _networkLogs = _networkLogs.take(50).toList();
      }
    });
  }

  Future<void> _testSpecificEndpoint(String endpoint) async {
    final stopwatch = Stopwatch()..start();
    
    try {
      String result = '';
      switch (endpoint) {
        case 'auth':
          await ApiService.login('test@test.com', 'test');
          result = 'Auth endpoint';
          break;
        case 'notifications':
          await ApiService.getNotifications();
          result = 'Notifications endpoint';
          break;
        case 'operations':
          await ApiService.getSchedulerLastRun();
          result = 'Operations endpoint';
          break;
      }
      
      stopwatch.stop();
      _addNetworkLog('$result: نجح (${stopwatch.elapsedMilliseconds}ms)');
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('$result يعمل بشكل صحيح'),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      stopwatch.stop();
      _addNetworkLog('$endpoint: فشل - $e');
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('$endpoint فشل: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('مراقب الشبكة والاتصال'),
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _pingServer,
          ),
        ],
      ),
      body: Column(
        children: [
          // حالة الاتصال
          Container(
            padding: const EdgeInsets.all(16),
            child: Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    Row(
                      children: [
                        Icon(
                          _isOnline ? Icons.wifi : Icons.wifi_off,
                          color: _isOnline ? Colors.green : Colors.red,
                          size: 32,
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _isOnline ? 'متصل بالإنترنت' : 'غير متصل بالإنترنت',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Text('نوع الاتصال: $_connectionType'),
                            ],
                          ),
                        ),
                      ],
                    ),
                    
                    const Divider(),
                    
                    // حالة الخادم
                    Row(
                      children: [
                        Icon(
                          _isServerReachable ? Icons.cloud_done : Icons.cloud_off,
                          color: _isServerReachable ? Colors.green : Colors.red,
                          size: 32,
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _isServerReachable ? 'الخادم متاح' : 'الخادم غير متاح',
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Text('الاستجابة: $_serverResponse'),
                              if (_responseTime > 0)
                                Text('وقت الاستجابة: ${_responseTime}ms'),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),

          // اختبارات سريعة
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'اختبارات سريعة للـ APIs',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        ElevatedButton.icon(
                          onPressed: () => _testSpecificEndpoint('auth'),
                          icon: const Icon(Icons.login),
                          label: const Text('Auth'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTokens.primaryGreen,
                            foregroundColor: AppTokens.neutralWhite,
                          ),
                        ),
                        ElevatedButton.icon(
                          onPressed: () => _testSpecificEndpoint('notifications'),
                          icon: const Icon(Icons.notifications),
                          label: const Text('Notifications'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTokens.primaryBlue,
                            foregroundColor: AppTokens.neutralWhite,
                          ),
                        ),
                        ElevatedButton.icon(
                          onPressed: () => _testSpecificEndpoint('operations'),
                          icon: const Icon(Icons.settings),
                          label: const Text('Operations'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTokens.primaryGold,
                            foregroundColor: AppTokens.neutralWhite,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),

          // سجل الشبكة
          Expanded(
            child: Container(
              padding: const EdgeInsets.all(16),
              child: Card(
                child: Column(
                  children: [
                    Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          const Icon(Icons.history),
                          const SizedBox(width: 8),
                          const Text(
                            'سجل الشبكة',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const Spacer(),
                          TextButton(
                            onPressed: () {
                              setState(() {
                                _networkLogs.clear();
                              });
                            },
                            child: const Text('مسح السجل'),
                          ),
                        ],
                      ),
                    ),
                    Expanded(
                      child: _networkLogs.isEmpty
                          ? const Center(
                              child: Text('لا توجد سجلات بعد'),
                            )
                          : ListView.builder(
                              itemCount: _networkLogs.length,
                              itemBuilder: (context, index) {
                                final log = _networkLogs[index];
                                return ListTile(
                                  leading: Icon(
                                    log.message.contains('نجح') ? Icons.check_circle : Icons.error,
                                    color: log.message.contains('نجح') ? Colors.green : Colors.red,
                                    size: 20,
                                  ),
                                  title: Text(log.message),
                                  subtitle: Text(
                                    '${log.timestamp.hour}:${log.timestamp.minute.toString().padLeft(2, '0')}:${log.timestamp.second.toString().padLeft(2, '0')}',
                                  ),
                                  dense: true,
                                );
                              },
                            ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class NetworkLog {
  final DateTime timestamp;
  final String message;

  NetworkLog({
    required this.timestamp,
    required this.message,
  });
}
