import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/fotos_con_nota.dart';

class ReparacionScreen extends StatefulWidget {
  final int ingresoId;
  const ReparacionScreen({super.key, required this.ingresoId});

  @override
  State<ReparacionScreen> createState() => _ReparacionScreenState();
}

class _ReparacionScreenState extends State<ReparacionScreen> {
  final _trabajoCtrl = TextEditingController();
  final _observacionesCtrl = TextEditingController();
  final List<FotoPendiente> _fotos = [];
  bool _guardando = false;

  @override
  void dispose() {
    _trabajoCtrl.dispose();
    _observacionesCtrl.dispose();
    for (final f in _fotos) {
      f.liberar();
    }
    super.dispose();
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
        final id = await ApiService.subirFoto(foto.archivo, descripcion: foto.texto);
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
          const SizedBox(height: 4),
          const Text('Escribe una nota debajo de cada foto: qué se arregló o cambió.',
              style: TextStyle(color: Marca.gris)),
          const SizedBox(height: 10),
          FotosConNota(fotos: _fotos),
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