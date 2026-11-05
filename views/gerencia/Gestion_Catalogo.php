<?php
/**
 * Variables inyectadas desde CatalogoController.php:
 * @var array $categorias
 * @var array $productos
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Padrino's Pizza - Gestión de Menú</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .font-padrino { font-family: 'Cinzel', serif; }
  </style>
</head>
<body class="bg-[#0E1A14] text-white min-h-screen">

  <!-- Header Superior -->
  <header class="bg-[#1B4332] border-b-2 border-[#D4AF37] px-6 py-3 flex justify-between items-center shadow-md">
    <div class="flex items-center space-x-4">
      <h1 class="text-xl font-bold text-[#D4AF37] font-padrino tracking-wider">PADRINO'S PIZZA</h1>
      <span class="bg-[#D4AF37]/15 text-[#D4AF37] border border-[#D4AF37] text-xs px-3 py-1 rounded-full font-semibold">
        Administración - Gestión de Menú
      </span>
    </div>
    <a href="GerenciaController.php" class="bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-600 px-4 py-1.5 rounded-lg text-xs font-semibold transition">
      ← Volver al Dashboard
    </a>
  </header>

  <div class="container mx-auto p-6 max-w-6xl">

    <!-- Formulario de Registro / Edición -->
    <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-6 shadow-xl mb-8">
      <h2 class="text-lg font-bold text-[#D4AF37] font-padrino mb-4 pb-2 border-b border-[#D4AF37]/30" id="form-titulo">
        Registrar Nuevo Producto
      </h2>

      <form action="CatalogoController.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input type="hidden" name="id_producto" id="id_producto" value="">

        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-1">Nombre del Producto</label>
          <input type="text" name="nombre" id="nombre" required placeholder="Ej. Pizza Hawaiana Especial" 
                 class="w-full bg-black/30 border border-[#D4AF37]/30 rounded-lg p-2.5 text-sm text-white focus:outline-none focus:border-[#D4AF37]">
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-1">Categoría</label>
          <select name="id_categoria" id="id_categoria" required 
                  class="w-full bg-black/30 border border-[#D4AF37]/30 rounded-lg p-2.5 text-sm text-white focus:outline-none focus:border-[#D4AF37]">
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= $cat['id_categoria'] ?>" class="bg-[#14281E] text-white"><?= htmlspecialchars($cat['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-1">Precio Base ($)</label>
          <input type="number" step="0.01" name="precio_base" id="precio_base" required placeholder="0.00" 
                 class="w-full bg-black/30 border border-[#D4AF37]/30 rounded-lg p-2.5 text-sm text-white focus:outline-none focus:border-[#D4AF37]">
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-400 mb-1">Estado</label>
          <select name="activo" id="activo" 
                  class="w-full bg-black/30 border border-[#D4AF37]/30 rounded-lg p-2.5 text-sm text-white focus:outline-none focus:border-[#D4AF37]">
            <option value="1" class="bg-[#14281E]">Activo (Disponible en POS)</option>
            <option value="0" class="bg-[#14281E]">Inactivo (Ocultar)</option>
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="block text-xs font-semibold text-gray-400 mb-1">Descripción / Ingredientes</label>
          <textarea name="descripcion" id="descripcion" rows="2" placeholder="Pepperoni, jamón, champiñones..." 
                    class="w-full bg-black/30 border border-[#D4AF37]/30 rounded-lg p-2.5 text-sm text-white focus:outline-none focus:border-[#D4AF37]"></textarea>
        </div>

        <div class="md:col-span-2 flex justify-end gap-3 mt-2">
          <button type="button" onclick="limpiarFormulario()" class="bg-transparent border border-gray-500 text-gray-300 px-4 py-2 rounded-lg text-sm hover:bg-gray-800 transition">
            Limpiar / Nuevo
          </button>
          <button type="submit" class="bg-[#1B4332] hover:bg-[#1B4332]/80 border border-[#D4AF37] text-white font-bold px-6 py-2 rounded-lg text-sm transition">
            Guardar Producto
          </button>
        </div>
      </form>
    </div>

    <!-- Tabla Dinámica de Productos -->
    <div class="bg-[#14281E] border border-[#D4AF37]/30 rounded-xl p-6 shadow-xl">
      <h2 class="text-lg font-bold text-[#D4AF37] font-padrino mb-4">Catálogo Actual</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="bg-[#1B4332]/60 text-[#D4AF37] text-xs uppercase border-b border-[#D4AF37]/30">
              <th class="p-3">Código</th>
              <th class="p-3">Producto</th>
              <th class="p-3">Categoría</th>
              <th class="p-3">Precio</th>
              <th class="p-3">Estado</th>
              <th class="p-3 text-center">Acción</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <?php foreach ($productos as $prd): 
                $precioReal = $prd['precio_mostrar'] ?? $prd['precio'] ?? 0;
            ?>
            <tr class="hover:bg-[#D4AF37]/5 transition">
              <td class="p-3 font-bold text-gray-400">#PRD-<?= str_pad($prd['id_producto'], 3, '0', STR_PAD_LEFT) ?></td>
              <td class="p-3 font-semibold text-white"><?= htmlspecialchars($prd['nombre']) ?></td>
              <td class="p-3 text-gray-300"><?= htmlspecialchars($prd['categoria_nombre']) ?></td>
              <td class="p-3 font-bold text-[#D4AF37]">$<?= number_format((float)$precioReal, 2) ?></td>
              <td class="p-3">
                <?php if ($prd['activo']): ?>
                  <span class="text-emerald-400 font-bold text-xs">Activo</span>
                <?php else: ?>
                  <span class="text-red-400 font-bold text-xs">Inactivo</span>
                <?php endif; ?>
              </td>
              <td class="p-3 text-center">
                <button onclick='editarProducto(<?= json_encode($prd) ?>)' 
                        class="bg-[#1B4332] hover:bg-[#D4AF37]/20 border border-[#D4AF37] text-white px-3 py-1 rounded text-xs transition">
                  Editar
                </button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <script>
    function editarProducto(prd) {
      document.getElementById('form-titulo').textContent = 'Editar Producto #' + prd.id_producto;
      document.getElementById('id_producto').value = prd.id_producto;
      document.getElementById('nombre').value = prd.nombre;
      document.getElementById('id_categoria').value = prd.id_categoria;
      document.getElementById('precio_base').value = prd.precio_mostrar || prd.precio || 0;
      document.getElementById('activo').value = prd.activo;
      document.getElementById('descripcion').value = prd.descripcion || '';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function limpiarFormulario() {
      document.getElementById('form-titulo').textContent = 'Registrar Nuevo Producto';
      document.getElementById('id_producto').value = '';
      document.getElementById('nombre').value = '';
      document.getElementById('precio_base').value = '';
      document.getElementById('descripcion').value = '';
      document.getElementById('activo').value = '1';
    }
  </script>

</body>
</html>