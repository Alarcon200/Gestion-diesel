import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/fotos_con_nota.dart';
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
  final List<FotoPendiente> _fotos = [];
  bool _guardando = false;

  @override
  void dispose() {
    _observacionesCtrl.dispose();
    for (final f in _fotos) {
      f.liberar();
    }
    super.dispose();
  }

  Future<void> _guardar() async {
    setState(() => _guardando = true);
    try {
      // 1. Subir cada foto y recolectar sus ids
      final fotosIds = <int>[];
      for (final foto in _fotos) {
        final id = await ApiService.subirFoto(foto.archivo, descripcion: foto.texto);
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
          const Text('Fotos del estado en que llega el vehículo',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          const Text('Toma fotos de los daños que ya trae y escribe una nota debajo de cada una.',
              style: TextStyle(color: Marca.gris)),
          const SizedBox(height: 10),
          FotosConNota(fotos: _fotos),
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