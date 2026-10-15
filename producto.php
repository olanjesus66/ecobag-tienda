<?php
require_once 'includes/conexion.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT p.*, c.nombre AS categoria
    FROM productos p
    JOIN categorias c ON c.id = p.categoria_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$p = $stmt->fetch();

// Si el id no existe (ej. producto.php?id=999), regresamos al catálogo
if (!$p) {
    header('Location: productos.php');
    exit;
}

// Galería de imágenes
$stmt = $pdo->prepare("SELECT archivo FROM producto_imagenes WHERE producto_id = ? ORDER BY orden");
$stmt->execute([$id]);
$galeria = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (!$galeria) $galeria = [$p['imagen']];

include 'includes/header.php';
?>
<div class="breadcrumb">
  <a href="index.php">Inicio</a> <span>›</span>
  <a href="productos.php">Productos</a> <span>›</span>
  <strong><?= htmlspecialchars($p['nombre']) ?></strong>
</div>

<section class="detalle">

  <!-- ===== Galería ===== -->
  <div class="galeria">
    <img id="img-principal"
         src="img/<?= htmlspecialchars($galeria[0]) ?>"
         alt="<?= htmlspecialchars($p['nombre']) ?>"
         onerror="this.onerror=null; this.src='img/sin-foto.jpg';">

    <?php if (count($galeria) > 1): ?>
      <div class="miniaturas">
        <?php foreach ($galeria as $i => $foto): ?>
          <img class="mini <?= $i === 0 ? 'activa' : '' ?>"
               src="img/<?= htmlspecialchars($foto) ?>"
               data-src="img/<?= htmlspecialchars($foto) ?>"
               alt="Vista <?= $i + 1 ?>">
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- ===== Información ===== -->
  <div class="info">
    <small class="etiqueta"><?= htmlspecialchars(strtoupper($p['categoria'])) ?></small>
    <h1><?= htmlspecialchars($p['nombre']) ?></h1>
    <p class="precio">$<?= number_format($p['precio'], 0) ?> MXN</p>

    <div class="estrellas">
      <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
      <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
      <i class="fa-solid fa-star"></i>
      <span>(4.8) · 124 reseñas</span>
    </div>

    <p class="resumen"><?= htmlspecialchars($p['descripcion']) ?></p>

    <ul class="datos">
      <li><i class="fa-solid fa-leaf"></i> Material: <?= htmlspecialchars($p['material']) ?></li>
      <li><i class="fa-solid fa-ruler-combined"></i> Medidas: <?= htmlspecialchars($p['medidas']) ?></li>
      <li><i class="fa-solid fa-recycle"></i> Reutilizable y lavable</li>
    </ul>

    <div class="compra">
      <div class="cantidad">
        <button type="button" class="menos">−</button>
        <input type="text" id="cantidad" value="1" readonly>
        <button type="button" class="mas">+</button>
      </div>
      <button type="button" class="btn btn-agregar-detalle" data-id="<?= $p['id'] ?>">
        <i class="fa-solid fa-cart-plus"></i> Agregar al carrito
      </button>
    </div>
    <p id="msg-carrito" class="msg-carrito">Agregado al carrito ✓</p>
  </div>
</section>

<!-- ===== Pestañas ===== -->
<section class="pestanas">
  <div class="tabs">
    <button type="button" class="tab activa" data-tab="tab-desc">Descripción</button>
    <button type="button" class="tab" data-tab="tab-envios">Envíos</button>
    <button type="button" class="tab" data-tab="tab-resenas">Reseñas</button>
  </div>

  <div class="panel" id="tab-desc">
    <p><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
  </div>
  <div class="panel" id="tab-envios" hidden>
    <p>Enviamos a todo el país. El tiempo de entrega es de 3 a 7 días hábiles. Envío gratis en compras mayores a $500 MXN.</p>
  </div>
  <div class="panel" id="tab-resenas" hidden>
    <p>Las reseñas de clientes estarán disponibles próximamente.</p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>