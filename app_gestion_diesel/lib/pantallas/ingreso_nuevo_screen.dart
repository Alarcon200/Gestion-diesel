import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'inspeccion_screen.dart';

class IngresoNuevoScreen extends StatefulWidget {
  const IngresoNuevoScreen({super.key});

  @override
  State<IngresoNuevoScreen> createState() => _IngresoNuevoScreenState();
}

class _IngresoNuevoScreenState extends State<IngresoNuevoScreen> {
  List<dynamic> _vehiculos = [];
  int? _vehiculoSeleccionado;
  final _conductorCtrl = TextEditingController();
  final _motivoCtrl = TextEditingController();
  final _kilometrajeCtrl = TextEditingController();
  bool _cargando = true;
  bool _guardando = false;

  @override
  void initState() {
    super.initState();
    _cargarVehiculos();
  }

  Future<void> _cargarVehiculos() async {
    try {
      final data = await ApiService.get('vehiculos');
      setState(() {
        _vehiculos = data;
        _cargando = false;
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
      setState(() => _cargando = false);
    }
  }

  Future<void> _guardar() async {
    if (_vehiculoSeleccionado == null || _motivoCtrl.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Selecciona un vehiculo y escribe el motivo')),
      );
      return;
    }

    setState(() => _guardando = true);
    try {
      final ingreso = await ApiService.post('ingresos', {
        'vehiculo_id': _vehiculoSeleccionado,
        'conductor': _conductorCtrl.text,
        'fecha_ingreso': DateTime.now().toIso8601String(),
        'motivo': _motivoCtrl.text,
        'kilometraje': int.tryParse(_kilometrajeCtrl.text),
      });

      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => InspeccionScreen(ingresoId: ingreso['id'])),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _guardando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_cargando) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (_vehiculos.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: const Text('Ingresar vehiculo')),
        body: const Center(
          child: Padding(
            padding: EdgeInsets.all(24),
            child: Text(
              'No hay vehiculos registrados. Ve a la seccion Vehiculos y registra uno primero.',
              textAlign: TextAlign.center,
            ),
          ),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Ingresar vehiculo al taller')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            DropdownButtonFormField<int>(
              decoration: const InputDecoration(labelText: 'Vehiculo', border: OutlineInputBorder()),
              items: _vehiculos
                  .map<DropdownMenuItem<int>>((v) => DropdownMenuItem(
                        value: v['id'],
                        child: Text('${v['placa']} - ${v['marca'] ?? ''}'),
                      ))
                  .toList(),
              onChanged: (v) => setState(() => _vehiculoSeleccionado = v),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _conductorCtrl,
              decoration: const InputDecoration(labelText: 'Conductor', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _motivoCtrl,
              decoration: const InputDecoration(labelText: 'Motivo de ingreso', border: OutlineInputBorder()),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _kilometrajeCtrl,
              decoration: const InputDecoration(labelText: 'Kilometraje', border: OutlineInputBorder()),
              keyboardType: TextInputType.number,
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _guardando ? null : _guardar,
                child: _guardando
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text('Registrar ingreso y continuar a inspeccion'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}