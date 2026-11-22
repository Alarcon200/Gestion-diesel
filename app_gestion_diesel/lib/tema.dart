import 'package:flutter/material.dart';

/// Colores tomados del logo de I.S.A. y de la placa amarilla de carga.
class Marca {
  static const negro = Color(0xFF000000);
  static const azul = Color(0xFF2F66D6);
  static const rojo = Color(0xFFCF2027);
  static const placa = Color(0xFFF6C90E);
  static const fondo = Color(0xFFE9EBEE);
  static const tinta = Color(0xFF1C2330);
  static const gris = Color(0xFF6B7280);
}

/// Radio de las tarjetas y botones: casi recto, estilo placa / taller.
const double radioTarjeta = 4;
const Color bordeTarjeta = Color(0xFFD0D5DD);

ThemeData temaApp() {
  final base = ColorScheme.fromSeed(seedColor: Marca.azul, brightness: Brightness.light);

  return ThemeData(
    useMaterial3: true,
    colorScheme: base.copyWith(primary: Marca.azul, secondary: Marca.rojo),
    scaffoldBackgroundColor: Marca.fondo,
    appBarTheme: const AppBarTheme(
      backgroundColor: Marca.negro,
      foregroundColor: Colors.white,
      elevation: 0,
      centerTitle: false,
      titleTextStyle: TextStyle(
        color: Colors.white,
        fontSize: 20,
        fontWeight: FontWeight.w700,
        letterSpacing: 0.3,
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: Marca.azul,
        foregroundColor: Colors.white,
        minimumSize: const Size(64, 52),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
        textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
      ),
    ),
  );
}

/// Campo de texto con el estilo de la marca.
InputDecoration campo(String etiqueta, {IconData? icono}) {
  return InputDecoration(
    labelText: etiqueta,
    prefixIcon: icono == null ? null : Icon(icono),
    filled: true,
    fillColor: Colors.white,
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(6),
      borderSide: BorderSide.none,
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(6),
      borderSide: const BorderSide(color: Color(0xFFD5D9E0)),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(6),
      borderSide: const BorderSide(color: Marca.azul, width: 2),
    ),
  );
}
