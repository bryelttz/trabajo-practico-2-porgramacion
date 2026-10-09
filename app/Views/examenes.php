<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis exámenes</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
    <header class="encabezado">
        <div class="contenedor encabezado-contenido">
            <div>
                <h1>Mis exámenes</h1>
                <p>Administre sus exámenes y banco de preguntas.</p>
            </div>
            <?php if ($usuario !== null): ?>
                <div class="usuario">
                    <span>Hola, <?= esc($usuario) ?></span>
                    <button id="cerrar-sesion" class="boton boton-claro" type="button">Cerrar sesión</button>
                </div>
            <?php endif ?>
        </div>
    </header>

    <main class="contenedor">
        <p id="mensaje" class="mensaje" role="status" aria-live="polite"></p>

        <?php if ($usuario === null): ?>
            <section class="autenticacion">
                <article class="tarjeta oculto">
                    <h2>Ingresar</h2>
                    <form id="form-login">
                        <label for="login-email">Correo</label>
                        <input id="login-email" name="email" type="email" required>
                        <label for="login-password">Contraseña</label>
                        <input id="login-password" name="password" type="password" required>
                        <button class="boton" type="submit">Ingresar</button>
                    </form>
                    <button id="mostrar-registro" class="boton-enlace" type="button">Crear una cuenta</button>
                </article>

                <article class="tarjeta">
                    <h2>Crear cuenta</h2>
                    <form id="form-registro">
                        <label for="registro-nombre">Nombre</label>
                        <input id="registro-nombre" name="nombre" maxlength="100" required>
                        <label for="registro-email">Correo</label>
                        <input id="registro-email" name="email" type="email" required>
                        <label for="registro-password">Contraseña (mínimo 8 caracteres)</label>
                        <input id="registro-password" name="password" type="password" minlength="8" required>
                        <button class="boton" type="submit">Registrarme</button>
                    </form>
                    <button id="mostrar-login" class="boton-enlace" type="button">¿Ya tiene cuenta? Ingresar</button>
                </article>
            </section>
        <?php else: ?>
            <section class="tarjeta">
                <h2>Exámenes</h2>
                <form id="form-examen" class="formulario-en-linea">
                    <input type="hidden" name="id" id="examen-id">
                    <div class="campo">
                        <label for="examen-titulo">Nombre del examen</label>
                        <input id="examen-titulo" name="titulo" maxlength="120" required>
                    </div>
                    <div class="campo campo-ancho">
                        <label for="examen-descripcion">Descripción (opcional)</label>
                        <input id="examen-descripcion" name="descripcion">
                    </div>
                    <button class="boton" type="submit" id="guardar-examen">Guardar examen</button>
                    <button class="boton boton-claro oculto" type="button" id="cancelar-examen">Cancelar</button>
                </form>
                <div id="lista-examenes" class="lista"></div>
            </section>

            <section id="seccion-preguntas" class="tarjeta oculto">
                <h2>Preguntas: <span id="titulo-examen-seleccionado"></span></h2>
                <form id="form-pregunta">
                    <input type="hidden" name="id" id="pregunta-id">
                    <label for="pregunta-texto">Texto de la pregunta</label>
                    <textarea id="pregunta-texto" name="texto" maxlength="1000" rows="3" required></textarea>
                    <div class="botones-formulario">
                        <button class="boton" type="submit" id="guardar-pregunta">Guardar pregunta</button>
                        <button class="boton boton-claro oculto" type="button" id="cancelar-pregunta">Cancelar</button>
                    </div>
                </form>
                <div id="lista-preguntas" class="lista"></div>

                <div class="sorteo">
                    <h3>Sortear preguntas</h3>
                    <form id="form-sorteo" class="formulario-en-linea">
                        <div class="campo">
                            <label for="cantidad-preguntas">Cantidad</label>
                            <input id="cantidad-preguntas" name="cantidad" type="number" min="1" value="1" required>
                        </div>
                        <button class="boton" type="submit">Sortear</button>
                    </form>
                    <ol id="resultado-sorteo"></ol>
                </div>
            </section>
        <?php endif ?>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/app.js" defer></script>
</body>
</html>
