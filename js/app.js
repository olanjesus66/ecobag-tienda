$(document).ready(function () {

  $('.btn-agregar').on('click', function () {
    const id = $(this).data('id');

    $.post('ajax/agregar_carrito.php', { id: id }, function (resp) {
      if (resp.ok) {
        $('#contador-carrito').text(resp.total);
      } else {
        alert(resp.mensaje);
      }
    }, 'json');
  });

});
  // Filtros: enviar el formulario al cambiar cualquier opción
  $('#form-filtros').on('change', 'input, select', function () {
    $('#form-filtros').submit();
  });

  // Menú hamburguesa (celular)
  $('.btn-menu').on('click', function () {
    $('.menu').toggleClass('abierto');
  });

  // ----- Detalle de producto -----

  // Cambiar la foto principal al hacer clic en una miniatura
  $('.mini').on('click', function () {
    $('#img-principal').attr('src', $(this).data('src'));
    $('.mini').removeClass('activa');
    $(this).addClass('activa');
  });

  // Selector de cantidad
  $('.mas').on('click', function () {
    let n = parseInt($('#cantidad').val());
    if (n < 99) $('#cantidad').val(n + 1);
  });
  $('.menos').on('click', function () {
    let n = parseInt($('#cantidad').val());
    if (n > 1) $('#cantidad').val(n - 1);
  });

  // Agregar al carrito con la cantidad elegida
  $('.btn-agregar-detalle').on('click', function () {
    const id = $(this).data('id');
    const cantidad = $('#cantidad').val();

    $.post('ajax/agregar_carrito.php', { id: id, cantidad: cantidad }, function (resp) {
      if (resp.ok) {
        $('#contador-carrito').text(resp.total);
        $('#msg-carrito').stop(true, true).fadeIn(200).delay(2000).fadeOut(400);
      } else {
        alert(resp.mensaje);
      }
    }, 'json');
  });

  // Pestañas
  $('.tab').on('click', function () {
    const destino = $(this).data('tab');
    $('.tab').removeClass('activa');
    $(this).addClass('activa');
    $('.panel').attr('hidden', true);
    $('#' + destino).removeAttr('hidden');
  });

    // ----- Carrito -----
  function actualizarCarrito(id, accion) {
    $.post('ajax/actualizar_carrito.php', { id: id, accion: accion }, function (resp) {
      if (!resp.ok) return;

      // Si ya no queda nada, recargamos para mostrar "Tu carrito está vacío"
      if (resp.vacio) {
        location.reload();
        return;
      }

      const fila = $('#fila-' + id);
      if (accion === 'eliminar') {
        fila.fadeOut(250, function () { $(this).remove(); });
      } else {
        fila.find('.qty').text(resp.cantidad_item);
        fila.find('.subtotal-item').text(resp.subtotal_item);
      }

      $('#res-subtotal').text(resp.subtotal);
      $('#res-envio').text(resp.envio);
      $('#res-total').text(resp.total);
      $('#contador-carrito').text(resp.contador);
    }, 'json');
  }

  $('.btn-qty').on('click', function () {
    actualizarCarrito($(this).data('id'), $(this).data('accion'));
  });

  $('.btn-eliminar').on('click', function () {
    actualizarCarrito($(this).data('id'), 'eliminar');
  });

    // ----- Registro -----

  // Mostrar / ocultar contraseña
  $('.ver-pass').on('click', function () {
    const input = $('#' + $(this).data('target'));
    const visible = input.attr('type') === 'text';
    input.attr('type', visible ? 'password' : 'text');
    $(this).find('i').toggleClass('fa-eye fa-eye-slash');
  });

  // Validar antes de enviar
  $('#form-registro').on('submit', function (e) {
    const p1 = $('#password').val();
    const p2 = $('#password2').val();
    let mensaje = '';

    if (p1.length < 8) {
      mensaje = 'La contraseña debe tener al menos 8 caracteres.';
    } else if (p1 !== p2) {
      mensaje = 'Las contraseñas no coinciden.';
    }

    if (mensaje) {
      e.preventDefault();                       // detiene el envío
      $('#error-js').text(mensaje).show();
      $('html, body').animate({ scrollTop: 0 }, 200);
    }
  });

    // Menú desplegable del usuario
  $('.btn-usuario').on('click', function (e) {
    e.stopPropagation();
    $(this).closest('.menu-usuario').toggleClass('abierto');
  });
  $(document).on('click', function () {
    $('.menu-usuario').removeClass('abierto');
  });

    // ----- Finalizar compra (sin salir de carrito.php) -----
  const textoBtnFinalizar = '<i class="fa-solid fa-lock"></i> Finalizar compra';

  function errorCompra(mensaje) {
    $('#error-compra').text(mensaje).show();
    $('#btn-finalizar').prop('disabled', false).html(textoBtnFinalizar);
  }

  $('#btn-finalizar').on('click', function () {
    const btn = $(this);

    // Evita doble clic mientras se procesa
    btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Procesando...');
    $('#error-compra').hide();

    $.ajax({
      url: 'ajax/finalizar_compra.php',
      method: 'POST',
      data: { csrf: btn.data('csrf') },
      dataType: 'json'
    })
    .done(function (resp) {
      // Cambiamos SOLO el contenido del contenedor del carrito
      $('#contenedor-carrito')
        .hide()
        .html($('#tpl-gracias').html().trim())
        .fadeIn(300);

      $('#gracias-pedido').text(resp.pedido);
      $('#gracias-total').text(resp.total);
      $('#contador-carrito').text(0);

      $('html, body').animate({ scrollTop: 0 }, 250);
    })
    .fail(function (xhr) {
      const r = xhr.responseJSON || {};

      if (r.login) { location.href = r.url; return; }    // la sesión caducó
      if (r.vacio) { location.reload(); return; }        // el carrito ya estaba vacío

      errorCompra(r.mensaje || 'No pudimos procesar tu pedido. Inténtalo de nuevo.');
    });
  });