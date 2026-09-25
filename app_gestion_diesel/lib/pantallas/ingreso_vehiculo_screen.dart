import 'package:flutter/material.dart';
import 'inspeccion_inicial_screen.dart'; // Importamos la siguiente pantalla

class IngresoVehiculoScreen extends StatefulWidget {
  const IngresoVehiculoScreen({super.key});

  @override
  State<IngresoVehiculoScreen> createState() => _IngresoVehiculoScreenState();
}

class _IngresoVehiculoScreenState extends State<IngresoVehiculoScreen> {
  final TextEditingController _placaController = TextEditingController();
  final TextEditingController _vehiculoController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text('Ingreso de Vehículo', style: TextStyle(color: Colors.white)),
        backgroundColor: const Color(0xFF1976D2),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Datos Básicos',
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF333333)),
            ),
            const SizedBox(height: 20),
            TextField(
              controller: _placaController,
              decoration: InputDecoration(
                labelText: 'Placa (Ej: ABC-123)',
                prefixIcon: const Icon(Icons.directions_car),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _vehiculoController,
              decoration: InputDecoration(
                labelText: 'Vehículo (Marca / Modelo)',
                prefixIcon: const Icon(Icons.build),
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                filled: true,
                fillColor: Colors.white,
              ),
            ),
            const Spacer(),
            SizedBox(
              width: double.infinity,
              height: 55,
              child: ElevatedButton(
                onPressed: () {
                  // Validar que no estén vacíos
                  if (_placaController.text.isEmpty || _vehiculoController.text.isEmpty) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(content: Text('Por favor llena ambos campos')),
                    );
                    return;
                  }
                  
                  // Avanzar a la inspección pasando los datos
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => InspeccionInicialScreen(
                        placa: _placaController.text,
                        vehiculo: _vehiculoController.text,
                      ),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF1976D2),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Continuar a Inspección', style: TextStyle(fontSize: 18, color: Colors.white)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}