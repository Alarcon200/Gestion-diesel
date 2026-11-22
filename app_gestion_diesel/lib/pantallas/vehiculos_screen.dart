import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/placa.dart';

/// Catálogo de vehículos registrados, con su cliente y si están en el taller.
class VehiculosScreen extends StatefulWidget {
  const VehiculosScreen({super.key});

  @override
  State<VehiculosScreen> createState() => _VehiculosScreenState();
}

class _VehiculosScreenState extends State<VehiculosScreen> {
  List<dynamic> _vehiculos = [];
  List<dynamic> _clientes = [];
  Set<int> _enTaller = {};
  bool _cargando = true;
  final _buscarCtrl = TextEditingController();
  String _texto = '';

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  @override
  void dispose() {
    _buscarCtrl.dispose();
    super.dispose();
  }

  void _aviso(String texto) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(texto)));
  }

  Future<void> _cargar() async {
    setState(() => _cargando = true);
    try {
      final vehiculos = await ApiService.get('vehiculos');
      final clientes = await ApiService.get('clientes');
      _vehiculos = vehiculos is List ? vehiculos : [];
      _clientes = clientes is List ? clientes : [];
    } catch (e) {
      _aviso(e.toString().replaceAll('Exception: ', ''));
    }

    // Marca cuáles vehículos están ahora mismo en el taller (si falla, solo no se muestra la marca)
    try {
      final ingresos = await ApiService.get('ingresos');
      final ids = <int>{};
      for (final i in (ingresos is List ? ingresos : [])) {
        if (i['estado'] != 'salio_taller' && i['vehiculo_id'] != null) {
          ids.add((i['vehiculo_id'] as num).toInt());
        }
      }
      _enTaller = ids;
    } catch (_) {
      _enTaller = {};
    }

    if (mounted) setState(() => _cargando = false);
  }

  List<dynamic> get _filtrados {
    final t = _texto.toLowerCase();
    if (t.isEmpty) return _vehiculos;
    return _vehiculos.where((v) {
      final texto = '${v['placa'] ?? ''} ${v['marca'] ?? ''} ${v['modelo'] ?? ''} ${v['cliente']?['nombre'] ?? ''}'
          .toLowerCase();
      return texto.contains(t);
    }).toList();
  }

  void _mostrarFormularioNuevo() {
    if (_clientes.isEmpty) {
      _aviso('Primero registra al menos un cliente');
      return;
    }

    final placaCtrl = TextEditingController();
    final marcaCtrl = TextEditingController();
    final modeloCtrl = TextEditingController();
    final anioCtrl = TextEditingController();
    int? clienteSeleccionado;
    bool guardando = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => SafeArea(
          child: Padding(
            padding: EdgeInsets.only(
              left: 16,
              right: 16,
              top: 16,
              bottom: MediaQuery.of(ctx).viewInsets.bottom + 16,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text('Nuevo vehículo',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Marca.tinta)),
                  const SizedBox(height: 14),
                  TextField(
                    controller: placaCtrl,
                    textCapitalization: TextCapitalization.characters,
                    decoration: campo('Placa', icono: Icons.pin),
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(child: TextField(controller: marcaCtrl, decoration: campo('Marca'))),
                      const SizedBox(width: 10),
                      Expanded(child: TextField(controller: modeloCtrl, decoration: campo('Modelo'))),
                    ],
                  ),
                  const SizedBox(height: 10),
                  TextField(
                    controller: anioCtrl,
                    keyboardType: TextInputType.number,
                    decoration: campo('Año (opcional)', icono: Icons.calendar_today),
                  ),
                  const SizedBox(height: 10),
                  DropdownButtonFormField<int>(
                    isExpanded: true,
                    decoration: campo('Cliente propietario', icono: Icons.business),
                    items: _clientes
                        .map<DropdownMenuItem<int>>(
                          (c) => DropdownMenuItem(
                            value: c['id'] as int,
                            child: Text('${c['nombre']}', overflow: TextOverflow.ellipsis),
                          ),
                        )
                        .toList(),
                    onChanged: (v) => setModalState(() => clienteSeleccionado = v),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: guardando
                        ? null
                        : () async {
                            if (placaCtrl.text.trim().isEmpty) {
                              ScaffoldMessenger.of(ctx).showSnackBar(
                                const SnackBar(content: Text('Escribe la placa')),
                              );
                              return;
                            }
                            if (clienteSeleccionado == null) {
                              ScaffoldMessenger.of(ctx).showSnackBar(
                                const SnackBar(content: Text('Selecciona un cliente')),
                              );
                              return;
                            }
                            setModalState(() => guardando = true);
                            try {
                              await ApiService.post('vehiculos', {
                                'placa': placaCtrl.text.trim().toUpperCase(),
                                'marca': marcaCtrl.text.trim(),
                                'modelo': modeloCtrl.text.trim(),
                                if (int.tryParse(anioCtrl.text.trim()) != null)
                                  'anio': int.parse(anioCtrl.text.trim()),
                                'cliente_id': clienteSeleccionado,
                              });
                              if (!ctx.mounted) return;
                              Navigator.pop(ctx);
                              _cargar();
                            } catch (e) {
                              if (!ctx.mounted) return;
                              setModalState(() => guardando = false);
                              ScaffoldMessenger.of(ctx).showSnackBar(
                                SnackBar(content: Text(e.toString().replaceAll('Exception: ', ''))),
                              );
                            }
                          },
                    child: guardando
                        ? const SizedBox(
                            width: 22,
                            height: 22,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 3),
                          )
                        : const Text('Guardar vehículo'),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final lista = _filtrados;

    return Scaffold(
      appBar: AppBar(title: const Text('Catálogo de vehículos')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _mostrarFormularioNuevo,
        backgroundColor: Marca.rojo,
        foregroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
        icon: const Icon(Icons.add),
        label: const Text('Nuevo vehículo', style: TextStyle(fontWeight: FontWeight.w700)),
      ),
      body: Column(
        children: [
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(12, 12, 12, 10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                TextField(
                  controller: _buscarCtrl,
                  decoration: campo('Buscar por placa, marca o cliente', icono: Icons.search),
                  onChanged: (v) => setState(() => _texto = v),
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Text(
                      '${lista.length} ${lista.length == 1 ? 'vehículo' : 'vehículos'}',
                      style: const TextStyle(fontWeight: FontWeight.w800, color: Marca.tinta),
                    ),
                    const SizedBox(width: 10),
                    if (_enTaller.isNotEmpty)
                      Text('${_enTaller.length} en el taller',
                          style: const TextStyle(fontWeight: FontWeight.w700, color: Marca.azul)),
                  ],
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: bordeTarjeta),
          Expanded(
            child: _cargando
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _cargar,
                    child: lista.isEmpty
                        ? ListView(
                            children: const [
                              SizedBox(height: 80),
                              Center(child: Text('No se encontraron vehículos')),
                            ],
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.fromLTRB(12, 12, 12, 96),
                            itemCount: lista.length,
                            itemBuilder: (_, i) => _tarjeta(lista[i]),
                          ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _tarjeta(dynamic v) {
    final enTaller = v['id'] != null && _enTaller.contains((v['id'] as num).toInt());
    final auto = '${v['marca'] ?? ''} ${v['modelo'] ?? ''}'.trim();
    final anio = (v['anio'] ?? '').toString();

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: TarjetaTaller(
        franja: enTaller ? Marca.azul : const Color(0xFFB8BEC8),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                PlacaChip(placa: '${v['placa'] ?? '-'}', tamano: 20),
                const Spacer(),
                if (enTaller) const EtiquetaColor(texto: 'En el taller', color: Marca.azul),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              auto.isEmpty ? 'Sin marca ni modelo' : (anio.isEmpty ? auto : '$auto  ·  $anio'),
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                color: auto.isEmpty ? Marca.gris : Marca.tinta,
              ),
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.business, size: 16, color: Marca.gris),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    '${v['cliente']?['nombre'] ?? '-'}',
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: Marca.gris),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
