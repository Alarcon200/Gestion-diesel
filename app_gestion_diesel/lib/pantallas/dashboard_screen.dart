import 'package:flutter/material.dart';
import 'lista_vehiculos_screen.dart';
import 'ingreso_vehiculo_screen.dart';


class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    const Color isaPrimaryBlue = Color(0xFF1976D2);
    const Color isaAccentRed = Color(0xFFD32F2F);
    const Color textPrimary = Color(0xFF333333);
    const Color textSecondary = Color(0xFF757575);

    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text('Panel de Administrador', style: TextStyle(color: Color(0xFF1976D2), fontWeight: FontWeight.bold)),
        backgroundColor: Colors.white,
        elevation: 0,
        iconTheme: const IconThemeData(color: isaPrimaryBlue),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1.0),
          child: Container(color: isaAccentRed, height: 1.0),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Panel de Administrador',
              style: TextStyle(fontSize: 14, color: textSecondary),
            ),
            const SizedBox(height: 16),
            const Text(
              'Estado del Taller',
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: textPrimary),
            ),
            const SizedBox(height: 16),
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 1.15,
              children: [
                _crearTarjetaEstado('En Proceso', '5', Icons.settings, isaPrimaryBlue),
                _crearTarjetaEstado('Pendientes', '3', Icons.access_time_filled, const Color(0xFFFFA000)),
                _crearTarjetaEstado('Wait Repuestos', '2', Icons.build_circle, isaAccentRed),
                _crearTarjetaEstado('Listo / Facturar', '4', Icons.assignment_turned_in, Colors.green),
              ],
            ),
            const SizedBox(height: 32),
            const Text(
              'Acciones Rápidas',
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: textPrimary),
            ),
            const SizedBox(height: 16),
            _crearBotonAccion(
              context,
              'Ver Vehículos y Órdenes',
              Icons.list_alt,
              isaAccentRed,
              isaPrimaryBlue,
              () {
                // Navegación directa a la lista
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (context) => const ListaVehiculosScreen()),
                );
              }
            ),
            const SizedBox(height: 12),
            _crearBotonAccion(
              context,
              'Ingresar Nuevo Vehículo',
              Icons.add_circle_outline,
              isaAccentRed,
              isaPrimaryBlue,
              () {
                // AQUI ESTÁ LA MAGIA: Navegación directa al formulario
                Navigator.push(
                context,
                MaterialPageRoute(builder: (context) => const IngresoVehiculoScreen()),
                )   ;
              }
            ),
          ],
        ),
      ),
    );
  }

  Widget _crearTarjetaEstado(String titulo, String cantidad, IconData icono, Color color) {
    return Card(
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16), 
        side: BorderSide(color: color.withOpacity(0.3), width: 1),
      ),
      color: Colors.white,
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icono, color: color, size: 30),
            const SizedBox(height: 12),
            Text(
              cantidad,
              style: TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: color),
            ),
            Text(
              titulo,
              style: const TextStyle(fontSize: 14, color: Color(0xFF757575), fontWeight: FontWeight.w500),
            ),
          ],
        ),
      ),
    );
  }

  Widget _crearBotonAccion(BuildContext context, String texto, IconData icono, Color bordeColor, Color contentColor, VoidCallback accion) {
    return SizedBox(
      width: double.infinity,
      height: 65,
      child: ElevatedButton.icon(
        onPressed: accion,
        icon: Icon(icono, size: 28, color: contentColor),
        label: Text(
          texto,
          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: contentColor),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.white,
          elevation: 0,
          side: BorderSide(color: bordeColor, width: 2.0),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          padding: const EdgeInsets.symmetric(horizontal: 24),
        ),
      ),
    );
  }
}