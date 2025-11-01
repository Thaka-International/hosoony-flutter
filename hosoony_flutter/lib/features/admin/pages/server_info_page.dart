import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';
import '../../../core/config/env.dart';

class ServerInfoPage extends StatefulWidget {
  const ServerInfoPage({super.key});

  @override
  State<ServerInfoPage> createState() => _ServerInfoPageState();
}

class _ServerInfoPageState extends State<ServerInfoPage> {
  PackageInfo _packageInfo = PackageInfo(
    appName: 'Unknown',
    packageName: 'Unknown',
    version: 'Unknown',
    buildNumber: 'Unknown',
  );

  @override
  void initState() {
    super.initState();
    _initPackageInfo();
  }

  Future<void> _initPackageInfo() async {
    final info = await PackageInfo.fromPlatform();
    setState(() {
      _packageInfo = info;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Server Info'),
      ),
      body: ListView(
        children: <Widget>[
          ListTile(
            title: const Text('API Base URL'),
            subtitle: Text(Env.baseUrl),
          ),
          ListTile(
            title: const Text('API Documentation'),
            subtitle: const Text(Env.apiDocumentation),
            onTap: () { /* Handle tap */ },
          ),
          ListTile(
            title: const Text('App Version'),
            subtitle: Text('${_packageInfo.version}+${_packageInfo.buildNumber}'),
          ),
        ],
      ),
    );
  }
}
