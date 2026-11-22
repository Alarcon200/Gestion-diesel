import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Emulador de Android: http://10.0.2.2/api (valor por defecto).
  // Celular real: flutter run --dart-define=API_URL=http://IP-DE-TU-MAC/api
 static const String baseUrl = String.fromEnvironment(
  'API_URL',
  defaultValue: 'http://192.168.1.9/api',
);
  static String? _token;
  static String? _rol;
  static String? _nombre;

  static Future<void> cargarSesion() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('token');
    _rol = prefs.getString('rol');
    _nombre = prefs.getString('nombre');
  }

  static Future<void> guardarSesion(String token, String rol, String nombre) async {
    _token = token;
    _rol = rol;
    _nombre = nombre;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('token', token);
    await prefs.setString('rol', rol);
    await prefs.setString('nombre', nombre);
  }

  static Future<void> cerrarSesion() async {
    _token = null;
    _rol = null;
    _nombre = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
  }

  static bool get autenticado => _token != null;
  static String get rol => _rol ?? '';
  static String get nombre => _nombre ?? '';

  static Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (_token != null) 'Authorization': 'Bearer $_token',
      };

  static Future<Map<String, dynamic>> login(String email, String password) async {
    final res = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
      body: jsonEncode({'email': email, 'password': password}),
    );

    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      final rol = data['usuario']?['rol']?['nombre'] ?? '';
      final nombre = data['usuario']?['nombre'] ?? '';
      await guardarSesion(data['token'], rol, nombre);
      return data;
    }
    throw _errorDesdeRespuesta(res);
  }

  static Future<dynamic> get(String endpoint) async {
    final res = await http.get(Uri.parse('$baseUrl/$endpoint'), headers: _headers);
    if (res.statusCode == 200) return jsonDecode(res.body);
    throw _errorDesdeRespuesta(res);
  }

  static Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> body) async {
    final res = await http.post(
      Uri.parse('$baseUrl/$endpoint'),
      headers: _headers,
      body: jsonEncode(body),
    );
    if (res.statusCode == 200 || res.statusCode == 201) return jsonDecode(res.body);
    throw _errorDesdeRespuesta(res);
  }

  static Future<Map<String, dynamic>> put(String endpoint, Map<String, dynamic> body) async {
    final res = await http.put(
      Uri.parse('$baseUrl/$endpoint'),
      headers: _headers,
      body: jsonEncode(body),
    );
    if (res.statusCode == 200) return jsonDecode(res.body);
    throw _errorDesdeRespuesta(res);
  }

  static Future<int> subirFoto(File archivo, {String? descripcion}) async {
    final uri = Uri.parse('$baseUrl/fotografias');
    final request = http.MultipartRequest('POST', uri);
    request.headers['Accept'] = 'application/json';
    if (_token != null) request.headers['Authorization'] = 'Bearer $_token';
    request.files.add(await http.MultipartFile.fromPath('foto', archivo.path));
    if (descripcion != null && descripcion.isNotEmpty) request.fields['descripcion'] = descripcion;

    final streamed = await request.send();
    final res = await http.Response.fromStream(streamed);

    if (res.statusCode == 201) return jsonDecode(res.body)['id'];
    throw _errorDesdeRespuesta(res);
  }

  /// Sube una foto directamente al ingreso (vehículo en el taller), con su nota.
  static Future<Map<String, dynamic>> subirFotoIngreso(int ingresoId, File archivo, {String? descripcion}) async {
    final uri = Uri.parse('$baseUrl/ingresos/$ingresoId/fotos');
    final request = http.MultipartRequest('POST', uri);
    request.headers['Accept'] = 'application/json';
    if (_token != null) request.headers['Authorization'] = 'Bearer $_token';
    request.files.add(await http.MultipartFile.fromPath('foto', archivo.path));
    if (descripcion != null && descripcion.isNotEmpty) request.fields['descripcion'] = descripcion;

    final streamed = await request.send().timeout(const Duration(seconds: 90));
    final res = await http.Response.fromStream(streamed);

    if (res.statusCode == 201) return Map<String, dynamic>.from(jsonDecode(res.body));
    throw _errorDesdeRespuesta(res);
  }

  static Exception _errorDesdeRespuesta(http.Response res) {
    try {
      final data = jsonDecode(res.body);
      if (data['errors'] != null) {
        final primerError = (data['errors'] as Map).values.first;
        return Exception(primerError is List ? primerError.first : primerError.toString());
      }
      return Exception(data['message'] ?? 'Error ${res.statusCode}');
    } catch (_) {
      return Exception('Error ${res.statusCode}');
    }
  }
}