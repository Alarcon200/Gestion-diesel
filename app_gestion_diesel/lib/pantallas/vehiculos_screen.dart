import 'package:flutter/material.dart';
import '../services/api_service.dart';

class VehiculosScreen extends StatefulWidget {
  const VehiculosScreen({super.key});

  @override
  State<VehiculosScreen> createState() => _VehiculosScreenState();
}

class _VehiculosScreenState extends State<VehiculosScreen> {
  List<dynamic> _vehiculos = [];
  List<dynamic> _clientes = [];
  bool _cargando = true;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    setState(() => _cargando = true);
    try {
      final vehiculos = await ApiService.get('vehiculos');
      final clientes = await ApiService.get('clientes');
      setState(() {
        _vehiculos = vehiculos;
        _clientes = clientes;
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  void _mostrarFormularioNuevo() {
    final placaCtrl = TextEditingController();
    final marcaCtrl = TextEditingController();
    final modeloCtrl = TextEditingController();
    int? clienteSeleccionado;

    if (_clientes.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Primero registra al menos un cliente')),
      );
      return;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Padding(
          padding: EdgeInsets.only(
            left: 16, right: 16, top: 16,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 16,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('Nuevo vehiculo', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 16),
              TextField(controller: placaCtrl, decoration: const InputDecoration(labelText: 'Placa')),
              TextField(controller: marcaCtrl, decoration: const InputDecoration(labelText: 'Marca')),
              TextField(controller: modeloCtrl, decoration: const InputDecoration(labelText: 'Modelo')),
              const SizedBox(height: 8),
              DropdownButtonFormField<int>(
                decoration: const InputDecoration(labelText: 'Cliente propietario'),
                items: _clientes
                    .map<DropdownMenuItem<int>>((c) => DropdownMenuItem(value: c['id'], child: Text(c['nombre'])))
                    .toList(),
                onChanged: (v) => setModalState(() => clienteSeleccionado = v),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () async {
                    if (clienteSeleccionado == null) {
                      ScaffoldMessenger.of(ctx).showSnackBar(
                        const SnackBar(content: Text('Selecciona un cliente')),
                      );
                      return;
                    }
                    try {
                      await ApiService.post('vehiculos', {
                        'placa': placaCtrl.text,
                        'marca': marcaCtrl.text,
                        'modelo': modeloCtrl.text,
                        'cliente_id': clienteSeleccionado,
                      });
                      if (!ctx.mounted) return;
                      Navigator.pop(ctx);
                      _cargar();
                    } catch (e) {
                      ScaffoldMessenger.of(ctx).showSnackBar(SnackBar(content: Text(e.toString())));
                    }
                  },
                  child: const Text('Guardar'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Vehiculos')),
      floatingActionButton: FloatingActionButton(
        onPressed: _mostrarFormularioNuevo,
        child: const Icon(Icons.add),
      ),
      body: _cargando
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _cargar,
              child: ListView.builder(
                itemCount: _vehiculos.length,
                itemBuilder: (_, i) {
                  final v = _vehiculos[i];
                  return ListTile(
                    title: Text('${v['placa']} - ${v['marca'] ?? ''} ${v['modelo'] ?? ''}'),
                    subtitle: Text(v['cliente']?['nombre'] ?? '-'),
                  );
                },
              ),
            ),
    );
  }
}