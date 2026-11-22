import 'package:flutter/material.dart';
import '../estados.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/placa.dart';
import 'diagnostico_screen.dart';
import 'reparacion_screen.dart';
import 'prueba_screen.dart';
import 'fotos_ingreso_screen.dart';

class DetalleIngresoScreen extends StatefulWidget {
  final Map<String, dynamic> ingreso;
  const DetalleIngresoScreen({super.key, required this.ingreso});

  @override
  State<DetalleIngresoScreen> createState() => _DetalleIngresoScreenState();
}

class _DetalleIngresoScreenState extends State<DetalleIngresoScreen> {
  bool _generandoInforme = false;
  bool _guardandoEstado = false;
  late String _estado;

  @override
  void initState() {
    super.initState();
    _estado = (widget.ingreso['estado'] ?? '').toString();
  }

  Future<void> _cambiarEstado(String nuevo) async {
    if (nuevo == _estado || _guardandoEstado) return;

    if (nuevo == 'salio_taller') {
      final confirmar = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          title: const Text('Registrar salida'),
          content: const Text('¿Confirmas que este vehículo salió del taller?'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancelar')),
            TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('Confirmar')),
          ],
        ),
      );
      if (confirmar != true) return;
    }

    setState(() => _guardandoEstado = true);
    try {
      await ApiService.put('ingresos/${widget.ingreso['id']}/estado', {'estado': nuevo});
      if (!mounted) return;
      setState(() => _estado = nuevo);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Estado actualizado: ${infoEstado(nuevo).etiqueta}')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceAll('Exception: ', ''))),
      );
    } finally {
      if (mounted) setState(() => _guardandoEstado = false);
    }
  }

  Future<void> _generarInforme() async {
    setState(() => _generandoInforme = true);
    try {
      await ApiService.post('informes-servicio', {
        'ingreso_id': widget.ingreso['id'],
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Informe generado correctamente')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _generandoInforme = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final ing = widget.ingreso;
    final vehiculo = ing['vehiculo'];
    final rol = ApiService.rol;
    final st = numeroST(ing);

    return Scaffold(
      appBar: AppBar(title: const Text('Detalle del vehículo')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Datos del vehículo
          TarjetaTaller(
            franja: infoEstado(_estado).color,
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    PlacaChip(placa: '${vehiculo?['placa'] ?? '-'}', tamano: 26),
                    const Spacer(),
                    EstadoPildora(estado: _estado),
                  ],
                ),
                const SizedBox(height: 14),
                Text(
                  '${vehiculo?['marca'] ?? ''} ${vehiculo?['modelo'] ?? ''}'.trim(),
                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Marca.tinta),
                ),
                const SizedBox(height: 10),
                _dato('Cliente', '${vehiculo?['cliente']?['nombre'] ?? '-'}'),
                _dato('Motivo', '${ing['motivo'] ?? '-'}'),
                if ((ing['conductor'] ?? '').toString().isNotEmpty)
                  _dato('Conductor', '${ing['conductor']}'),
                _dato('ST', st ?? 'Sin asignar'),
                _dato('Ingresó', fechaCorta(ing['fecha_ingreso'])),
              ],
            ),
          ),

          // Evidencia: todos pueden ver las fotos; suben Administrador, Jefe de taller y Mecánico
          const SizedBox(height: 22),
          const Text('Evidencia',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Marca.tinta)),
          const SizedBox(height: 10),
          _botonAccion(
            puedeOperar(rol) ? 'Fotos del vehículo (ver y agregar)' : 'Fotos del vehículo',
            Icons.photo_camera,
            () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => FotosIngresoScreen(
                    ingresoId: (ing['id'] as num).toInt(),
                    placa: '${vehiculo?['placa'] ?? ''}',
                    puedeSubir: puedeOperar(rol),
                  ),
                ),
              );
            },
          ),

          // Cambio de estado: Administrador, Jefe de taller y Contabilidad
          if (puedeCambiarEstado(rol)) ...[
            const SizedBox(height: 22),
            const Text('Estado del vehículo',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Marca.tinta)),
            const SizedBox(height: 10),
            Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(radioTarjeta),
                border: Border.all(color: bordeTarjeta),
              ),
              child: Column(
                children: [
                  for (final e in estadosIngreso.entries)
                    ListTile(
                      enabled: !_guardandoEstado,
                      onTap: () => _cambiarEstado(e.key),
                      leading: Icon(
                        _estado == e.key ? Icons.radio_button_checked : Icons.radio_button_unchecked,
                        color: _estado == e.key ? e.value.color : Marca.gris,
                      ),
                      title: Text(
                        e.value.etiqueta,
                        style: TextStyle(
                          fontWeight: _estado == e.key ? FontWeight.w800 : FontWeight.w500,
                          color: Marca.tinta,
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ],

          // Trabajo del taller: Administrador, Jefe de taller y Mecánico
          if (puedeOperar(rol)) ...[
            const SizedBox(height: 22),
            const Text('Trabajo en el vehículo',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Marca.tinta)),
            const SizedBox(height: 10),
            _botonAccion('Registrar diagnóstico', Icons.assignment, () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => DiagnosticoScreen(ingresoId: ing['id'])),
              );
            }),
            _botonAccion('Registrar reparación (con fotos)', Icons.build, () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => ReparacionScreen(ingresoId: ing['id'])),
              );
            }),
            _botonAccion('Registrar prueba de calidad', Icons.check_circle, () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => PruebaScreen(ingresoId: ing['id'])),
              );
            }),
          ],

          // Informe: Administrador, Jefe de taller y Contabilidad (el mecánico no)
          if (puedeCambiarEstado(rol)) ...[
            const SizedBox(height: 22),
            ElevatedButton.icon(
              icon: const Icon(Icons.picture_as_pdf),
              label: _generandoInforme
                  ? const SizedBox(
                      width: 22,
                      height: 22,
                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 3),
                    )
                  : const Text('Generar informe'),
              onPressed: _generandoInforme ? null : _generarInforme,
            ),
          ],
        ],
      ),
    );
  }

  Widget _dato(String etiqueta, String valor) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 82,
            child: Text(etiqueta, style: const TextStyle(color: Marca.gris)),
          ),
          Expanded(
            child: Text(valor, style: const TextStyle(fontWeight: FontWeight.w600, color: Marca.tinta)),
          ),
        ],
      ),
    );
  }

  Widget _botonAccion(String texto, IconData icono, VoidCallback onTap) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: TarjetaTaller(
        franja: Marca.azul,
        onTap: onTap,
        padding: const EdgeInsets.fromLTRB(14, 14, 12, 14),
        child: Row(
          children: [
            Icon(icono, color: Marca.azul),
            const SizedBox(width: 14),
            Expanded(
              child: Text(texto,
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: Marca.tinta)),
            ),
            const Icon(Icons.chevron_right, color: Marca.gris),
          ],
        ),
      ),
    );
  }
}
