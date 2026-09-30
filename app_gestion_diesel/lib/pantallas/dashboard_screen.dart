import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'login_screen.dart';
import 'clientes_screen.dart';
import 'vehiculos_screen.dart';
import 'ingreso_screen.dart';

class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Panel'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () async {
              await ApiService.cerrarSesion();
              if (!context.mounted) return;
              Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
            },
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _botonMenu(context, 'Ingresar vehiculo al taller', Icons.directions_car, () {
            Navigator.push(context, MaterialPageRoute(builder: (_) => const IngresoScreen()));
          }),
          const SizedBox(height: 12),
          _botonMenu(context, 'Clientes', Icons.people, () {
            Navigator.push(context, MaterialPageRoute(builder: (_) => const ClientesScreen()));
          }),
          const SizedBox(height: 12),
          _botonMenu(context, 'Vehiculos', Icons.local_shipping, () {
            Navigator.push(context, MaterialPageRoute(builder: (_) => const VehiculosScreen()));
          }),
        ],
      ),
    );
  }

  Widget _botonMenu(BuildContext context, String texto, IconData icono, VoidCallback onTap) {
    return Card(
      child: ListTile(
        leading: Icon(icono, size: 32),
        title: Text(texto, style: const TextStyle(fontSize: 16)),
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}