import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'diagnostico_screen.dart';
import 'reparacion_screen.dart';
import 'prueba_screen.dart';

class DetalleIngresoScreen extends StatefulWidget {
  final Map<String, dynamic> ingreso;
  const DetalleIngresoScreen({super.key, required this.ingreso});

  @override
  State<DetalleIngresoScreen> createState() => _DetalleIngresoScreenState();
}

class _DetalleIngresoScreenState extends State<DetalleIngresoScreen> {
  bool _generandoInforme = false;

  Future<void> _generarInforme() async {
    setState(() => _generandoInforme = true);
    try {
      await ApiService.post('informes-servicio', {
        'ingreso_id': widget.ingreso['id'],
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Informe generado correctamente')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _generandoInforme = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final ing = widget.ingreso;
    final vehiculo = ing['vehiculo'];

    return Scaffold(
      appBar: AppBar(title: Text(vehiculo?['placa'] ?? 'Vehiculo')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('${vehiculo?['marca'] ?? ''} ${vehiculo?['modelo'] ?? ''}',
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text('Cliente: ${vehiculo?['cliente']?['nombre'] ?? '-'}'),
                  Text('Motivo: ${ing['motivo'] ?? '-'}'),
                  Text('Estado: ${ing['estado'] ?? '-'}'),
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),
          _botonAccion(context, 'Registrar diagnostico', Icons.assignment, () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => DiagnosticoScreen(ingresoId: ing['id'])),
            );
          }),
          const SizedBox(height: 12),
          _botonAccion(context, 'Registrar reparacion (con fotos)', Icons.build, () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => ReparacionScreen(ingresoId: ing['id'])),
            );
          }),
          const SizedBox(height: 12),
          _botonAccion(context, 'Registrar prueba de calidad', Icons.check_circle, () {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => PruebaScreen(ingresoId: ing['id'])),
            );
          }),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton.icon(
              icon: const Icon(Icons.picture_as_pdf),
              label: _generandoInforme
                  ? const CircularProgressIndicator(color: Colors.white)
                  : const Text('Generar informe'),
              onPressed: _generandoInforme ? null : _generarInforme,
            ),
          ),
        ],
      ),
    );
  }

  Widget _botonAccion(BuildContext context, String texto, IconData icono, VoidCallback onTap) {
    return Card(
      child: ListTile(
        leading: Icon(icono, size: 28),
        title: Text(texto),
        trailing: const Icon(Icons.chevron_right),
        onTap: onTap,
      ),
    );
  }
}