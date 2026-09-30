import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';
import 'dashboard_screen.dart';

class InspeccionScreen extends StatefulWidget {
  final int ingresoId;
  const InspeccionScreen({super.key, required this.ingresoId});

  @override
  State<InspeccionScreen> createState() => _InspeccionScreenState();
}

class _ItemChecklist {
  final String nombre;
  String estado = 'Bueno';
  _ItemChecklist(this.nombre);
}

class _InspeccionScreenState extends State<InspeccionScreen> {
  final _items = [
    _ItemChecklist('Llantas'),
    _ItemChecklist('Luces'),
    _ItemChecklist('Carroceria'),
    _ItemChecklist('Espejos'),
    _ItemChecklist('Vidrios'),
    _ItemChecklist('Cabina'),
  ];
  String _nivelCombustible = 'Medio';
  final _observacionesCtrl = TextEditingController();
  final List<File> _fotos = [];
  bool _guardando = false;

  Future<void> _tomarFoto() async {
    final picker = ImagePicker();
    final foto = await picker.pickImage(source: ImageSource.camera, imageQuality: 70);
    if (foto != null) {
      setState(() => _fotos.add(File(foto.path)));
    }
  }

  Future<void> _guardar() async {
    setState(() => _guardando = true);
    try {
      // 1. Subir cada foto y recolectar sus ids
      final fotosIds = <int>[];
      for (final foto in _fotos) {
        final id = await ApiService.subirFoto(foto, descripcion: 'Inspeccion de ingreso');
        fotosIds.add(id);
      }

      // 2. Crear la inspeccion con el checklist y las fotos
      await ApiService.post('inspecciones', {
        'ingreso_id': widget.ingresoId,
        'fecha_inspeccion': DateTime.now().toIso8601String(),
        'nivel_combustible': _nivelCombustible,
        'observaciones': _observacionesCtrl.text,
        'items': _items
            .map((i) => {'item': i.nombre, 'estado': i.estado})
            .toList(),
        'fotos_ids': fotosIds,
      });

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Inspeccion registrada correctamente')),
      );
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (_) => const DashboardScreen()),
        (route) => false,
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
    return Scaffold(
      appBar: AppBar(title: const Text('Inspeccion de ingreso')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text('Checklist', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          ..._items.map((item) => Card(
                child: ListTile(
                  title: Text(item.nombre),
                  trailing: DropdownButton<String>(
                    value: item.estado,
                    items: ['Bueno', 'Regular', 'Dañado']
                        .map((e) => DropdownMenuItem(value: e, child: Text(e)))
                        .toList(),
                    onChanged: (v) => setState(() => item.estado = v!),
                  ),
                ),
              )),
          const SizedBox(height: 16),
          DropdownButtonFormField<String>(
            decoration: const InputDecoration(labelText: 'Nivel de combustible', border: OutlineInputBorder()),
            value: _nivelCombustible,
            items: ['Vacio', 'Bajo', 'Medio', 'Alto', 'Lleno']
                .map((e) => DropdownMenuItem(value: e, child: Text(e)))
                .toList(),
            onChanged: (v) => setState(() => _nivelCombustible = v!),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _observacionesCtrl,
            decoration: const InputDecoration(labelText: 'Observaciones', border: OutlineInputBorder()),
            maxLines: 3,
          ),
          const SizedBox(height: 24),
          const Text('Fotos', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              ..._fotos.map((f) => ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.file(f, width: 90, height: 90, fit: BoxFit.cover),
                  )),
              InkWell(
                onTap: _tomarFoto,
                child: Container(
                  width: 90,
                  height: 90,
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.camera_alt, size: 32),
                ),
              ),
            ],
          ),
          const SizedBox(height: 32),
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: _guardando ? null : _guardar,
              child: _guardando
                  ? const CircularProgressIndicator(color: Colors.white)
                  : const Text('Guardar inspeccion'),
            ),
          ),
        ],
      ),
    );
  }
}