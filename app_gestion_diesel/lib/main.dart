import 'package:flutter/material.dart';
import 'services/api_service.dart';
import 'pantallas/login_screen.dart';
import 'pantallas/dashboard_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await ApiService.cargarSesion();
  runApp(const GestionDieselApp());
}

class GestionDieselApp extends StatelessWidget {
  const GestionDieselApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Gestion Diesel',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(primarySwatch: Colors.blue, useMaterial3: true),
      home: ApiService.autenticado ? const DashboardScreen() : const LoginScreen(),
    );
  }
}