import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/tokens.dart';
import '../../../services/api_service.dart';

class PhoneAuthTestPage extends ConsumerStatefulWidget {
  const PhoneAuthTestPage({super.key});

  @override
  ConsumerState<PhoneAuthTestPage> createState() => _PhoneAuthTestPageState();
}

class _PhoneAuthTestPageState extends ConsumerState<PhoneAuthTestPage> {
  final _phoneController = TextEditingController(text: '966541355804');
  final _codeController = TextEditingController();
  
  bool _isLoading = false;
  String _currentStep = 'send_code'; // send_code, verify_code, success
  String _lastResponse = '';
  List<PhoneTestLog> _testLogs = [];

  @override
  void dispose() {
    _phoneController.dispose();
    _codeController.dispose();
    super.dispose();
  }

  void _addTestLog(String action, String result, bool success) {
    setState(() {
      _testLogs.insert(0, PhoneTestLog(
        timestamp: DateTime.now(),
        action: action,
        result: result,
        success: success,
      ));
    });
  }

  Future<void> _sendCode() async {
    if (_phoneController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('يرجى إدخال رقم الجوال'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final response = await ApiService.sendPhoneCode(_phoneController.text);
      
      setState(() {
        _lastResponse = response.toString();
        _currentStep = 'verify_code';
      });

      _addTestLog(
        'إرسال رمز التحقق',
        response.toString(),
        response['success'] == true,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(response['message'] ?? 'تم إرسال الطلب'),
          backgroundColor: response['success'] == true ? Colors.green : Colors.orange,
        ),
      );
    } catch (e) {
      _addTestLog(
        'إرسال رمز التحقق',
        e.toString(),
        false,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('خطأ: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _verifyCode() async {
    if (_codeController.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('يرجى إدخال رمز التحقق'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final response = await ApiService.verifyPhoneCode(
        _phoneController.text,
        _codeController.text,
      );

      setState(() {
        _lastResponse = response.toString();
        _currentStep = response['success'] == true ? 'success' : 'verify_code';
      });

      _addTestLog(
        'التحقق من الرمز',
        response.toString(),
        response['success'] == true,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(response['message'] ?? 'تم التحقق'),
          backgroundColor: response['success'] == true ? Colors.green : Colors.red,
        ),
      );
    } catch (e) {
      _addTestLog(
        'التحقق من الرمز',
        e.toString(),
        false,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('خطأ: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _resendCode() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await ApiService.resendPhoneCode(_phoneController.text);
      
      _addTestLog(
        'إعادة إرسال الرمز',
        response.toString(),
        response['success'] == true,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(response['message'] ?? 'تم إعادة الإرسال'),
          backgroundColor: response['success'] == true ? Colors.green : Colors.orange,
        ),
      );
    } catch (e) {
      _addTestLog(
        'إعادة إرسال الرمز',
        e.toString(),
        false,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('خطأ: $e'),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _resetTest() {
    setState(() {
      _currentStep = 'send_code';
      _codeController.clear();
      _lastResponse = '';
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('اختبار Phone Authentication'),
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _resetTest,
          ),
        ],
      ),
      body: Column(
        children: [
          // حالة الاختبار
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
                          _currentStep == 'success' ? Icons.check_circle : Icons.phone,
                          color: _currentStep == 'success' ? Colors.green : AppTokens.primaryBlue,
                          size: 32,
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                _getStepTitle(),
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              Text(_getStepDescription()),
                            ],
                          ),
                        ),
                      ],
                    ),
                    
                    if (_lastResponse.isNotEmpty) ...[
                      const Divider(),
                      ExpansionTile(
                        title: const Text('آخر استجابة'),
                        children: [
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.grey[100],
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              _lastResponse,
                              style: const TextStyle(
                                fontFamily: 'monospace',
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ),
          ),

          // نموذج الاختبار
          Container(
            padding: const EdgeInsets.all(16),
            child: Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    TextFormField(
                      controller: _phoneController,
                      keyboardType: TextInputType.phone,
                      decoration: const InputDecoration(
                        labelText: 'رقم الجوال',
                        prefixText: '+',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.phone),
                      ),
                      enabled: _currentStep == 'send_code',
                    ),
                    
                    if (_currentStep == 'verify_code' || _currentStep == 'success') ...[
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _codeController,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(
                          labelText: 'رمز التحقق',
                          border: OutlineInputBorder(),
                          prefixIcon: Icon(Icons.security),
                        ),
                        enabled: _currentStep == 'verify_code',
                      ),
                    ],
                    
                    const SizedBox(height: 16),
                    
                    Row(
                      children: [
                        if (_currentStep == 'send_code') ...[
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: _isLoading ? null : _sendCode,
                              icon: _isLoading 
                                  ? const SizedBox(
                                      width: 16,
                                      height: 16,
                                      child: CircularProgressIndicator(strokeWidth: 2),
                                    )
                                  : const Icon(Icons.send),
                              label: const Text('إرسال رمز التحقق'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppTokens.primaryGreen,
                                foregroundColor: AppTokens.neutralWhite,
                              ),
                            ),
                          ),
                        ],
                        
                        if (_currentStep == 'verify_code') ...[
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: _isLoading ? null : _verifyCode,
                              icon: _isLoading 
                                  ? const SizedBox(
                                      width: 16,
                                      height: 16,
                                      child: CircularProgressIndicator(strokeWidth: 2),
                                    )
                                  : const Icon(Icons.check),
                              label: const Text('التحقق من الرمز'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppTokens.primaryBlue,
                                foregroundColor: AppTokens.neutralWhite,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          ElevatedButton.icon(
                            onPressed: _isLoading ? null : _resendCode,
                            icon: const Icon(Icons.refresh),
                            label: const Text('إعادة إرسال'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppTokens.primaryGold,
                              foregroundColor: AppTokens.neutralWhite,
                            ),
                          ),
                        ],
                        
                        if (_currentStep == 'success') ...[
                          Expanded(
                            child: ElevatedButton.icon(
                              onPressed: _resetTest,
                              icon: const Icon(Icons.restart_alt),
                              label: const Text('اختبار جديد'),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppTokens.primaryBrown,
                                foregroundColor: AppTokens.neutralWhite,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),

          // سجل الاختبارات
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
                            'سجل الاختبارات',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const Spacer(),
                          TextButton(
                            onPressed: () {
                              setState(() {
                                _testLogs.clear();
                              });
                            },
                            child: const Text('مسح السجل'),
                          ),
                        ],
                      ),
                    ),
                    Expanded(
                      child: _testLogs.isEmpty
                          ? const Center(
                              child: Text('لا توجد سجلات بعد'),
                            )
                          : ListView.builder(
                              itemCount: _testLogs.length,
                              itemBuilder: (context, index) {
                                final log = _testLogs[index];
                                return ListTile(
                                  leading: Icon(
                                    log.success ? Icons.check_circle : Icons.error,
                                    color: log.success ? Colors.green : Colors.red,
                                    size: 20,
                                  ),
                                  title: Text(log.action),
                                  subtitle: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(log.result),
                                      Text(
                                        '${log.timestamp.hour}:${log.timestamp.minute.toString().padLeft(2, '0')}:${log.timestamp.second.toString().padLeft(2, '0')}',
                                        style: const TextStyle(fontSize: 10),
                                      ),
                                    ],
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

  String _getStepTitle() {
    switch (_currentStep) {
      case 'send_code':
        return 'إرسال رمز التحقق';
      case 'verify_code':
        return 'التحقق من الرمز';
      case 'success':
        return 'تم التحقق بنجاح';
      default:
        return 'بدء الاختبار';
    }
  }

  String _getStepDescription() {
    switch (_currentStep) {
      case 'send_code':
        return 'أدخل رقم الجوال واضغط إرسال رمز التحقق';
      case 'verify_code':
        return 'أدخل رمز التحقق الذي تم إرساله إلى واتساب';
      case 'success':
        return 'تم التحقق من الرمز بنجاح!';
      default:
        return 'اختبار Phone Authentication';
    }
  }
}

class PhoneTestLog {
  final DateTime timestamp;
  final String action;
  final String result;
  final bool success;

  PhoneTestLog({
    required this.timestamp,
    required this.action,
    required this.result,
    required this.success,
  });
}





















