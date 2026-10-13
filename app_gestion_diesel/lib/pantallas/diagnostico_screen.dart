import 'package:flutter/material.dart';
import '../services/api_service.dart';

class DiagnosticoScreen extends StatefulWidget {
  final int ingresoId;
  const DiagnosticoScreen({super.key, required this.ingresoId});

  @override
  State<DiagnosticoScreen> createState() => _DiagnosticoScreenState();
}

class _DiagnosticoScreenState extends State<DiagnosticoScreen> {
  final _problemaCtrl = TextEditingController();
  final _causaCtrl = TextEditingController();
  final _trabajoCtrl = TextEditingController();
  final _recomendacionesCtrl = TextEditingController();
  bool _guardando = false;

  Future<void> _guardar() async {
    if (_problemaCtrl.text.isEmpty || _trabajoCtrl.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Completa el problema encontrado y el trabajo a realizar')),
      );
      return;
    }

    setState(() => _guardando = true);
    try {
      await ApiService.post('diagnosticos', {
        'ingreso_id': widget.ingresoId,
        'problema_encontrado': _problemaCtrl.text,
        'causa_probable': _causaCtrl.text,
        'trabajo_a_realizar': _trabajoCtrl.text,
        'recomendaciones': _recomendacionesCtrl.text,
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Diagnostico registrado')),
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
      appBar: AppBar(title: const Text('Diagnostico')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            TextField(
              controller: _problemaCtrl,
              decoration: const InputDecoration(labelText: 'Problema encontrado', border: OutlineInputBorder()),
              maxLines: 3,
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _causaCtrl,
              decoration: const InputDecoration(labelText: 'Causa probable', border: OutlineInputBorder()),
              maxLines: 2,
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _trabajoCtrl,
              decoration: const InputDecoration(labelText: 'Trabajo a realizar', border: OutlineInputBorder()),
              maxLines: 3,
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _recomendacionesCtrl,
              decoration: const InputDecoration(labelText: 'Recomendaciones', border: OutlineInputBorder()),
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
                    : const Text('Guardar diagnostico'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}