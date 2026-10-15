<?php
include 'includes/header.php';

// ---------- Datos fijos para armar los filtros ----------
$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$materiales = ['Algodón', 'Lino', 'Reciclado'];
$rangos = [
    '0-200'   => '$0 - $200',
    '201-400' => '$201 - $400',
    '401-600' => '$401 - $600',
];
$ordenes = [
    'recientes'   => 'id DESC',
    'precio_asc'  => 'precio ASC',
    'precio_desc' => 'precio DESC',
    'nombre'      => 'nombre ASC',
];

// ---------- Lo que eligió el usuario (viene por la URL) ----------
$cat        = (int)($_GET['categoria'] ?? 0);
$preciosSel = $_GET['precio']   ?? [];
$matsSel    = $_GET['material'] ?? [];
$orden      = $_GET['orden']    ?? 'recientes';

// ---------- Armar el WHERE pieza por pieza ----------
$where  = [];
$params = [];

// Categoría
if ($cat > 0) {
    $where[]  = 'categoria_id = ?';
    $params[] = $cat;
}

// Precio: si marca varios rangos, se une con OR
$condPrecio = [];
foreach ($preciosSel as $r) {
    if (isset($rangos[$r])) {              // solo aceptamos rangos que existen
        [$min, $max] = explode('-', $r);
        $condPrecio[] = '(precio BETWEEN ? AND ?)';
        $params[] = $min;
        $params[] = $max;
    }
}
if ($condPrecio) {
    $where[] = '(' . implode(' OR ', $condPrecio) . ')';
}

// Material: usamos IN (?, ?, ?)
$matsValidos = array_values(array_intersect($matsSel, $materiales));
if ($matsValidos) {
    $marcas  = implode(',', array_fill(0, count($matsValidos), '?'));
    $where[] = "material IN ($marcas)";
    foreach ($matsValidos as $m) $params[] = $m;
}

// ---------- Armar y ejecutar la consulta final ----------
$orderBy = $ordenes[$orden] ?? $ordenes['recientes'];

$sql = "SELECT * FROM productos";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= " ORDER BY $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();
?>
<section class="page-hero">
  <small>NUESTRA COLECCIÓN</small>
  <h1>Productos</h1>
  <p>Cada diseño está pensado para acompañarte en tu día a día, cuidando al planeta.</p>
</section>

<form method="get" id="form-filtros" class="catalogo">

  <!-- ===== Barra lateral de filtros ===== -->
  <aside class="filtros">

    <h4>Categorías</h4>
    <label class="opcion">
      <input type="radio" name="categoria" value="0" <?= $cat === 0 ? 'checked' : '' ?>> Todas
    </label>
    <?php foreach ($categorias as $c): ?>
      <label class="opcion">
        <input type="radio" name="categoria" value="<?= $c['id'] ?>"
               <?= $cat === (int)$c['id'] ? 'checked' : '' ?>>
        <?= htmlspecialchars($c['nombre']) ?>
      </label>
    <?php endforeach; ?>

    <h4>Precio</h4>
    <?php foreach ($rangos as $valor => $texto): ?>
      <label class="opcion">
        <input type="checkbox" name="precio[]" value="<?= $valor ?>"
               <?= in_array($valor, $preciosSel) ? 'checked' : '' ?>>
        <?= $texto ?>
      </label>
    <?php endforeach; ?>

    <h4>Material</h4>
    <?php foreach ($materiales as $m): ?>
      <label class="opcion">
        <input type="checkbox" name="material[]" value="<?= $m ?>"
               <?= in_array($m, $matsSel) ? 'checked' : '' ?>>
        <?= $m ?>
      </label>
    <?php endforeach; ?>

    <a href="productos.php" class="limpiar">Limpiar filtros</a>
  </aside>

  <!-- ===== Resultados ===== -->
  <div class="resultados">

    <div class="barra-orden">
      <span><?= count($productos) ?> producto(s)</span>
      <label>Ordenar por:
        <select name="orden">
          <option value="recientes"   <?= $orden === 'recientes'   ? 'selected' : '' ?>>Más recientes</option>
          <option value="precio_asc"  <?= $orden === 'precio_asc'  ? 'selected' : '' ?>>Precio: menor a mayor</option>
          <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
          <option value="nombre"      <?= $orden === 'nombre'      ? 'selected' : '' ?>>Nombre A-Z</option>
        </select>
      </label>
    </div>

    <?php if (!$productos): ?>
      <p class="vacio">No encontramos productos con esos filtros.</p>
    <?php else: ?>
      <div class="grid-productos">
        <?php foreach ($productos as $p): ?>
          <div class="tarjeta">
            <a href="producto.php?id=<?= $p['id'] ?>">
              <img src="img/<?= htmlspecialchars($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
            </a>
            <div class="tarjeta-info">
              <div>
                <h3><a href="producto.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></a></h3>
                <span>$<?= number_format($p['precio'], 0) ?> MXN</span>
              </div>
              <button type="button" class="btn-agregar" data-id="<?= $p['id'] ?>">
                <i class="fa-solid fa-cart-plus"></i>
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</form>

<?php include 'includes/footer.php'; ?>