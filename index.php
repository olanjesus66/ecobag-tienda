<?php
include 'includes/header.php';

$stmt = $pdo->query("SELECT * FROM productos WHERE destacado = 1 LIMIT 4");
$destacados = $stmt->fetchAll();
?>

<section class="hero">
  <div>
    <small>BOLSAS ECOLÓGICAS</small>
    <h1>Diseños únicos,<br>huellas ligeras</h1>
    <p>Bolsas, accesorios y productos personalizados pensados para ti y para el planeta.</p>
    <a href="productos.php" class="btn">Ver productos →</a>
  </div>
  <img src="img/hero.png" alt="Bolsa ecológica">
</section>

<section class="beneficios">
  <div><i class="fa-solid fa-leaf"></i>Materiales sustentables</div>
  <div><i class="fa-solid fa-truck"></i>Envíos a todo el país</div>
  <div><i class="fa-solid fa-shield"></i>Compra segura</div>
  <div><i class="fa-regular fa-heart"></i>Diseños únicos</div>
</section>

<section class="seccion">
  <div class="seccion-titulo">
    <h2>Productos destacados</h2>
    <a href="productos.php">Ver todos →</a>
  </div>

  <div class="grid-productos grid-destacados">
    <?php foreach ($destacados as $p): ?>
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
</section>

<?php include 'includes/footer.php'; ?>