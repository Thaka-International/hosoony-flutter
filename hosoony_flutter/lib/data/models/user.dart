class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final String gender;
  final String status;

  const User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.gender,
    required this.status,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      role: json['role'] ?? 'student',
      gender: json['gender'] ?? 'male',
      status: json['status'] ?? 'active',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'gender': gender,
      'status': status,
    };
  }

  User copyWith({
    int? id,
    String? name,
    String? email,
    String? role,
    String? gender,
    String? status,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      role: role ?? this.role,
      gender: gender ?? this.gender,
      status: status ?? this.status,
    );
  }

  @override
  String toString() {
    return 'User(id: $id, name: $name, email: $email, role: $role, gender: $gender, status: $status)';
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is User &&
        other.id == id &&
        other.name == name &&
        other.email == email &&
        other.role == role &&
        other.gender == gender &&
        other.status == status;
  }

  @override
  int get hashCode {
    return id.hashCode ^
        name.hashCode ^
        email.hashCode ^
        role.hashCode ^
        gender.hashCode ^
        status.hashCode;
  }
}