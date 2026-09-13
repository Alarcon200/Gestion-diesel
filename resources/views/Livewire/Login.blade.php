<div class="max-w-sm mx-auto mt-20 bg-white p-8 rounded shadow">
    <h1 class="text-xl font-bold mb-6 text-center">Gestion Diesel</h1>

    @if($error)
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">{{ $error }}</div>
    @endif

    <form wire:submit="ingresar">
        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Correo</label>
            <input type="email" wire:model="email" class="w-full border rounded px-3 py-2">
            @error('email') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-6">
            <label class="block text-sm font-medium mb-1">Contraseña</label>
            <input type="password" wire:model="password" class="w-full border rounded px-3 py-2">
            @error('password') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="w-full bg-slate-800 text-white py-2 rounded hover:bg-slate-700">
            Ingresar
        </button>
    </form>
</div>