import 'package:flutter/material.dart';
import '../estados.dart';
import '../tema.dart';

/// Placa de vehículo de carga: fondo amarillo, letras negras y esquinas casi rectas.
class PlacaChip extends StatelessWidget {
  final String placa;
  final double tamano;

  const PlacaChip({super.key, required this.placa, this.tamano = 20});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.symmetric(horizontal: tamano * 0.6, vertical: tamano * 0.14),
      decoration: BoxDecoration(
        color: Marca.placa,
        borderRadius: BorderRadius.circular(tamano * 0.14),
        border: Border.all(color: Colors.black, width: 2),
      ),
      child: Text(
        placa.toUpperCase(),
        style: TextStyle(
          color: Colors.black,
          fontSize: tamano,
          fontWeight: FontWeight.w800,
          letterSpacing: tamano * 0.1,
        ),
      ),
    );
  }
}

/// Estado del vehículo como etiqueta rectangular con su color.
class EstadoPildora extends StatelessWidget {
  final String? estado;

  const EstadoPildora({super.key, required this.estado});

  @override
  Widget build(BuildContext context) {
    final info = infoEstado(estado);
    return EtiquetaColor(texto: info.etiqueta, color: info.color);
  }
}

/// Etiqueta rectangular de un color (estado, "EN EL TALLER", etc.).
class EtiquetaColor extends StatelessWidget {
  final String texto;
  final Color color;

  const EtiquetaColor({super.key, required this.texto, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withAlpha(30),
        borderRadius: BorderRadius.circular(3),
        border: Border.all(color: color.withAlpha(110)),
      ),
      child: Text(
        texto.toUpperCase(),
        style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w800, letterSpacing: 0.4),
      ),
    );
  }
}

/// Tarjeta blanca con borde fino y una franja de color a la izquierda.
class TarjetaTaller extends StatelessWidget {
  final Color franja;
  final Widget child;
  final VoidCallback? onTap;
  final EdgeInsets padding;

  const TarjetaTaller({
    super.key,
    required this.franja,
    required this.child,
    this.onTap,
    this.padding = const EdgeInsets.fromLTRB(14, 12, 14, 12),
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radioTarjeta),
        side: const BorderSide(color: bordeTarjeta),
      ),
      child: InkWell(
        onTap: onTap,
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Container(width: 6, color: franja),
              Expanded(child: Padding(padding: padding, child: child)),
            ],
          ),
        ),
      ),
    );
  }
}
