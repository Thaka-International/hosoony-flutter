import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/tokens.dart';
import '../../../shared_ui/widgets/copyright_widget.dart';
import '../models/settings_model.dart';
import '../services/settings_service.dart';

class SettingsPage extends ConsumerStatefulWidget {
  const SettingsPage({super.key});

  @override
  ConsumerState<SettingsPage> createState() => _SettingsPageState();
}

class _SettingsPageState extends ConsumerState<SettingsPage> {
  AppSettings _settings = AppSettings();
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final settings = await SettingsService.getSettings();
    setState(() {
      _settings = settings;
      _isLoading = false;
    });
  }

  Future<void> _updateSetting(String key, dynamic value) async {
    await SettingsService.updateSetting(key, value);
    final updatedSettings = await SettingsService.getSettings();
    setState(() {
      _settings = updatedSettings;
    });
    
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('تم حفظ الإعدادات'),
        duration: Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTokens.neutralLight,
      appBar: AppBar(
        backgroundColor: AppTokens.primaryBrown,
        foregroundColor: AppTokens.neutralWhite,
        title: const Text('الإعدادات'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => context.go('/student/home'),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _buildSettingsContent(),
      bottomSheet: const CopyrightWidget(),
    );
  }

  Widget _buildSettingsContent() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Section
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: AppTokens.primaryGradient,
              borderRadius: BorderRadius.circular(16),
              boxShadow: AppTokens.shadowLG,
            ),
            child: Column(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: AppTokens.neutralWhite.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(30),
                  ),
                  child: const Icon(
                    Icons.settings,
                    color: AppTokens.neutralWhite,
                    size: 30,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'إعدادات التطبيق',
                  style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    color: AppTokens.neutralWhite,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  'قم بتخصيص تجربتك في التطبيق',
                  style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: AppTokens.neutralWhite.withOpacity(0.9),
                  ),
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 24),
          
          // Notifications Section
          _buildSection(
            title: 'الإشعارات والتنبيهات',
            icon: Icons.notifications,
            children: [
              const SizedBox(height: 8),
              _buildSwitchTile(
                title: 'تنبيه قبل بداية الحلقة',
                subtitle: 'ستتلقى إشعاراً قبل بداية الحلقة بـ ${_settings.reminderMinutes} دقائق',
                icon: Icons.access_time,
                value: _settings.reminderBeforeClass,
                onChanged: (value) => _updateSetting('reminderBeforeClass', value),
              ),
              const Divider(height: 1),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: Row(
                  children: [
                    const SizedBox(width: 50),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'الوقت قبل الحلقة',
                            style: Theme.of(context).textTheme.bodyMedium,
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              ...[10, 15, 20, 30].map((minutes) {
                                return Expanded(
                                  child: Padding(
                                    padding: const EdgeInsets.only(right: 4),
                                    child: ChoiceChip(
                                      label: Text('$minutes'),
                                      selected: _settings.reminderMinutes == minutes,
                                      selectedColor: AppTokens.primaryBrown,
                                      labelStyle: TextStyle(
                                        color: _settings.reminderMinutes == minutes
                                            ? Colors.white
                                            : Colors.black87,
                                      ),
                                      onSelected: (selected) {
                                        if (selected) {
                                          _updateSetting('reminderMinutes', minutes);
                                        }
                                      },
                                    ),
                                  ),
                                );
                              }),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const Divider(height: 1),
            ],
          ),
          
          const SizedBox(height: 24),
          
          // Sound Notifications Section
          _buildSection(
            title: 'الإشعارات الصوتية',
            icon: Icons.volume_up,
            children: [
              const SizedBox(height: 8),
              _buildSwitchTile(
                title: 'صوت عند إشعار النشاط',
                subtitle: 'سيتم تشغيل صوت عند استلام إشعار نشاط جديد',
                icon: Icons.notification_important,
                value: _settings.soundOnActivity,
                onChanged: (value) => _updateSetting('soundOnActivity', value),
              ),
              const Divider(height: 1),
              _buildSwitchTile(
                title: 'صوت عند إشعار الاختبار',
                subtitle: 'سيتم تشغيل صوت عند استلام إشعار اختبار',
                icon: Icons.quiz,
                value: _settings.soundOnExam,
                onChanged: (value) => _updateSetting('soundOnExam', value),
              ),
            ],
          ),
          
          const SizedBox(height: 24),
          
          // Actions Section
          Card(
            elevation: 2,
            child: Column(
              children: [
                ListTile(
                  leading: Icon(Icons.restore, color: AppTokens.primaryBrown),
                  title: const Text('إعادة الإعدادات الافتراضية'),
                  subtitle: const Text('إعادة جميع الإعدادات إلى القيم الافتراضية'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () => _showResetDialog(),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: Icon(Icons.info_outline, color: AppTokens.primaryBrown),
                  title: const Text('حول التطبيق'),
                  subtitle: const Text('الإصدار 1.0.0'),
                  trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                  onTap: () => _showAboutDialog(),
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 100),
        ],
      ),
    );
  }

  Widget _buildSection({
    required String title,
    required IconData icon,
    required List<Widget> children,
  }) {
    return Card(
      elevation: 2,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppTokens.primaryBrown.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(icon, color: AppTokens.primaryBrown, size: 24),
                ),
                const SizedBox(width: 12),
                Text(
                  title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
          ...children,
        ],
      ),
    );
  }

  Widget _buildSwitchTile({
    required String title,
    required String subtitle,
    required IconData icon,
    required bool value,
    required ValueChanged<bool> onChanged,
  }) {
    return SwitchListTile(
      secondary: Icon(icon, color: AppTokens.primaryBrown),
      title: Text(title),
      subtitle: Text(subtitle),
      value: value,
      onChanged: onChanged,
      activeColor: AppTokens.primaryGreen,
    );
  }

  Future<void> _showResetDialog() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('إعادة الإعدادات'),
        content: const Text('هل أنت متأكد من إعادة جميع الإعدادات إلى القيم الافتراضية؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('إعادة'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await SettingsService.resetToDefaults();
      await _loadSettings();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('تم إعادة الإعدادات الافتراضية'),
            backgroundColor: Colors.green,
          ),
        );
      }
    }
  }

  void _showAboutDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('حول التطبيق'),
        content: const Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('حصوني - تطبيق تعليمي متطور'),
            SizedBox(height: 8),
            Text('الإصدار: 1.0.0'),
            SizedBox(height: 8),
            Text('نظام إدارة تعليمي شامل'),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('حسناً'),
          ),
        ],
      ),
    );
  }
}



