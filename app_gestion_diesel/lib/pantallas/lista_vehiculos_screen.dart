import 'package:flutter/material.dart';

class ListaVehiculosScreen extends StatelessWidget {
  const ListaVehiculosScreen({super.key});

  // Lista 1: Vehículos actualmente en el taller
  final List<Map<String, dynamic>> vehiculosActivos = const [
    {
      'placa': 'SJW-982', 'vehiculo': 'Kenworth T800', 'cliente': 'Transportes del Valle',
      'fecha_ingreso': '23 Sept 2026', 'estado': 'En Proceso', 
      'mecanico': 'Carlos Gómez', 'colorEstado': Color(0xFF1976D2) // Azul I.S.A.
    },
    {
      'placa': 'TNV-415', 'vehiculo': 'Freightliner Cascadia', 'cliente': 'Logística Sur',
      'fecha_ingreso': '21 Sept 2026', 'estado': 'Wait Repuestos', 
      'mecanico': 'Sin asignar', 'colorEstado': Color(0xFFD32F2F) // Rojo Alerta
    },
  ];

  // Lista 2: Vehículos que ya fueron entregados (Historial)
  final List<Map<String, dynamic>> vehiculosHistorial = const [
    {
      'placa': 'XMC-771', 'vehiculo': 'International ProStar', 'cliente': 'Independiente',
      'fecha_ingreso': '10 Sept 2026', 'fecha_salida': '15 Sept 2026',
      'estado': 'Entregado', 'mecanico': 'Luis Martínez', 'colorEstado': Colors.grey
    },
    {
      'placa': 'RTP-002', 'vehiculo': 'Mack Anthem', 'cliente': 'Constructor S.A.',
      'fecha_ingreso': '01 Sept 2026', 'fecha_salida': '05 Sept 2026',
      'estado': 'Entregado', 'mecanico': 'Carlos Gómez', 'colorEstado': Colors.grey
    },
  ];

  @override
  Widget build(BuildContext context) {
    // DefaultTabController es la magia que hace funcionar las pestañas
    return DefaultTabController(
      length: 2, // Número de pestañas
      child: Scaffold(
        backgroundColor: Colors.grey[100],
        appBar: AppBar(
          title: const Text('Vehículos', style: TextStyle(fontWeight: FontWeight.bold)),
          backgroundColor: Colors.white,
          foregroundColor: const Color(0xFF333333),
          elevation: 0,
          // Aquí definimos las pestañas
          bottom: const TabBar(
            labelColor: Color(0xFF1976D2), // Azul al estar seleccionado
            unselectedLabelColor: Colors.grey,
            indicatorColor: Color(0xFF1976D2),
            indicatorWeight: 3,
            tabs: [
              Tab(icon: Icon(Icons.build_circle), text: 'En Taller'),
              Tab(icon: Icon(Icons.history), text: 'Historial'),
            ],
          ),
        ),
        // TabBarView muestra el contenido dependiendo de la pestaña seleccionada
        body: TabBarView(
          children: [
            // Contenido de la pestaña 1 (Activos)
            _construirLista(vehiculosActivos, esHistorial: false),
            
            // Contenido de la pestaña 2 (Historial)
            _construirLista(vehiculosHistorial, esHistorial: true),
          ],
        ),
      ),
    );
  }

  // Función constructora para no repetir código en ambas listas
  Widget _construirLista(List<Map<String, dynamic>> lista, {required bool esHistorial}) {
    return ListView.builder(
      padding: const EdgeInsets.all(16.0),
      itemCount: lista.length,
      itemBuilder: (context, index) {
        final vehiculo = lista[index];
        
        return Card(
          margin: const EdgeInsets.only(bottom: 16.0),
          elevation: 1,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(color: Colors.grey.shade200),
          ),
          color: Colors.white,
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Encabezado: Placa y Etiqueta
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      vehiculo['placa'],
                      style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Color(0xFF333333)),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: vehiculo['colorEstado'].withOpacity(0.1),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: vehiculo['colorEstado']),
                      ),
                      child: Text(
                        vehiculo['estado'],
                        style: TextStyle(color: vehiculo['colorEstado'], fontWeight: FontWeight.bold, fontSize: 12),
                      ),
                    ),
                  ],
                ),
                const Divider(height: 24, thickness: 1),
                
                // Detalles
                _crearFilaDetalle(Icons.local_shipping, vehiculo['vehiculo']),
                const SizedBox(height: 8),
                _crearFilaDetalle(Icons.business, vehiculo['cliente']),
                const SizedBox(height: 8),
                _crearFilaDetalle(Icons.person, 'Mecánico: ${vehiculo['mecanico']}'),
                const SizedBox(height: 8),
                
                // Mostrar fecha de salida solo si es del historial
                esHistorial
                    ? _crearFilaDetalle(Icons.calendar_month, 'Ingreso: ${vehiculo['fecha_ingreso']}  |  Salida: ${vehiculo['fecha_salida']}')
                    : _crearFilaDetalle(Icons.calendar_today, 'Ingreso: ${vehiculo['fecha_ingreso']}'),
                
                const SizedBox(height: 16),
                
                // Botón de acción dependiendo de la pestaña
                SizedBox(
                  width: double.infinity,
                  height: 45,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      // Acción a programar después
                    },
                    icon: Icon(esHistorial ? Icons.picture_as_pdf : Icons.camera_alt, size: 20),
                    label: Text(esHistorial ? 'Ver Informe Final' : 'Ver Orden y Fotos'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: esHistorial ? Colors.grey[200] : const Color(0xFF1976D2).withOpacity(0.1),
                      foregroundColor: esHistorial ? Colors.grey[800] : const Color(0xFF1976D2),
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                )
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _crearFilaDetalle(IconData icono, String texto) {
    return Row(
      children: [
        Icon(icono, size: 18, color: const Color(0xFF757575)),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            texto, 
            style: const TextStyle(fontSize: 14, color: Color(0xFF555555)),
            overflow: TextOverflow.ellipsis, // Para que textos largos no rompan la pantalla
          ),
        ),
      ],
    );
  }
}