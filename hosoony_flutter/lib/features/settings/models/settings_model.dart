class AppSettings {
  final bool reminderBeforeClass;
  final bool soundOnActivity;
  final bool soundOnExam;
  final int reminderMinutes;

  AppSettings({
    this.reminderBeforeClass = true,
    this.soundOnActivity = true,
    this.soundOnExam = true,
    this.reminderMinutes = 10,
  });

  Map<String, dynamic> toJson() {
    return {
      'reminderBeforeClass': reminderBeforeClass,
      'soundOnActivity': soundOnActivity,
      'soundOnExam': soundOnExam,
      'reminderMinutes': reminderMinutes,
    };
  }

  factory AppSettings.fromJson(Map<String, dynamic> json) {
    return AppSettings(
      reminderBeforeClass: json['reminderBeforeClass'] ?? true,
      soundOnActivity: json['soundOnActivity'] ?? true,
      soundOnExam: json['soundOnExam'] ?? true,
      reminderMinutes: json['reminderMinutes'] ?? 10,
    );
  }

  AppSettings copyWith({
    bool? reminderBeforeClass,
    bool? soundOnActivity,
    bool? soundOnExam,
    int? reminderMinutes,
  }) {
    return AppSettings(
      reminderBeforeClass: reminderBeforeClass ?? this.reminderBeforeClass,
      soundOnActivity: soundOnActivity ?? this.soundOnActivity,
      soundOnExam: soundOnExam ?? this.soundOnExam,
      reminderMinutes: reminderMinutes ?? this.reminderMinutes,
    );
  }
}



