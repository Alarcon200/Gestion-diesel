import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // En emulador Android, 10.0.2.2 apunta al localhost de tu Mac
  static const String baseUrl = 'http://10.0.2.2/api';

  static String? _token;

  static Future<void> cargarToken() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('token');
  }

  static Future<void> guardarToken(String token) async {
    _token = token;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('token', token);
  }

  static Future<void> cerrarSesion() async {
    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
  }

  static bool get autenticado => _token != null;

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
      await guardarToken(data['token']);
      return data;
    }
    throw _errorDesdeRespuesta(res);
  }

  static Future<List<dynamic>> get(String endpoint) async {
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

  /// Sube una foto (multipart) al endpoint /fotografias y devuelve su id
  static Future<int> subirFoto(File archivo, {String? descripcion}) async {
    final uri = Uri.parse('$baseUrl/fotografias');
    final request = http.MultipartRequest('POST', uri);
    request.headers['Accept'] = 'application/json';
    if (_token != null) request.headers['Authorization'] = 'Bearer $_token';
    request.files.add(await http.MultipartFile.fromPath('foto', archivo.path));
    if (descripcion != null) request.fields['descripcion'] = descripcion;

    final streamed = await request.send();
    final res = await http.Response.fromStream(streamed);

    if (res.statusCode == 201) {
      return jsonDecode(res.body)['id'];
    }
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