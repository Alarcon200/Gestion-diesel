import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/placa.dart';
import 'detalle_ingreso_screen.dart';
import 'fotos_ingreso_screen.dart';

/// Vehículos asignados al mecánico que inició sesión.
class MisTrabajosScreen extends StatefulWidget {
  const MisTrabajosScreen({super.key});

  @override
  State<MisTrabajosScreen> createState() => _MisTrabajosScreenState();
}

class _MisTrabajosScreenState extends State<MisTrabajosScreen> {
  List<dynamic> _trabajos = [];
  bool _cargando = true;
  int? _procesando;

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
      final data = await ApiService.get('mis-trabajos');
      _trabajos = (data['trabajos'] as List<dynamic>?) ?? [];
    } catch (e) {
      _aviso(e.toString().replaceAll('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  Future<void> _cambiarEstado(dynamic trabajo, String nuevo) async {
    if (_procesando != null) return;

    if (nuevo == 'finalizada') {
      final confirmar = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Text('Finalizar reparación'),
          content: const Text('¿Confirmas que terminaste el trabajo en este vehículo?'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
            TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Confirmar')),
          ],
        ),
      );
      if (confirmar != true) return;
    }

    setState(() => _procesando = trabajo['id'] as int);
    try {
      await ApiService.put('mis-trabajos/${trabajo['id']}/estado', {'estado': nuevo});
      _aviso(nuevo == 'en_reparacion' ? 'Marcado en reparación' : 'Reparación finalizada');
      await _cargar();
    } catch (e) {
      _aviso(e.toString().replaceAll('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _procesando = null);
    }
  }

  Future<void> _abrir(dynamic trabajo) async {
    try {
      final ingreso = await ApiService.get('ingresos/${trabajo['ingreso_id']}');
      if (!mounted) return;
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => DetalleIngresoScreen(ingreso: Map<String, dynamic>.from(ingreso)),
        ),
      ).then((_) => _cargar());
    } catch (e) {
      _aviso(e.toString().replaceAll('Exception: ', ''));
    }
  }

  String _etiqueta(String estado) {
    switch (estado) {
      case 'en_reparacion':
        return 'En reparación';
      case 'finalizada':
        return 'Finalizada';
      default:
        return 'Por iniciar';
    }
  }

  Color _color(String estado) {
    switch (estado) {
      case 'en_reparacion':
        return Marca.azul;
      case 'finalizada':
        return const Color(0xFF15803D);
      default:
        return Marca.gris;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mis trabajos')),
      body: _cargando
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _cargar,
              child: _trabajos.isEmpty
                  ? ListView(
                      children: const [
                        SizedBox(height: 80),
                        Center(child: Text('Todavía no tienes vehículos asignados.')),
                        SizedBox(height: 8),
                        Center(child: Text('Desliza hacia abajo para actualizar.', style: TextStyle(color: Marca.gris))),
                      ],
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: _trabajos.length,
                      itemBuilder: (_, i) => _tarjeta(_trabajos[i]),
                    ),
            ),
    );
  }

  void _abrirFotos(dynamic trabajo) {
    final ingresoId = trabajo['ingreso_id'];
    if (ingresoId == null) {
      _aviso('Esta orden no tiene un ingreso asociado.');
      return;
    }
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => FotosIngresoScreen(
          ingresoId: (ingresoId as num).toInt(),
          placa: '${trabajo['vehiculo']?['placa'] ?? ''}',
          puedeSubir: true,
        ),
      ),
    );
  }

  ButtonStyle _estiloSecundario() {
    return OutlinedButton.styleFrom(
      minimumSize: const Size(64, 46),
      foregroundColor: Marca.tinta,
      side: const BorderSide(color: bordeTarjeta),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
    );
  }

  Widget _tarjeta(dynamic t) {
    final estado = (t['estado'] ?? 'pendiente').toString();
    final color = _color(estado);
    final vehiculo = t['vehiculo'];
    final enProceso = _procesando == t['id'];
    final auto = '${vehiculo?['marca'] ?? ''} ${vehiculo?['modelo'] ?? ''}'.trim();

    Widget cargando(Color c) =>
        SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: c, strokeWidth: 3));

    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: TarjetaTaller(
        franja: color,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                PlacaChip(placa: '${vehiculo?['placa'] ?? '-'}', tamano: 22),
                const Spacer(),
                EtiquetaColor(texto: _etiqueta(estado), color: color),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              auto.isEmpty ? '${vehiculo?['cliente']?['nombre'] ?? '-'}' : auto,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700, color: Marca.tinta),
            ),
            const SizedBox(height: 2),
            Text(
              '${vehiculo?['cliente']?['nombre'] ?? '-'}  ·  ${t['numero_orden'] ?? ''}',
              style: const TextStyle(color: Marca.gris),
            ),
            if ((t['ingreso']?['motivo'] ?? '').toString().isNotEmpty) ...[
              const SizedBox(height: 6),
              Text('${t['ingreso']['motivo']}', style: const TextStyle(color: Marca.tinta)),
            ],
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _abrir(t),
                    style: _estiloSecundario(),
                    icon: const Icon(Icons.open_in_new, size: 18),
                    label: const Text('Abrir'),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _abrirFotos(t),
                    style: _estiloSecundario(),
                    icon: const Icon(Icons.photo_camera, size: 18),
                    label: const Text('Fotos'),
                  ),
                ),
              ],
            ),
            if (estado == 'pendiente') ...[
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: enProceso ? null : () => _cambiarEstado(t, 'en_reparacion'),
                  child: enProceso ? cargando(Colors.white) : const Text('Iniciar reparación'),
                ),
              ),
            ],
            if (estado == 'en_reparacion') ...[
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  onPressed: enProceso ? null : () => _cambiarEstado(t, 'finalizada'),
                  style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF15803D)),
                  child: enProceso ? cargando(Colors.white) : const Text('Finalizar'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
