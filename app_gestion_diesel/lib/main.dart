import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'pantallas/dashboard_screen.dart'; // Añade esta línea

void main() {
  runApp(const GestionDieselApp());
}

class GestionDieselApp extends StatelessWidget {
  const GestionDieselApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Gestión Diesel',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.blueGrey),
        useMaterial3: true,
      ),
      home: const LoginScreen(),
    );
  }
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  // Controladores para capturar lo que el usuario escribe
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  
  // Variable para mostrar un circulito de carga mientras esperamos a Laravel
  bool _isLoading = false; 

  Future<void> _iniciarSesion() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await http.post(
        Uri.parse('http://10.0.2.2/api/login'), 
        headers: {
          'Accept': 'application/json',
        },
        body: {
          'email': _emailController.text,
          'password': _passwordController.text,
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        final token = data['token']; 
        
        print("¡Login exitoso! Token recibido: $token");
        
        // ¡Esta es la instrucción que hace el salto al Panel de Gestión!
        if (!mounted) return; 
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => const DashboardScreen()),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Correo o contraseña incorrectos'), backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      print("Error de conexión: $e");
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No se pudo conectar con el servidor'), backgroundColor: Colors.orange),
      );
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      body: Center(
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.settings_suggest, size: 120, color: Colors.blueGrey),
                const SizedBox(height: 20),
                const Text(
                  'Gestión Diesel',
                  style: TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Colors.blueGrey),
                ),
                const Text('Acceso al Sistema', style: TextStyle(fontSize: 16, color: Colors.grey)),
                const SizedBox(height: 40),
                TextField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  decoration: InputDecoration(
                    labelText: 'Correo Electrónico',
                    prefixIcon: const Icon(Icons.email),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    filled: true,
                    fillColor: Colors.white,
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _passwordController,
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: 'Contraseña',
                    prefixIcon: const Icon(Icons.lock),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    filled: true,
                    fillColor: Colors.white,
                  ),
                ),
                const SizedBox(height: 30),
                SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: _isLoading 
                    ? const Center(child: CircularProgressIndicator())
                    : ElevatedButton(
                        onPressed: _iniciarSesion,
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.blueGrey,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text('Iniciar Sesión', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}