import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/placa.dart';

/// Administrador y Jefe de taller: asignar (o cambiar) el mecánico de cada vehículo.
class AsignarTrabajosScreen extends StatefulWidget {
  const AsignarTrabajosScreen({super.key});

  @override
  State<AsignarTrabajosScreen> createState() => _AsignarTrabajosScreenState();
}

class _AsignarTrabajosScreenState extends State<AsignarTrabajosScreen> {
  List<dynamic> _ordenes = [];
  List<dynamic> _mecanicos = [];
  bool _cargando = true;
  bool _verAsignados = false;
  int? _guardando;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  void _aviso(String texto) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(texto)));
  }

  Future<void> _cargar() async {
    setState(() => _cargando = true);
    try {
      final ordenes = await ApiService.get('taller/ordenes');
      final mecanicos = await ApiService.get('taller/mecanicos');
      _ordenes = ordenes is List ? ordenes : [];
      _mecanicos = mecanicos is List ? mecanicos : [];
    } catch (e) {
      _aviso(e.toString().replaceAll('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  List<dynamic> get _sinAsignar => _ordenes.where((o) => o['mecanico_id'] == null).toList();
  List<dynamic> get _asignados => _ordenes.where((o) => o['mecanico_id'] != null).toList();

  String _nombreMecanico(dynamic orden) {
    final m = orden['mecanico'];
    if (m == null) return '';
    return '${m['nombre'] ?? ''} ${m['apellido'] ?? ''}'.trim();
  }

  Future<void> _elegirMecanico(dynamic orden) async {
    if (_mecanicos.isEmpty) {
      _aviso('No hay mecánicos activos registrados.');
      return;
    }

    final actual = orden['mecanico_id'];
    final placa = '${orden['vehiculo']?['placa'] ?? ''}';

    final elegido = await showModalBottomSheet<int>(
      context: context,
      isScrollControlled: true,
      builder: (ctx) => SafeArea(
        child: ConstrainedBox(
          constraints: BoxConstraints(maxHeight: MediaQuery.of(ctx).size.height * 0.75),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
                child: Row(
                  children: [
                    const Expanded(
                      child: Text('Asignar a un mecánico',
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Marca.tinta)),
                    ),
                    PlacaChip(placa: placa, tamano: 16),
                  ],
                ),
              ),
              const Divider(height: 1, color: bordeTarjeta),
              Flexible(
                child: ListView(
                  shrinkWrap: true,
                  children: [
                    for (final m in _mecanicos)
                      ListTile(
                        leading: Icon(
                          m['id'] == actual ? Icons.radio_button_checked : Icons.radio_button_unchecked,
                          color: m['id'] == actual ? Marca.azul : Marca.gris,
                        ),
                        title: Text('${m['nombre']}', style: const TextStyle(fontWeight: FontWeight.w700)),
                        subtitle: Text(
                          (m['trabajos_abiertos'] ?? 0) == 0
                              ? 'Sin trabajos abiertos'
                              : '${m['trabajos_abiertos']} ${m['trabajos_abiertos'] == 1 ? 'trabajo abierto' : 'trabajos abiertos'}',
                        ),
                        onTap: () => Navigator.pop(ctx, m['id'] as int),
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );

    if (elegido == null || elegido == actual) return;

    // Si ya lo está reparando otro mecánico, se confirma antes de quitárselo.
    if (actual != null && orden['estado'] == 'en_reparacion') {
      if (!mounted) return;
      final confirmar = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Text('Cambiar mecánico'),
          content: Text('${_nombreMecanico(orden)} ya está reparando este vehículo. ¿Pasar el trabajo a otro mecánico?'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
            TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Cambiar')),
          ],
        ),
      );
      if (confirmar != true) return;
    }

    setState(() => _guardando = orden['id'] as int);
    try {
      await ApiService.put('taller/ordenes/${orden['id']}/mecanico', {'mecanico_id': elegido});
      final nombre = _mecanicos.firstWhere((m) => m['id'] == elegido, orElse: () => {'nombre': 'el mecánico'})['nombre'];
      _aviso('Asignado a $nombre');
      await _cargar();
    } catch (e) {
      _aviso(e.toString().replaceAll('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _guardando = null);
    }
  }

  @override
  Widget build(BuildContext context) {
    final lista = _verAsignados ? _asignados : _sinAsignar;

    return Scaffold(
      appBar: AppBar(title: const Text('Asignar trabajos')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 12, 12, 8),
            child: Row(
              children: [
                Expanded(child: _pestana('Sin asignar (${_sinAsignar.length})', !_verAsignados, () => setState(() => _verAsignados = false))),
                const SizedBox(width: 8),
                Expanded(child: _pestana('Asignados (${_asignados.length})', _verAsignados, () => setState(() => _verAsignados = true))),
              ],
            ),
          ),
          Expanded(
            child: _cargando
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _cargar,
                    child: lista.isEmpty
                        ? ListView(
                            children: [
                              const SizedBox(height: 80),
                              Center(
                                child: Text(
                                  _verAsignados
                                      ? 'Todavía no hay trabajos asignados'
                                      : 'Todos los vehículos ya tienen mecánico',
                                  style: const TextStyle(fontWeight: FontWeight.w700, color: Marca.tinta),
                                ),
                              ),
                            ],
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.fromLTRB(12, 4, 12, 20),
                            itemCount: lista.length,
                            itemBuilder: (_, i) => _tarjeta(lista[i]),
                          ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _pestana(String texto, bool activa, VoidCallback onTap) {
    return Material(
      color: activa ? Marca.negro : Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radioTarjeta),
        side: BorderSide(color: activa ? Marca.negro : bordeTarjeta),
      ),
      child: InkWell(
        onTap: onTap,
        child: SizedBox(
          height: 44,
          child: Center(
            child: Text(
              texto,
              style: TextStyle(
                fontWeight: FontWeight.w700,
                color: activa ? Colors.white : Marca.tinta,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _tarjeta(dynamic o) {
    final v = o['vehiculo'];
    final nombreMecanico = _nombreMecanico(o);
    final tiene = o['mecanico_id'] != null;
    final guardando = _guardando == o['id'];
    final estado = (o['estado'] ?? 'pendiente').toString();
    final auto = '${v?['marca'] ?? ''} ${v?['modelo'] ?? ''}'.trim();
    final motivo = (o['ingreso']?['motivo'] ?? '').toString();

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: TarjetaTaller(
        franja: tiene ? Marca.azul : const Color(0xFFB7791F),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                PlacaChip(placa: '${v?['placa'] ?? '-'}', tamano: 20),
                const Spacer(),
                EtiquetaColor(
                  texto: estado == 'en_reparacion' ? 'En reparación' : 'Por iniciar',
                  color: estado == 'en_reparacion' ? Marca.azul : Marca.gris,
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              auto.isEmpty ? '${v?['cliente']?['nombre'] ?? '-'}' : auto,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Marca.tinta),
            ),
            const SizedBox(height: 2),
            Text('${v?['cliente']?['nombre'] ?? '-'}  ·  ${o['numero_orden'] ?? ''}',
                style: const TextStyle(color: Marca.gris)),
            if (motivo.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(motivo, style: const TextStyle(color: Marca.tinta)),
            ],
            const SizedBox(height: 10),
            Row(
              children: [
                Icon(Icons.engineering, size: 18, color: tiene ? Marca.tinta : const Color(0xFFB7791F)),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    tiene ? nombreMecanico : 'Sin mecánico asignado',
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      color: tiene ? Marca.tinta : const Color(0xFFB7791F),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: tiene
                  ? OutlinedButton(
                      onPressed: guardando ? null : () => _elegirMecanico(o),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: Marca.tinta,
                        side: const BorderSide(color: bordeTarjeta),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                      ),
                      child: guardando ? const _Cargando(color: Marca.tinta) : const Text('Cambiar mecánico'),
                    )
                  : ElevatedButton(
                      onPressed: guardando ? null : () => _elegirMecanico(o),
                      child: guardando ? const _Cargando(color: Colors.white) : const Text('Asignar mecánico'),
                    ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Cargando extends StatelessWidget {
  final Color color;
  const _Cargando({required this.color});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 20,
      height: 20,
      child: CircularProgressIndicator(color: color, strokeWidth: 3),
    );
  }
}
