import 'package:flutter/material.dart';

class EstadoInfo {
  final String etiqueta;
  final Color color;
  const EstadoInfo(this.etiqueta, this.color);
}

/// Estados del vehículo, en el orden del flujo del taller.
const Map<String, EstadoInfo> estadosIngreso = {
  'en_espera_diagnostico': EstadoInfo('En espera de diagnóstico', Color(0xFF6B7280)),
  'en_reparacion': EstadoInfo('En reparación', Color(0xFF2F66D6)),
  'pendiente_cotizacion': EstadoInfo('Pendiente de cotización', Color(0xFFB7791F)),
  'pendiente_informe': EstadoInfo('Pendiente de informe', Color(0xFF7C3AED)),
  'pendiente_prefactura': EstadoInfo('Pendiente de prefactura', Color(0xFFDB2777)),
  'orden_enviada': EstadoInfo('Orden enviada', Color(0xFF4338CA)),
  'salio_taller': EstadoInfo('Salió del taller', Color(0xFF15803D)),
};

EstadoInfo infoEstado(String? clave) {
  return estadosIngreso[clave] ?? EstadoInfo(clave ?? '-', const Color(0xFF6B7280));
}

/// Administrador, Jefe de taller y Contabilidad (secretaria) cambian el estado.
bool puedeCambiarEstado(String rol) {
  return rol == 'Administrador' || rol == 'Jefe de taller' || rol == 'Contabilidad';
}

/// Administrador, Jefe de taller y Mecánico registran diagnóstico, reparación y prueba.
bool puedeOperar(String rol) {
  return rol == 'Administrador' || rol == 'Jefe de taller' || rol == 'Mecánico';
}

/// Número ST de la orden del ingreso, si ya fue asignado.
String? numeroST(dynamic ingreso) {
  final ordenes = ingreso is Map ? ingreso['ordenes'] : null;
  if (ordenes is List) {
    for (final o in ordenes) {
      final n = (o['numero_orden'] ?? '').toString();
      if (n.startsWith('ST')) return n;
    }
  }
  return null;
}

String fechaCorta(dynamic valor) {
  final d = DateTime.tryParse((valor ?? '').toString());
  if (d == null) return '-';
  final l = d.toLocal();
  String dos(int n) => n.toString().padLeft(2, '0');
  return '${dos(l.day)}/${dos(l.month)}/${l.year} ${dos(l.hour)}:${dos(l.minute)}';
}
