import 'package:flutter/material.dart';
import '../estados.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/placa.dart';
import 'detalle_ingreso_screen.dart';

class IngresosListaScreen extends StatefulWidget {
  const IngresosListaScreen({super.key});

  @override
  State<IngresosListaScreen> createState() => _IngresosListaScreenState();
}

class _IngresosListaScreenState extends State<IngresosListaScreen> {
  List<dynamic> _todos = [];
  List<dynamic> _filtrados = [];
  bool _cargando = true;
  final _buscarCtrl = TextEditingController();

  // null = todos los que siguen en el taller; o una clave de estado
  String? _filtro;

  @override
  void initState() {
    super.initState();
    _cargar();
  }

  @override
  void dispose() {
    _buscarCtrl.dispose();
    super.dispose();
  }

  Future<void> _cargar() async {
    setState(() => _cargando = true);
    try {
      final data = await ApiService.get('ingresos');
      _todos = data;
      _aplicar();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceAll('Exception: ', ''))),
      );
    } finally {
      if (mounted) setState(() => _cargando = false);
    }
  }

  void _aplicar() {
    final t = _buscarCtrl.text.toLowerCase();
    setState(() {
      _filtrados = _todos.where((i) {
        final estado = (i['estado'] ?? '').toString();

        if (_filtro == 'salio_taller') {
          if (estado != 'salio_taller') return false;
        } else {
          if (estado == 'salio_taller') return false;
          if (_filtro != null && estado != _filtro) return false;
        }

        final placa = (i['vehiculo']?['placa'] ?? '').toString().toLowerCase();
        final cliente = (i['vehiculo']?['cliente']?['nombre'] ?? '').toString().toLowerCase();
        return placa.contains(t) || cliente.contains(t);
      }).toList();
    });
  }

  int _contar(String? clave) {
    return _todos.where((i) {
      final estado = (i['estado'] ?? '').toString();
      if (clave == null) return estado != 'salio_taller';
      return estado == clave;
    }).length;
  }

  Widget _filtroChip(String? clave, String etiqueta) {
    final activo = _filtro == clave;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: ChoiceChip(
        label: Text('$etiqueta (${_contar(clave)})'),
        selected: activo,
        showCheckmark: false,
        selectedColor: Marca.negro,
        backgroundColor: Colors.white,
        side: BorderSide(color: activo ? Marca.negro : bordeTarjeta),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(radioTarjeta)),
        labelStyle: TextStyle(
          color: activo ? Colors.white : Marca.tinta,
          fontWeight: FontWeight.w700,
          fontSize: 13,
        ),
        onSelected: (_) {
          _filtro = clave;
          _aplicar();
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Vehículos en el taller')),
      body: Column(
        children: [
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(12, 12, 12, 10),
            child: Column(
              children: [
                TextField(
                  controller: _buscarCtrl,
                  decoration: campo('Buscar por placa o cliente', icono: Icons.search),
                  onChanged: (_) => _aplicar(),
                ),
                const SizedBox(height: 10),
                SizedBox(
                  height: 40,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    children: [
                      _filtroChip(null, 'En el taller'),
                      for (final e in estadosIngreso.entries) _filtroChip(e.key, e.value.etiqueta),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: bordeTarjeta),
          Expanded(
            child: _cargando
                ? const Center(child: CircularProgressIndicator())
                : RefreshIndicator(
                    onRefresh: _cargar,
                    child: _filtrados.isEmpty
                        ? ListView(
                            children: const [
                              SizedBox(height: 80),
                              Center(child: Text('No hay vehículos con ese filtro')),
                            ],
                          )
                        : ListView.builder(
                            padding: const EdgeInsets.fromLTRB(12, 12, 12, 16),
                            itemCount: _filtrados.length,
                            itemBuilder: (_, i) => _tarjeta(_filtrados[i]),
                          ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _tarjeta(dynamic ing) {
    final st = numeroST(ing);
    final info = infoEstado(ing['estado']?.toString());
    final vehiculo = ing['vehiculo'];
    final auto = '${vehiculo?['marca'] ?? ''} ${vehiculo?['modelo'] ?? ''}'.trim();

    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: TarjetaTaller(
        franja: info.color,
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => DetalleIngresoScreen(ingreso: Map<String, dynamic>.from(ing))),
          ).then((_) => _cargar());
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                PlacaChip(placa: '${vehiculo?['placa'] ?? '-'}', tamano: 20),
                const SizedBox(width: 10),
                if (auto.isNotEmpty)
                  Expanded(
                    child: Text(auto,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(fontWeight: FontWeight.w600, color: Marca.gris)),
                  )
                else
                  const Spacer(),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              '${vehiculo?['cliente']?['nombre'] ?? '-'}',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Marca.tinta),
            ),
            if ((ing['motivo'] ?? '').toString().isNotEmpty) ...[
              const SizedBox(height: 2),
              Text('${ing['motivo']}',
                  maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Marca.gris)),
            ],
            const SizedBox(height: 10),
            const Divider(height: 1, color: Color(0xFFE4E7EC)),
            const SizedBox(height: 8),
            Row(
              children: [
                EtiquetaColor(texto: info.etiqueta, color: info.color),
                const Spacer(),
                if (st != null)
                  Text(st, style: const TextStyle(fontWeight: FontWeight.w800, color: Marca.tinta))
                else
                  const Text('SIN ST',
                      style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12, color: Color(0xFFB7791F))),
              ],
            ),
            const SizedBox(height: 4),
            Text('Ingresó ${fechaCorta(ing['fecha_ingreso'])}',
                style: const TextStyle(fontSize: 12, color: Marca.gris)),
          ],
        ),
      ),
    );
  }
}
