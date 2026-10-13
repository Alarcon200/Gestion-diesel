import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../services/api_service.dart';

class ReparacionScreen extends StatefulWidget {
  final int ingresoId;
  const ReparacionScreen({super.key, required this.ingresoId});

  @override
  State<ReparacionScreen> createState() => _ReparacionScreenState();
}

class _ReparacionScreenState extends State<ReparacionScreen> {
  final _trabajoCtrl = TextEditingController();
  final _observacionesCtrl = TextEditingController();
  final List<File> _fotos = [];
  bool _guardando = false;

  Future<void> _tomarFoto() async {
    final picker = ImagePicker();
    final foto = await picker.pickImage(source: ImageSource.camera, imageQuality: 70);
    if (foto != null) setState(() => _fotos.add(File(foto.path)));
  }

  Future<void> _guardar() async {
    if (_trabajoCtrl.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Describe el trabajo realizado')),
      );
      return;
    }
    if (_fotos.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Debes tomar al menos una foto de evidencia')),
      );
      return;
    }

    setState(() => _guardando = true);
    try {
      final fotosIds = <int>[];
      for (final foto in _fotos) {
        final id = await ApiService.subirFoto(foto, descripcion: 'Reparacion');
        fotosIds.add(id);
      }

      await ApiService.post('reparaciones', {
        'ingreso_id': widget.ingresoId,
        'trabajo_realizado': _trabajoCtrl.text,
        'observaciones': _observacionesCtrl.text,
        'fotos_ids': fotosIds,
      });

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Reparacion registrada')),
      );
      Navigator.pop(context);
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
      appBar: AppBar(title: const Text('Reparacion')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          TextField(
            controller: _trabajoCtrl,
            decoration: const InputDecoration(labelText: 'Trabajo realizado', border: OutlineInputBorder()),
            maxLines: 3,
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _observacionesCtrl,
            decoration: const InputDecoration(labelText: 'Observaciones', border: OutlineInputBorder()),
            maxLines: 2,
          ),
          const SizedBox(height: 20),
          const Text('Fotos de evidencia (obligatorio al menos 1)', style: TextStyle(fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              ..._fotos.map((f) => Stack(
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(8),
                        child: Image.file(f, width: 90, height: 90, fit: BoxFit.cover),
                      ),
                      Positioned(
                        top: 0,
                        right: 0,
                        child: GestureDetector(
                          onTap: () => setState(() => _fotos.remove(f)),
                          child: const CircleAvatar(
                            radius: 10,
                            backgroundColor: Colors.red,
                            child: Icon(Icons.close, size: 14, color: Colors.white),
                          ),
                        ),
                      ),
                    ],
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
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: _guardando ? null : _guardar,
              child: _guardando
                  ? const CircularProgressIndicator(color: Colors.white)
                  : const Text('Guardar reparacion'),
            ),
          ),
        ],
      ),
    );
  }
}