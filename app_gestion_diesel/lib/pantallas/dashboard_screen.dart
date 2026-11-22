import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../tema.dart';
import '../widgets/placa.dart';
import 'login_screen.dart';
import 'clientes_screen.dart';
import 'vehiculos_screen.dart';
import 'ingreso_nuevo_screen.dart';
import 'ingresos_lista_screen.dart';
import 'mis_trabajos_screen.dart';
import 'asignar_trabajos_screen.dart';
import 'notificaciones_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  int _noLeidas = 0;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _revisarNotificaciones(primera: true);
    // Mientras la app esté abierta, revisa si hay avisos nuevos cada 20 segundos
    _timer = Timer.periodic(const Duration(seconds: 20), (_) => _revisarNotificaciones());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _revisarNotificaciones({bool primera = false}) async {
    try {
      final data = await ApiService.get('notificaciones/contador');
      final n = (data['no_leidas'] ?? 0) as int;
      if (!mounted) return;
      final hayNuevas = n > _noLeidas;
      setState(() => _noLeidas = n);
      if (hayNuevas && !primera) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Tienes notificaciones nuevas'),
            action: SnackBarAction(label: 'VER', onPressed: _abrirNotificaciones),
          ),
        );
      }
    } catch (_) {
      // Sin conexión: se intenta de nuevo en la próxima revisión
    }
  }

  Future<void> _abrirNotificaciones() async {
    await Navigator.push(context, MaterialPageRoute(builder: (_) => const NotificacionesScreen()));
    _revisarNotificaciones(primera: true);
  }

  @override
  Widget build(BuildContext context) {
    final rol = ApiService.rol;
    final esAdminOJefe = rol == 'Administrador' || rol == 'Jefe de taller';
    final esMecanico = rol == 'Mecánico';

    return Scaffold(
      appBar: AppBar(
        titleSpacing: 12,
        title: Row(
          children: [
            ClipOval(
              child: Image.asset('assets/logo.png', width: 38, height: 38, fit: BoxFit.cover),
            ),
            const SizedBox(width: 10),
            const Text('Gestión Diesel'),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Notificaciones',
            onPressed: _abrirNotificaciones,
            icon: Badge(
              isLabelVisible: _noLeidas > 0,
              label: Text(_noLeidas > 99 ? '99+' : '$_noLeidas'),
              backgroundColor: Marca.rojo,
              child: const Icon(Icons.notifications_none),
            ),
          ),
          IconButton(
            tooltip: 'Cerrar sesión',
            icon: const Icon(Icons.logout),
            onPressed: () async {
              _timer?.cancel();
              await ApiService.cerrarSesion();
              if (!context.mounted) return;
              Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
            },
          ),
        ],
        bottom: const PreferredSize(
          preferredSize: Size.fromHeight(4),
          child: SizedBox(height: 4, width: double.infinity, child: ColoredBox(color: Marca.rojo)),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Padding(
            padding: const EdgeInsets.only(top: 4, bottom: 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Hola, ${ApiService.nombre}',
                  style: const TextStyle(fontSize: 26, fontWeight: FontWeight.w800, color: Marca.tinta),
                ),
                const SizedBox(height: 4),
                EtiquetaColor(texto: rol, color: Marca.azul),
              ],
            ),
          ),
          if (esMecanico)
            _tile(
              'Mis trabajos',
              'Vehículos asignados a ti',
              Icons.build,
              Marca.rojo,
              () => Navigator.push(context, MaterialPageRoute(builder: (_) => const MisTrabajosScreen())),
            ),
          _tile(
            'Vehículos en el taller',
            'Estado, placa y número ST',
            Icons.local_shipping,
            Marca.azul,
            () => Navigator.push(context, MaterialPageRoute(builder: (_) => const IngresosListaScreen())),
          ),
          if (esAdminOJefe) ...[
            _tile(
              'Asignar trabajos',
              'Elegir el mecánico de cada vehículo',
              Icons.engineering,
              Marca.rojo,
              () => Navigator.push(context, MaterialPageRoute(builder: (_) => const AsignarTrabajosScreen())),
            ),
            _tile(
              'Ingresar vehículo',
              'Registrar la entrada, inspección y fotos',
              Icons.login,
              Marca.rojo,
              () => Navigator.push(context, MaterialPageRoute(builder: (_) => IngresoNuevoScreen())),
            ),
            _tile(
              'Clientes',
              'Empresas y contactos',
              Icons.people,
              Marca.tinta,
              () => Navigator.push(context, MaterialPageRoute(builder: (_) => const ClientesScreen())),
            ),
            _tile(
              'Catálogo de vehículos',
              'Placas registradas',
              Icons.badge,
              Marca.tinta,
              () => Navigator.push(context, MaterialPageRoute(builder: (_) => const VehiculosScreen())),
            ),
          ],
        ],
      ),
    );
  }

  Widget _tile(String titulo, String detalle, IconData icono, Color color, VoidCallback onTap) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: TarjetaTaller(
        franja: color,
        onTap: onTap,
        padding: const EdgeInsets.fromLTRB(14, 14, 12, 14),
        child: Row(
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(4)),
              child: Icon(icono, color: Colors.white, size: 26),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(titulo, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w700, color: Marca.tinta)),
                  const SizedBox(height: 2),
                  Text(detalle, style: const TextStyle(fontSize: 13, color: Marca.gris)),
                ],
              ),
            ),
            const Icon(Icons.chevron_right, color: Marca.gris),
          ],
        ),
      ),
    );
  }
}
