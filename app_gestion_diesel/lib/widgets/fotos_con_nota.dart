import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../tema.dart';

/// Foto tomada en el celular que aún no se sube, con su nota.
class FotoPendiente {
  final File archivo;
  final TextEditingController nota = TextEditingController();

  FotoPendiente(this.archivo);

  String get texto => nota.text.trim();

  void liberar() => nota.dispose();
}

/// Lista de fotos con un campo para escribir la nota debajo de cada una.
/// Se usa en la inspección de ingreso y en la reparación.
class FotosConNota extends StatefulWidget {
  final List<FotoPendiente> fotos;

  const FotosConNota({super.key, required this.fotos});

  @override
  State<FotosConNota> createState() => _FotosConNotaState();
}

class _FotosConNotaState extends State<FotosConNota> {
  Future<void> _agregar(ImageSource origen) async {
    final picker = ImagePicker();
    final foto = await picker.pickImage(source: origen, imageQuality: 70, maxWidth: 1600);
    if (foto != null && mounted) {
      setState(() => widget.fotos.add(FotoPendiente(File(foto.path))));
    }
  }

  void _quitar(FotoPendiente f) {
    setState(() => widget.fotos.remove(f));
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        for (final f in widget.fotos)
          Container(
            margin: const EdgeInsets.only(bottom: 10),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(radioTarjeta),
              border: Border.all(color: bordeTarjeta),
            ),
            clipBehavior: Clip.antiAlias,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Stack(
                  children: [
                    Image.file(f.archivo, height: 190, width: double.infinity, fit: BoxFit.cover),
                    Positioned(
                      top: 6,
                      right: 6,
                      child: Material(
                        color: Colors.black54,
                        borderRadius: BorderRadius.circular(4),
                        child: InkWell(
                          borderRadius: BorderRadius.circular(4),
                          onTap: () => _quitar(f),
                          child: const Padding(
                            padding: EdgeInsets.all(6),
                            child: Icon(Icons.delete_outline, color: Colors.white, size: 20),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                Padding(
                  padding: const EdgeInsets.all(8),
                  child: TextField(
                    controller: f.nota,
                    maxLength: 200,
                    maxLines: 2,
                    minLines: 1,
                    textCapitalization: TextCapitalization.sentences,
                    decoration: campo('Nota de la foto (ej. golpe en el guardabarros)', icono: Icons.edit_note),
                  ),
                ),
              ],
            ),
          ),
        Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () => _agregar(ImageSource.camera),
                icon: const Icon(Icons.photo_camera),
                label: const Text('Tomar foto'),
                style: _estiloBoton(),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: OutlinedButton.icon(
                onPressed: () => _agregar(ImageSource.gallery),
                icon: const Icon(Icons.photo_library_outlined),
                label: const Text('Galería'),
                style: _estiloBoton(),
              ),
            ),
          ],
        ),
      ],
    );
  }

  ButtonStyle _estiloBoton() {
    return OutlinedButton.styleFrom(
      minimumSize: const Size(64, 48),
      foregroundColor: Marca.tinta,
      backgroundColor: Colors.white,
      side: const BorderSide(color: bordeTarjeta),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(6)),
    );
  }
}
