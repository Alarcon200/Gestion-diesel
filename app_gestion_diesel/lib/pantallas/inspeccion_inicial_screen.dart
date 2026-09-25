import 'package:flutter/material.dart';

class InspeccionInicialScreen extends StatefulWidget {
  final String placa;
  final String vehiculo;

  const InspeccionInicialScreen({
    super.key, 
    required this.placa, 
    required this.vehiculo
  });

  @override
  State<InspeccionInicialScreen> createState() => _InspeccionInicialScreenState();
}

class _InspeccionInicialScreenState extends State<InspeccionInicialScreen> {
  String _estadoExterior = 'Bueno';
  String _estadoInterior = 'Bueno';
  String _estadoLlantas = 'Bueno';
  String _estadoMecanica = 'Bueno';

  final Color isaPrimaryBlue = const Color(0xFF1976D2);
  final Color isaAccentRed = const Color(0xFFD32F2F);

  // Modificamos la función para que reciba la "Zona" que se está reportando
  void _mostrarCuadroNovedad(BuildContext context, String zona) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true, 
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom, 
            left: 24,
            right: 24,
            top: 24,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min, 
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Novedad en: $zona', // Muestra la zona afectada (Ej: Exterior)
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: isaPrimaryBlue),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.grey),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              
              TextField(
                maxLines: 4,
                decoration: InputDecoration(
                  hintText: 'Describe el detalle encontrado en $zona...',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  filled: true,
                  fillColor: Colors.grey[50],
                ),
              ),
              const SizedBox(height: 20),

              const Text('Evidencia fotográfica', style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _botonFoto(Icons.camera_alt, 'Cámara'),
                  _botonFoto(Icons.photo_library, 'Galería'),
                ],
              ),
              
              const SizedBox(height: 32),

              SizedBox(
                width: double.infinity,
                height: 55,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context); 
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Novedad guardada para $zona')),
                    );
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isaAccentRed,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: const Text('Guardar Novedad', style: TextStyle(fontSize: 16, color: Colors.white, fontWeight: FontWeight.bold)),
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        );
      },
    );
  }

  Widget _botonFoto(IconData icono, String texto) {
    return InkWell(
      onTap: () {},
      child: Container(
        width: 120,
        padding: const EdgeInsets.symmetric(vertical: 16),
        decoration: BoxDecoration(
          color: Colors.grey[100],
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade300),
        ),
        child: Column(
          children: [
            Icon(icono, size: 32, color: isaPrimaryBlue),
            const SizedBox(height: 8),
            Text(texto, style: TextStyle(color: Colors.grey[800], fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: Text('Inspección inicial', style: TextStyle(color: isaPrimaryBlue, fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: Colors.white,
        iconTheme: IconThemeData(color: isaPrimaryBlue),
        elevation: 1,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(widget.placa.toUpperCase(), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 4),
                  Text(widget.vehiculo.toUpperCase(), style: TextStyle(fontSize: 16, color: Colors.grey[700])),
                ],
              ),
            ),
            
            const SizedBox(height: 24),
            
            const Text(
              'Estado del vehículo',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF333333)),
            ),
            const SizedBox(height: 16),

            _crearCategoriaInspeccion('Exterior', _estadoExterior, (valor) {
              setState(() => _estadoExterior = valor!);
              // Lógica mágica: Si toca Observación o Daño, abre el cuadro automáticamente
              if (valor == 'Observación' || valor == 'Daño') {
                _mostrarCuadroNovedad(context, 'Exterior');
              }
            }),
            _crearCategoriaInspeccion('Interior/Cabina', _estadoInterior, (valor) {
              setState(() => _estadoInterior = valor!);
              if (valor == 'Observación' || valor == 'Daño') {
                _mostrarCuadroNovedad(context, 'Interior/Cabina');
              }
            }),
            _crearCategoriaInspeccion('Llantas', _estadoLlantas, (valor) {
              setState(() => _estadoLlantas = valor!);
              if (valor == 'Observación' || valor == 'Daño') {
                _mostrarCuadroNovedad(context, 'Llantas');
              }
            }),
            _crearCategoriaInspeccion('Mecánica visible', _estadoMecanica, (valor) {
              setState(() => _estadoMecanica = valor!);
              if (valor == 'Observación' || valor == 'Daño') {
                _mostrarCuadroNovedad(context, 'Mecánica visible');
              }
            }),

            const SizedBox(height: 32),

            // Botón manual por si quieren agregar una novedad general extra
            SizedBox(
              width: double.infinity,
              height: 55,
              child: OutlinedButton(
                onPressed: () => _mostrarCuadroNovedad(context, 'General'),
                style: OutlinedButton.styleFrom(
                  side: BorderSide(color: isaAccentRed, width: 2),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: Text('Registrar novedad manual', style: TextStyle(fontSize: 16, color: isaAccentRed, fontWeight: FontWeight.bold)),
              ),
            ),
            
            const SizedBox(height: 12),

            SizedBox(
              width: double.infinity,
              height: 55,
              child: ElevatedButton(
                onPressed: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Yendo al Resumen Final...')),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: isaPrimaryBlue,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Continuar', style: TextStyle(fontSize: 16, color: Colors.white, fontWeight: FontWeight.bold, letterSpacing: 1.2)),
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _crearCategoriaInspeccion(String titulo, String valorActual, ValueChanged<String?> onChanged) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 20.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(titulo, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          _crearOpcionRadio('Bueno', Colors.green, valorActual, onChanged),
          _crearOpcionRadio('Observación', Colors.orange, valorActual, onChanged),
          _crearOpcionRadio('Daño', Colors.red, valorActual, onChanged),
        ],
      ),
    );
  }

  Widget _crearOpcionRadio(String titulo, Color color, String grupoValor, ValueChanged<String?> onChanged) {
    return SizedBox(
      height: 36, 
      child: Row(
        children: [
          Radio<String>(
            value: titulo,
            groupValue: grupoValor,
            activeColor: color,
            onChanged: onChanged,
          ),
          Text(titulo, style: const TextStyle(fontSize: 15)),
        ],
      ),
    );
  }
}