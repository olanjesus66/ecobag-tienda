<?php
include 'includes/header.php';
require_once 'includes/funciones.php';

$c = obtenerCarrito($pdo);
?>

<section class="carrito" id="contenedor-carrito">
  <h1><i class="fa-solid fa-cart-shopping"></i> Tu carrito</h1>

  <?php if (!$c['items']): ?>

    <div class="carrito-vacio">
      <p>Tu carrito está vacío.</p>
      <a href="productos.php" class="btn">Ver productos</a>
    </div>

  <?php else: ?>

    <div class="tabla-scroll">
      <table class="tabla-carrito">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($c['items'] as $it): ?>
            <tr id="fila-<?= $it['id'] ?>">
              <td>
                <div class="producto-celda">
                  <img src="img/<?= htmlspecialchars($it['imagen']) ?>"
                       alt="<?= htmlspecialchars($it['nombre']) ?>"
                       onerror="this.onerror=null; this.src='img/sin-foto.jpg';">
                  <a href="producto.php?id=<?= $it['id'] ?>"><?= htmlspecialchars($it['nombre']) ?></a>
                </div>
              </td>
              <td><?= dinero($it['precio']) ?></td>
              <td>
                <div class="cantidad cantidad-carrito">
                  <button type="button" class="btn-qty" data-id="<?= $it['id'] ?>" data-accion="menos">−</button>
                  <span class="qty"><?= $it['cantidad'] ?></span>
                  <button type="button" class="btn-qty" data-id="<?= $it['id'] ?>" data-accion="mas">+</button>
                </div>
              </td>
              <td class="subtotal-item"><?= dinero($it['subtotal']) ?></td>
              <td>
                <button type="button" class="btn-eliminar" data-id="<?= $it['id'] ?>" aria-label="Eliminar">
                  <i class="fa-regular fa-trash-can"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="carrito-pie">
      <a href="productos.php" class="seguir"><i class="fa-solid fa-arrow-left"></i> Seguir comprando</a>

      <div class="caja-resumen">
        <div class="linea"><span>Subtotal</span> <span id="res-subtotal"><?= dinero($c['subtotal']) ?></span></div>
        <div class="linea"><span>Envío</span>    <span id="res-envio"><?= dinero($c['envio']) ?></span></div>
        <div class="linea total"><span>Total</span> <span id="res-total"><?= dinero($c['total']) ?></span></div>
        <p id="error-compra" class="errores" style="display:none;"></p>
            <?php if (isset($_SESSION['usuario_id'])): ?>
              <button type="button" id="btn-finalizar" class="btn btn-bloque"
                      data-csrf="<?= $_SESSION['csrf'] ?>">
                <i class="fa-solid fa-lock"></i> Finalizar compra
              </button>
            <?php else: ?>
              <a href="login.php?volver=carrito.php" class="btn btn-bloque">
                Inicia sesión para comprar
              </a>
            <?php endif; ?>
          <i class="fa-solid fa-lock"></i> Finalizar compra
        </a>
      </div>
    </div>

  <?php endif; ?>
</section>

<template id="tpl-gracias">
  <div class="gracias">
    <div class="gracias-icono"><i class="fa-solid fa-check"></i></div>
    <h2>¡Gracias por tu compra!</h2>
    <p>Tu pedido ha sido registrado correctamente.</p>

    <div class="gracias-pedido">
      <span>Número de pedido</span>
      <strong id="gracias-pedido"></strong>
      <span>Total del pedido: <b id="gracias-total"></b></span>
    </div>

    <h3>¿Qué sigue?</h3>
    <div class="siguiente">
      <div><i class="fa-regular fa-clipboard"></i><b>1. Confirmación</b><small>Guarda tu número de pedido.</small></div>
      <div><i class="fa-solid fa-box-open"></i><b>2. Preparación</b><small>Empacamos tu pedido.</small></div>
      <div><i class="fa-solid fa-truck"></i><b>3. Envío</b><small>Tu pedido sale a su destino.</small></div>
    </div>

    <a href="productos.php" class="btn">Seguir comprando</a>
  </div>
</template>

<?php include 'includes/footer.php'; ?>