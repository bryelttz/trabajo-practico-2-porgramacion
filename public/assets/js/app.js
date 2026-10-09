$(function () {
    const $mensaje = $('#mensaje');
    let examenSeleccionado = null;

    function mostrarMensaje(texto, error) {
        $mensaje.text(texto || '').toggleClass('error', Boolean(error));
    }

    function enviar(opciones) {
        return $.ajax(opciones).fail(function (respuesta) {
            const texto = respuesta.responseJSON && respuesta.responseJSON.mensaje
                ? respuesta.responseJSON.mensaje
                : 'No se pudo completar la operación. Revise la conexión y vuelva a intentarlo.';

            mostrarMensaje(texto, true);
        });
    }

    function limpiarExamen() {
        $('#form-examen')[0].reset();
        $('#examen-id').val('');
        $('#guardar-examen').text('Guardar examen');
        $('#cancelar-examen').addClass('oculto');
    }

    function limpiarPregunta() {
        $('#form-pregunta')[0].reset();
        $('#pregunta-id').val('');
        $('#guardar-pregunta').text('Guardar pregunta');
        $('#cancelar-pregunta').addClass('oculto');
    }

    function cargarExamenes() {
        enviar({ url: 'api/examenes', method: 'GET', dataType: 'json' })
            .done(function (respuesta) {
                const $lista = $('#lista-examenes').empty();

                respuesta.examenes.forEach(function (examen) {
                    const $fila = $('<article class="fila">');
                    const $info = $('<div>');
                    $info.append($('<h3>').text(examen.titulo));
                    if (examen.descripcion) {
                        $info.append($('<p>').text(examen.descripcion));
                    }

                    const $acciones = $('<div class="acciones">');
                    $acciones.append($('<button type="button" class="boton boton-pequeno">')
                        .text('Ver preguntas').attr('data-accion', 'preguntas').attr('data-id', examen.id));
                    $acciones.append($('<button type="button" class="boton boton-claro boton-pequeno">')
                        .text('Editar').attr('data-accion', 'editar').attr('data-id', examen.id));
                    $acciones.append($('<button type="button" class="boton boton-rojo boton-pequeno">')
                        .text('Eliminar').attr('data-accion', 'eliminar').attr('data-id', examen.id));

                    $fila.append($info, $acciones).appendTo($lista);
                });

                if (respuesta.examenes.length === 0) {
                    $lista.append($('<p>').text('Todavía no hay exámenes. Cree el primero con el formulario.'));
                }
            });
    }

    function cargarPreguntas() {
        enviar({
            url: 'api/preguntas',
            method: 'GET',
            data: { examen_id: examenSeleccionado.id },
            dataType: 'json'
        }).done(function (respuesta) {
            const $lista = $('#lista-preguntas').empty();
            $('#cantidad-preguntas').attr('max', respuesta.preguntas.length || 1);

            respuesta.preguntas.forEach(function (pregunta) {
                const $fila = $('<article class="fila">');
                $fila.append($('<p>').text(pregunta.texto));
                const $acciones = $('<div class="acciones">');
                $acciones.append($('<button type="button" class="boton boton-claro boton-pequeno">')
                    .text('Editar').attr('data-accion', 'editar-pregunta').attr('data-id', pregunta.id));
                $acciones.append($('<button type="button" class="boton boton-rojo boton-pequeno">')
                    .text('Eliminar').attr('data-accion', 'eliminar-pregunta').attr('data-id', pregunta.id));
                $fila.append($acciones).appendTo($lista);
            });

            if (respuesta.preguntas.length === 0) {
                $lista.append($('<p>').text('Agregue preguntas para poder realizar un sorteo.'));
            }
        });
    }

    $('#form-login').on('submit', function (evento) {
        evento.preventDefault();
        enviar({ url: 'api/login', method: 'POST', data: $(this).serialize(), dataType: 'json' })
            .done(function () { window.location.reload(); });
    });

    $('#form-registro').on('submit', function (evento) {
        evento.preventDefault();
        const email = $('#registro-email').val();

        enviar({ url: 'api/registro', method: 'POST', data: $(this).serialize(), dataType: 'json' })
            .done(function () {
                $('#form-registro').closest('.tarjeta').addClass('oculto');
                $('#form-login').closest('.tarjeta').removeClass('oculto');
                $('#form-login')[0].reset();
                $('#login-email').val(email);
                mostrarMensaje('Cuenta creada. Ahora ingrese con su correo y contraseña.');
                $('#login-password').trigger('focus');
            });
    });

    $('#mostrar-login').on('click', function () {
        $('#form-registro').closest('.tarjeta').addClass('oculto');
        $('#form-login').closest('.tarjeta').removeClass('oculto');
        mostrarMensaje('');
        $('#login-email').trigger('focus');
    });

    $('#mostrar-registro').on('click', function () {
        $('#form-login').closest('.tarjeta').addClass('oculto');
        $('#form-registro').closest('.tarjeta').removeClass('oculto');
        mostrarMensaje('');
        $('#registro-nombre').trigger('focus');
    });

    $('#cerrar-sesion').on('click', function () {
        enviar({ url: 'api/logout', method: 'POST', dataType: 'json' })
            .done(function () { window.location.reload(); });
    });

    $('#form-examen').on('submit', function (evento) {
        evento.preventDefault();
        enviar({ url: 'api/examenes/guardar', method: 'POST', data: $(this).serialize(), dataType: 'json' })
            .done(function () {
                mostrarMensaje('Examen guardado correctamente.');
                limpiarExamen();
                cargarExamenes();
            });
    });

    $('#cancelar-examen').on('click', limpiarExamen);

    $('#lista-examenes').on('click', 'button', function () {
        const id = Number($(this).attr('data-id'));
        const accion = $(this).attr('data-accion');
        const examen = $(this).closest('.fila');

        if (accion === 'preguntas') {
            examenSeleccionado = { id: id, titulo: examen.find('h3').text() };
            $('#titulo-examen-seleccionado').text(examenSeleccionado.titulo);
            $('#seccion-preguntas').removeClass('oculto');
            $('#resultado-sorteo').empty();
            limpiarPregunta();
            cargarPreguntas();
        } else if (accion === 'editar') {
            $('#examen-id').val(id);
            $('#examen-titulo').val(examen.find('h3').text());
            $('#examen-descripcion').val(examen.find('p').text());
            $('#guardar-examen').text('Actualizar examen');
            $('#cancelar-examen').removeClass('oculto');
            $('#examen-titulo').trigger('focus');
        } else if (accion === 'eliminar' && window.confirm('¿Eliminar este examen y todas sus preguntas?')) {
            enviar({ url: 'api/examenes/eliminar', method: 'POST', data: { id: id }, dataType: 'json' })
                .done(function () {
                    if (examenSeleccionado && examenSeleccionado.id === id) {
                        examenSeleccionado = null;
                        $('#seccion-preguntas').addClass('oculto');
                    }
                    mostrarMensaje('Examen eliminado.');
                    cargarExamenes();
                });
        }
    });

    $('#form-pregunta').on('submit', function (evento) {
        evento.preventDefault();
        const datos = $(this).serializeArray();
        datos.push({ name: 'examen_id', value: examenSeleccionado.id });

        enviar({ url: 'api/preguntas/guardar', method: 'POST', data: $.param(datos), dataType: 'json' })
            .done(function () {
                mostrarMensaje('Pregunta guardada correctamente.');
                limpiarPregunta();
                cargarPreguntas();
            });
    });

    $('#cancelar-pregunta').on('click', limpiarPregunta);

    $('#lista-preguntas').on('click', 'button', function () {
        const id = Number($(this).attr('data-id'));
        const accion = $(this).attr('data-accion');
        const $fila = $(this).closest('.fila');

        if (accion === 'editar-pregunta') {
            $('#pregunta-id').val(id);
            $('#pregunta-texto').val($fila.find('p').text()).trigger('focus');
            $('#guardar-pregunta').text('Actualizar pregunta');
            $('#cancelar-pregunta').removeClass('oculto');
        } else if (accion === 'eliminar-pregunta' && window.confirm('¿Eliminar esta pregunta?')) {
            enviar({
                url: 'api/preguntas/eliminar',
                method: 'POST',
                data: { id: id, examen_id: examenSeleccionado.id },
                dataType: 'json'
            }).done(function () {
                mostrarMensaje('Pregunta eliminada.');
                cargarPreguntas();
            });
        }
    });

    $('#form-sorteo').on('submit', function (evento) {
        evento.preventDefault();
        enviar({
            url: 'api/preguntas/sortear',
            method: 'POST',
            data: { examen_id: examenSeleccionado.id, cantidad: $('#cantidad-preguntas').val() },
            dataType: 'json'
        }).done(function (respuesta) {
            const $resultado = $('#resultado-sorteo').empty();
            respuesta.preguntas.forEach(function (pregunta) {
                $resultado.append($('<li>').text(pregunta.texto));
            });
            mostrarMensaje('Sorteo realizado.');
        });
    });

    if ($('#lista-examenes').length) {
        cargarExamenes();
    }
});
