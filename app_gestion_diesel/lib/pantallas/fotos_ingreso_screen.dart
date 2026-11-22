import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../estados.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/placa.dart';

/// Fotos del vehículo (evidencia del estado en que llegó y de lo que se hizo).
/// Cada foto lleva una nota debajo. Las fotos no se pueden borrar.
class FotosIngresoScreen extends StatefulWidget {
  final int ingresoId;
  final String placa;
  final bool puedeSubir;

  const FotosIngresoScreen({
    super.key,
    required this.ingresoId,
    required this.placa,
    required this.puedeSubir,
  });

  @override
  State<FotosIngresoScreen> createState() => _FotosIngresoScreenState();
}

class _NotaResultado {
  final String nota;
  final bool otra;
  const _NotaResultado(this.nota, this.otra);
}

class _FotosIngresoScreenState extends State<FotosIngresoScreen> {
  List<dynamic> _fotos = [];
  bool _cargando = true;
  bool _subiendo = false;
  final _picker = ImagePicker();

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
      final data = await ApiService.get('ingresos/${widget.ingresoId}/fotos');
      _fotos = data is List ? data : [];
    } catch (e) {
      _aviso(e.toString().replaceAll('Exception: ', ''));
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  Future<void> _agregar() async {
    final origen = await showModalBottomSheet<ImageSource>(
      context: context,
      builder: (ctx) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.photo_camera),
              title: const Text('Tomar foto con la cámara'),
              onTap: () => Navigator.pop(ctx, ImageSource.camera),
            ),
            ListTile(
              leading: const Icon(Icons.photo_library_outlined),
              title: const Text('Elegir de la galería'),
              onTap: () => Navigator.pop(ctx, ImageSource.gallery),
            ),
          ],
        ),
      ),
    );
    if (origen == null) return;
    await _capturar(origen);
  }

  Future<void> _capturar(ImageSource origen) async {
    final x = await _picker.pickImage(source: origen, imageQuality: 70, maxWidth: 1600);
    if (x == null || !mounted) return;

    final archivo = File(x.path);
    final resultado = await showModalBottomSheet<_NotaResultado>(
      context: context,
      isScrollControlled: true,
      isDismissible: false,
      builder: (_) => _HojaNota(archivo: archivo),
    );
    if (resultado == null) return;

    await _subir(archivo, resultado.nota);
    if (resultado.otra && mounted) await _capturar(origen);
  }

  Future<void> _subir(File archivo, String nota) async {
    setState(() => _subiendo = true);
    try {
      await ApiService.subirFotoIngreso(widget.ingresoId, archivo, descripcion: nota);
      _aviso('Foto guardada');
      await _cargar();
    } catch (e) {
      _aviso('No se pudo subir la foto: ${e.toString().replaceAll('Exception: ', '')}');
    } finally {
      if (mounted) setState(() => _subiendo = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Fotos del vehículo')),
      floatingActionButton: widget.puedeSubir
          ? FloatingActionButton.extended(
              onPressed: _subiendo ? null : _agregar,
              backgroundColor: Marca.rojo,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
              icon: const Icon(Icons.add_a_photo),
              label: const Text('Agregar foto', style: TextStyle(fontWeight: FontWeight.w700)),
            )
          : null,
      body: Column(
        children: [
          Container(
            width: double.infinity,
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 12),
            child: Row(
              children: [
                PlacaChip(placa: widget.placa, tamano: 20),
                const Spacer(),
                Text(
                  _cargando ? '' : '${_fotos.length} ${_fotos.length == 1 ? 'foto' : 'fotos'}',
                  style: const TextStyle(fontWeight: FontWeight.w700, color: Marca.tinta),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: bordeTarjeta),
          if (_subiendo) const LinearProgressIndicator(minHeight: 3),
          Expanded(
            child: _cargando
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _cargar,
                    child: _fotos.isEmpty
                        ? ListView(
                            children: [
                              const SizedBox(height: 70),
                              const Icon(Icons.photo_camera_outlined, size: 54, color: Marca.gris),
                              const SizedBox(height: 10),
                              const Center(
                                child: Text('Todavía no hay fotos de este vehículo',
                                    style: TextStyle(fontWeight: FontWeight.w700, color: Marca.tinta)),
                              ),
                              if (widget.puedeSubir)
                                const Padding(
                                  padding: EdgeInsets.fromLTRB(32, 6, 32, 0),
                                  child: Text(
                                    'Toma fotos de los daños que ya trae el carro antes de empezar a trabajar.',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(color: Marca.gris),
                                  ),
                                ),
                            ],
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.fromLTRB(12, 12, 12, 96),
                            itemCount: _fotos.length,
                            itemBuilder: (_, i) => _tarjeta(_fotos[i]),
                          ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _tarjeta(dynamic f) {
    final nota = (f['descripcion'] ?? '').toString().trim();
    final autor = (f['autor'] ?? '').toString();

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(radioTarjeta),
          border: Border.all(color: bordeTarjeta),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            InkWell(
              onTap: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => _VisorFoto(url: f['url'].toString(), nota: nota)),
              ),
              child: Image.network(
                f['url'].toString(),
                height: 210,
                fit: BoxFit.cover,
                loadingBuilder: (ctx, child, progreso) => progreso == null
                    ? child
                    : const SizedBox(height: 210, child: Center(child: CircularProgressIndicator())),
                errorBuilder: (ctx, error, stack) => const SizedBox(
                  height: 210,
                  child: Center(child: Icon(Icons.broken_image_outlined, size: 44, color: Marca.gris)),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    nota.isEmpty ? 'Sin nota' : nota,
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: nota.isEmpty ? FontWeight.w400 : FontWeight.w600,
                      fontStyle: nota.isEmpty ? FontStyle.italic : FontStyle.normal,
                      color: nota.isEmpty ? Marca.gris : Marca.tinta,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      EtiquetaColor(
                        texto: '${f['origen'] ?? 'Ingreso'}',
                        color: f['origen'] == 'Inspección' ? Marca.rojo : Marca.azul,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          [if (autor.isNotEmpty) autor, fechaCorta(f['fecha_subida'])].join('  ·  '),
                          style: const TextStyle(fontSize: 12, color: Marca.gris),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Hoja para escribir la nota de la foto recién tomada.
class _HojaNota extends StatefulWidget {
  final File archivo;
  const _HojaNota({required this.archivo});

  @override
  State<_HojaNota> createState() => _HojaNotaState();
}

class _HojaNotaState extends State<_HojaNota> {
  final _notaCtrl = TextEditingController();

  @override
  void dispose() {
    _notaCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: EdgeInsets.fromLTRB(16, 14, 16, MediaQuery.of(context).viewInsets.bottom + 16),
        child: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('Nueva foto',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Marca.tinta)),
              const SizedBox(height: 10),
              ClipRRect(
                borderRadius: BorderRadius.circular(radioTarjeta),
                child: Image.file(widget.archivo, height: 220, fit: BoxFit.cover),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _notaCtrl,
                maxLength: 200,
                maxLines: 3,
                minLines: 2,
                textCapitalization: TextCapitalization.sentences,
                decoration: campo('Escribe una nota (ej. golpe en la puerta derecha)', icono: Icons.edit_note),
              ),
              const SizedBox(height: 6),
              ElevatedButton(
                onPressed: () => Navigator.pop(context, _NotaResultado(_notaCtrl.text.trim(), false)),
                child: const Text('Guardar foto'),
              ),
              const SizedBox(height: 8),
              OutlinedButton(
                onPressed: () => Navigator.pop(context, _NotaResultado(_notaCtrl.text.trim(), true)),
                style: OutlinedButton.styleFrom(
                  minimumSize: const Size(64, 48),
                  foregroundColor: Marca.tinta,
                  side: const BorderSide(color: bordeTarjeta),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
                ),
                child: const Text('Guardar y tomar otra'),
              ),
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text('Descartar', style: TextStyle(color: Marca.gris)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Foto a pantalla completa con zoom y su nota.
class _VisorFoto extends StatelessWidget {
  final String url;
  final String nota;
  const _VisorFoto({required this.url, required this.nota});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(title: const Text('Foto')),
      body: Column(
        children: [
          Expanded(
            child: InteractiveViewer(
              minScale: 1,
              maxScale: 5,
              child: Center(
                child: Image.network(
                  url,
                  fit: BoxFit.contain,
                  errorBuilder: (ctx, error, stack) =>
                      const Icon(Icons.broken_image_outlined, size: 60, color: Colors.white54),
                ),
              ),
            ),
          ),
          if (nota.isNotEmpty)
            Container(
              width: double.infinity,
              color: const Color(0xFF111111),
              padding: const EdgeInsets.fromLTRB(16, 14, 16, 22),
              child: Text(nota, style: const TextStyle(color: Colors.white, fontSize: 16)),
            ),
        ],
      ),
    );
  }
}
