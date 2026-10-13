import 'package:flutter/material.dart';
import '../services/api_service.dart';

class PruebaScreen extends StatefulWidget {
  final int ingresoId;
  const PruebaScreen({super.key, required this.ingresoId});

  @override
  State<PruebaScreen> createState() => _PruebaScreenState();
}

class _PruebaScreenState extends State<PruebaScreen> {
  final _pruebaCtrl = TextEditingController();
  final _observacionesCtrl = TextEditingController();
  String _resultado = 'correcto';
  bool _guardando = false;

  Future<void> _guardar() async {
    if (_pruebaCtrl.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Describe la prueba realizada')),
      );
      return;
    }

    setState(() => _guardando = true);
    try {
      await ApiService.post('pruebas', {
        'ingreso_id': widget.ingresoId,
        'prueba_realizada': _pruebaCtrl.text,
        'resultado': _resultado,
        'observaciones': _observacionesCtrl.text,
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Prueba registrada')),
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
      appBar: AppBar(title: const Text('Prueba de calidad')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            TextField(
              controller: _pruebaCtrl,
              decoration: const InputDecoration(labelText: 'Prueba realizada', border: OutlineInputBorder()),
              maxLines: 2,
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              decoration: const InputDecoration(labelText: 'Resultado', border: OutlineInputBorder()),
              value: _resultado,
              items: const [
                DropdownMenuItem(value: 'correcto', child: Text('Correcto')),
                DropdownMenuItem(value: 'con_observaciones', child: Text('Con observaciones')),
              ],
              onChanged: (v) => setState(() => _resultado = v!),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _observacionesCtrl,
              decoration: const InputDecoration(labelText: 'Observaciones', border: OutlineInputBorder()),
              maxLines: 2,
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _guardando ? null : _guardar,
                child: _guardando
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text('Guardar prueba'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}