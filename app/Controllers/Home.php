<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    public function index(): string
    {
        return view('examenes', [
            'usuario' => session()->get('usuario_nombre'),
        ]);
    }

    public function registrar(): ResponseInterface
    {
        $nombre = $this->postString('nombre');
        $email = strtolower($this->postString('email'));
        $password = $this->postString('password', false);

        if ($nombre === '' || mb_strlen($nombre) > 100 || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['ok' => false, 'mensaje' => 'Ingrese un nombre y un correo válidos.'], 400);
        }

        if (strlen($password) < 8 || strlen($password) > 72) {
            return $this->json(['ok' => false, 'mensaje' => 'La contraseña debe tener entre 8 y 72 caracteres.'], 400);
        }

        $db = db_connect();
        $existente = $db->table('usuarios')->where('email', $email)->get()->getRowArray();

        if ($existente !== null) {
            return $this->json(['ok' => false, 'mensaje' => 'Ya existe una cuenta con ese correo.'], 409);
        }

        $db->table('usuarios')->insert([
            'nombre' => $nombre,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        return $this->json(['ok' => true]);
    }

    public function login(): ResponseInterface
    {
        $email = strtolower($this->postString('email'));
        $password = $this->postString('password', false);
        $usuario = db_connect()->table('usuarios')->where('email', $email)->get()->getRowArray();

        if ($usuario === null || ! password_verify($password, $usuario['password'])) {
            return $this->json(['ok' => false, 'mensaje' => 'Correo o contraseña incorrectos.'], 401);
        }

        session()->regenerate();
        session()->set([
            'usuario_id' => (int) $usuario['id'],
            'usuario_nombre' => $usuario['nombre'],
        ]);

        return $this->json(['ok' => true]);
    }

    public function logout(): ResponseInterface
    {
        session()->destroy();

        return $this->json(['ok' => true]);
    }

    public function listarExamenes(): ResponseInterface
    {
        $usuarioId = $this->usuarioId();

        if ($usuarioId === null) {
            return $this->noAutorizado();
        }

        $examenes = db_connect()->table('examenes')
            ->where('usuario_id', $usuarioId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->json(['ok' => true, 'examenes' => $examenes]);
    }

    public function guardarExamen(): ResponseInterface
    {
        $usuarioId = $this->usuarioId();

        if ($usuarioId === null) {
            return $this->noAutorizado();
        }

        $id = $this->postId('id');
        $titulo = $this->postString('titulo');
        $descripcion = $this->postString('descripcion');

        if ($titulo === '' || mb_strlen($titulo) > 120) {
            return $this->json(['ok' => false, 'mensaje' => 'El título es obligatorio y debe tener hasta 120 caracteres.'], 400);
        }

        $db = db_connect();
        $datos = [
            'titulo' => $titulo,
            'descripcion' => $descripcion !== '' ? $descripcion : null,
        ];

        if ($id !== null) {
            if (! $this->examenPerteneceA($id, $usuarioId)) {
                return $this->json(['ok' => false, 'mensaje' => 'No se encontró el examen.'], 404);
            }

            $actualizado = $db->table('examenes')
                ->where('id', $id)
                ->where('usuario_id', $usuarioId)
                ->update($datos);

            if (! $actualizado) {
                return $this->json(['ok' => false, 'mensaje' => 'No se encontró el examen.'], 404);
            }
        } else {
            $datos['usuario_id'] = $usuarioId;
            $db->table('examenes')->insert($datos);
        }

        return $this->json(['ok' => true]);
    }

    public function eliminarExamen(): ResponseInterface
    {
        $usuarioId = $this->usuarioId();
        $id = $this->postId('id');

        if ($usuarioId === null) {
            return $this->noAutorizado();
        }

        if ($id === null || ! $this->examenPerteneceA($id, $usuarioId)) {
            return $this->json(['ok' => false, 'mensaje' => 'No se encontró el examen.'], 404);
        }

        db_connect()->table('examenes')->where('id', $id)->delete();

        return $this->json(['ok' => true]);
    }

    public function listarPreguntas(): ResponseInterface
    {
        $usuarioId = $this->usuarioId();
        $examenId = $this->getId('examen_id');

        if ($usuarioId === null) {
            return $this->noAutorizado();
        }

        if ($examenId === null || ! $this->examenPerteneceA($examenId, $usuarioId)) {
            return $this->json(['ok' => false, 'mensaje' => 'No se encontró el examen.'], 404);
        }

        $preguntas = db_connect()->table('preguntas')
            ->where('examen_id', $examenId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return $this->json(['ok' => true, 'preguntas' => $preguntas]);
    }

    public function guardarPregunta(): ResponseInterface
    {
        $usuarioId = $this->usuarioId();
        $examenId = $this->postId('examen_id');
        $id = $this->postId('id');
        $texto = $this->postString('texto');

        if ($usuarioId === null) {
            return $this->noAutorizado();
        }

        if ($examenId === null || ! $this->examenPerteneceA($examenId, $usuarioId)) {
            return $this->json(['ok' => false, 'mensaje' => 'No se encontró el examen.'], 404);
        }

        if ($texto === '' || mb_strlen($texto) > 1000) {
            return $this->json(['ok' => false, 'mensaje' => 'La pregunta es obligatoria y debe tener hasta 1000 caracteres.'], 400);
        }

        $db = db_connect();
        $datos = ['texto' => $texto];

        if ($id !== null) {
            $existe = $db->table('preguntas')
                ->where('id', $id)
                ->where('examen_id', $examenId)
                ->countAllResults() > 0;

            if (! $existe) {
                return $this->json(['ok' => false, 'mensaje' => 'No se encontró la pregunta.'], 404);
            }

            $actualizado = $db->table('preguntas')
                ->where('id', $id)
                ->where('examen_id', $examenId)
                ->update($datos);

            if (! $actualizado) {
                return $this->json(['ok' => false, 'mensaje' => 'No se encontró la pregunta.'], 404);
            }
        } else {
            $datos['examen_id'] = $examenId;
            $db->table('preguntas')->insert($datos);
        }

        return $this->json(['ok' => true]);
    }

    public function eliminarPregunta(): ResponseInterface
    {
        $usuarioId = $this->usuarioId();
        $id = $this->postId('id');
        $examenId = $this->postId('examen_id');

        if ($usuarioId === null) {
            return $this->noAutorizado();
        }

        if (
            $id === null
            || $examenId === null
            || ! $this->examenPerteneceA($examenId, $usuarioId)
        ) {
            return $this->json(['ok' => false, 'mensaje' => 'No se encontró la pregunta.'], 404);
        }

        $db = db_connect();
        $existe = $db->table('preguntas')
            ->where('id', $id)
            ->where('examen_id', $examenId)
            ->countAllResults() > 0;

        if (! $existe) {
            return $this->json(['ok' => false, 'mensaje' => 'No se encontró la pregunta.'], 404);
        }

        $db->table('preguntas')
            ->where('id', $id)
            ->where('examen_id', $examenId)
            ->delete();

        return $this->json(['ok' => true]);
    }

    public function sortearPreguntas(): ResponseInterface
    {
        $usuarioId = $this->usuarioId();
        $examenId = $this->postId('examen_id');
        $cantidad = $this->postId('cantidad');

        if ($usuarioId === null) {
            return $this->noAutorizado();
        }

        if ($examenId === null || ! $this->examenPerteneceA($examenId, $usuarioId)) {
            return $this->json(['ok' => false, 'mensaje' => 'No se encontró el examen.'], 404);
        }

        if ($cantidad === null || $cantidad < 1) {
            return $this->json(['ok' => false, 'mensaje' => 'Elija una cantidad válida de preguntas.'], 400);
        }

        $preguntas = db_connect()->table('preguntas')
            ->where('examen_id', $examenId)
            ->get()
            ->getResultArray();

        if ($cantidad > count($preguntas)) {
            return $this->json([
                'ok' => false,
                'mensaje' => 'El examen solo tiene ' . count($preguntas) . ' preguntas.',
            ], 400);
        }

        shuffle($preguntas);

        return $this->json([
            'ok' => true,
            'preguntas' => array_slice($preguntas, 0, $cantidad),
        ]);
    }

    private function postString(string $key, bool $trim = true): string
    {
        $value = $this->request->getPost($key);

        if (! is_string($value)) {
            return '';
        }

        return $trim ? trim($value) : $value;
    }

    private function postId(string $key): ?int
    {
        $value = $this->request->getPost($key);

        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT) ?: null;
    }

    private function getId(string $key): ?int
    {
        $value = $this->request->getGet($key);

        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT) ?: null;
    }

    private function usuarioId(): ?int
    {
        $id = session()->get('usuario_id');

        return is_numeric($id) ? (int) $id : null;
    }

    private function examenPerteneceA(int $examenId, int $usuarioId): bool
    {
        return db_connect()->table('examenes')
            ->where('id', $examenId)
            ->where('usuario_id', $usuarioId)
            ->countAllResults() > 0;
    }

    private function noAutorizado(): ResponseInterface
    {
        return $this->json(['ok' => false, 'mensaje' => 'Inicie sesión para continuar.'], 401);
    }

    private function json(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
