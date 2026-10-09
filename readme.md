# Banco de preguntas y exámenes

Proyecto sencillo para Programación III. Permite registrar usuarios, crear sus
propios exámenes y preguntas, y sortear una cantidad elegida de preguntas al azar
sin repetirlas.

## Datos para completar antes de entregar

- Institución: completar.
- Carrera: Tecnicatura Superior en Desarrollo de Software.
- Asignatura: Programación III.
- Autor/es: completar con nombre/s y apellido/s.

## Requisitos

- PHP 8.2 o superior con `mysqli`, `mbstring` e `intl`.
- MySQL o MariaDB.
- CodeIgniter 4 y sus dependencias de Composer.
- Conexión a Internet para cargar jQuery desde su CDN.

## Instalación básica en XAMPP

1. Copie el proyecto dentro de `C:\xampp\htdocs`.
2. Inicie Apache y MySQL desde el panel de XAMPP.
3. Abra phpMyAdmin, seleccione **Importar** y cargue `database.sql`. El script
   crea la base `proyecto_examenes`, un usuario de prueba y dos exámenes con
   preguntas.
4. la conexión MySQL en `.env` para usar la base `proyecto_examenes`
   (`database.default.hostname`, `database.default.database`,
   `database.default.username` y `database.default.password`). 
5. Instale las dependencias de CodeIgniter con Composer si todavía no están
   instaladas.
6. Abra `http://localhost/mi_proyecto_examenes/public/`.

## Uso

1. Para ver los datos que incluye el SQL, ingrese con `demo@examenes.com` y la
   contraseña `demo1234`.
2. También puede crear su propia cuenta desde **Crear cuenta**.
3. Cree un examen desde **Exámenes** o use uno de los ya cargados.
4. Seleccione **Ver preguntas** para agregar, editar o eliminar preguntas de ese
   examen.
5. Indique cuántas preguntas quiere y presione **Sortear**.

Cada usuario ve solamente sus exámenes. Las preguntas pertenecen a un examen y
el sorteo se limita a las preguntas de ese examen. Al eliminar un examen también
se eliminan sus preguntas. Los exámenes de prueba pertenecen al usuario
`demo@examenes.com`; no aparecen en las cuentas de otros usuarios.

## Integración PHP y JavaScript

Los formularios usan jQuery para enviar peticiones AJAX a las rutas de
`app/Config/Routes.php`. CodeIgniter valida los datos, consulta MySQL y responde
con JSON; JavaScript actualiza la pantalla sin recargarla.