import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'detalle_ingreso_screen.dart';

class IngresosListaScreen extends StatefulWidget {
  const IngresosListaScreen({super.key});

  @override
  State<IngresosListaScreen> createState() => _IngresosListaScreenState();
}

class _IngresosListaScreenState extends State<IngresosListaScreen> {
  List<dynamic> _todos = [];
  List<dynamic> _filtrados = [];
  bool _cargando = true;
  final _buscarCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  Future<void> _cargar() async {
    setState(() => _cargando = true);
    try {
      final data = await ApiService.get('ingresos');
      setState(() {
        _todos = data;
        _filtrados = data;
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  void _buscar(String texto) {
    final t = texto.toLowerCase();
    setState(() {
      _filtrados = _todos.where((i) {
        final placa = (i['vehiculo']?['placa'] ?? '').toString().toLowerCase();
        final cliente = (i['vehiculo']?['cliente']?['nombre'] ?? '').toString().toLowerCase();
        return placa.contains(t) || cliente.contains(t);
      }).toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Vehiculos en el taller')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: TextField(
              controller: _buscarCtrl,
              decoration: const InputDecoration(
                labelText: 'Buscar por placa o cliente',
                prefixIcon: Icon(Icons.search),
                border: OutlineInputBorder(),
              ),
              onChanged: _buscar,
            ),
          ),
          Expanded(
            child: _cargando
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _cargar,
                    child: _filtrados.isEmpty
                        ? const Center(child: Text('No hay vehiculos que coincidan'))
                        : ListView.builder(
                            itemCount: _filtrados.length,
                            itemBuilder: (_, i) {
                              final ing = _filtrados[i];
                              return Card(
                                margin: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                child: ListTile(
                                  title: Text('${ing['vehiculo']?['placa'] ?? '-'}'),
                                  subtitle: Text(
                                    '${ing['vehiculo']?['cliente']?['nombre'] ?? '-'}\n${ing['motivo'] ?? ''}',
                                  ),
                                  isThreeLine: true,
                                  trailing: const Icon(Icons.chevron_right),
                                  onTap: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(builder: (_) => DetalleIngresoScreen(ingreso: ing)),
                                    ).then((_) => _cargar());
                                  },
                                ),
                              );
                            },
                          ),
                  ),
          ),
        ],
      ),
    );
  }
}