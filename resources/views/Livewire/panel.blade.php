<div>
    <h1 class="text-xl font-bold mb-1">Bienvenido, {{ session('usuario_nombre') }}</h1>
    <p class="text-gray-600 mb-6">Rol: {{ session('usuario_rol') }}</p>
    
    <!-- Aquí inyectamos el Dashboard -->
    <livewire:dashboard />
</div>