import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/placa.dart';
import 'detalle_ingreso_screen.dart';

/// Avisos del usuario: vehículos que ingresan, cambios de estado y trabajos asignados.
class NotificacionesScreen extends StatefulWidget {
  const NotificacionesScreen({super.key});

  @override
  State<NotificacionesScreen> createState() => _NotificacionesScreenState();
}

class _NotificacionesScreenState extends State<NotificacionesScreen> {
  List<dynamic> _lista = [];
  bool _cargando = true;

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
      final data = await ApiService.get('notificaciones');
      _lista = (data['notificaciones'] as List<dynamic>?) ?? [];
      // Se muestran con su estado "sin leer" y luego se marcan como leídas
      if ((data['no_leidas'] ?? 0) > 0) {
        try {
          await ApiService.post('notificaciones/leer', {});
        } catch (_) {}
      }
    } catch (e) {
      _aviso(e.toString().replaceAll('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  String _hace(dynamic iso) {
    final d = DateTime.tryParse((iso ?? '').toString());
    if (d == null) return '';
    final diff = DateTime.now().difference(d);
    if (diff.inMinutes < 1) return 'justo ahora';
    if (diff.inMinutes < 60) return 'hace ${diff.inMinutes} min';
    if (diff.inHours < 24) return 'hace ${diff.inHours} h';
    return 'hace ${diff.inDays} ${diff.inDays == 1 ? 'día' : 'días'}';
  }

  Color _color(String tipo) {
    switch (tipo) {
      case 'ingreso':
        return Marca.azul;
      case 'asignacion':
        return Marca.rojo;
      default:
        return const Color(0xFF7C3AED);
    }
  }

  String _etiqueta(String tipo) {
    switch (tipo) {
      case 'ingreso':
        return 'Ingreso';
      case 'asignacion':
        return 'Asignación';
      default:
        return 'Estado';
    }
  }

  Future<void> _abrir(dynamic n) async {
    final ingresoId = n['ingreso_id'];
    if (ingresoId == null) return;
    try {
      final ingreso = await ApiService.get('ingresos/$ingresoId');
      if (!mounted) return;
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => DetalleIngresoScreen(ingreso: Map<String, dynamic>.from(ingreso))),
      );
    } catch (e) {
      _aviso(e.toString().replaceAll('Exception: ', ''));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Notificaciones')),
      body: _cargando
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _cargar,
              child: _lista.isEmpty
                  ? ListView(
                      children: const [
                        SizedBox(height: 80),
                        Icon(Icons.notifications_none, size: 54, color: Marca.gris),
                        SizedBox(height: 10),
                        Center(
                          child: Text('No tienes notificaciones todavía',
                              style: TextStyle(fontWeight: FontWeight.w700, color: Marca.tinta)),
                        ),
                      ],
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: _lista.length,
                      itemBuilder: (_, i) {
                        final n = _lista[i];
                        final tipo = (n['tipo'] ?? '').toString();
                        final sinLeer = n['leida'] != true;
                        final mensaje = (n['mensaje'] ?? '').toString();

                        return Padding(
                          padding: const EdgeInsets.only(bottom: 8),
                          child: TarjetaTaller(
                            franja: _color(tipo),
                            onTap: n['ingreso_id'] == null ? null : () => _abrir(n),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    EtiquetaColor(texto: _etiqueta(tipo), color: _color(tipo)),
                                    if (sinLeer) ...[
                                      const SizedBox(width: 8),
                                      const EtiquetaColor(texto: 'Nueva', color: Marca.rojo),
                                    ],
                                    const Spacer(),
                                    Text(_hace(n['creada']), style: const TextStyle(fontSize: 12, color: Marca.gris)),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  '${n['titulo'] ?? ''}',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: sinLeer ? FontWeight.w800 : FontWeight.w600,
                                    color: Marca.tinta,
                                  ),
                                ),
                                if (mensaje.isNotEmpty) ...[
                                  const SizedBox(height: 2),
                                  Text(mensaje, style: const TextStyle(color: Marca.gris)),
                                ],
                              ],
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
