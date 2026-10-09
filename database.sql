CREATE DATABASE IF NOT EXISTS proyecto_examenes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE proyecto_examenes;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS examenes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    descripcion TEXT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_examenes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS preguntas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    examen_id INT UNSIGNED NOT NULL,
    texto VARCHAR(1000) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_preguntas_examen (examen_id),
    CONSTRAINT fk_preguntas_examen
        FOREIGN KEY (examen_id) REFERENCES examenes (id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

START TRANSACTION;

INSERT INTO usuarios (nombre, email, password)
SELECT 'Usuario de prueba', 'demo@examenes.com',
       '$2y$10$Bn//mKgxy0tBq3cViksh3OJV/t9DT5REIdfawz6Fs6aHvmtoq5XY6'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'demo@examenes.com'
);

SET @usuario_demo = (
    SELECT id FROM usuarios WHERE email = 'demo@examenes.com' LIMIT 1
);

INSERT INTO examenes (usuario_id, titulo, descripcion)
SELECT @usuario_demo, 'Matemática básica', 'Preguntas de operaciones y conceptos básicos.'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM examenes
    WHERE usuario_id = @usuario_demo AND titulo = 'Matemática básica'
);

INSERT INTO examenes (usuario_id, titulo, descripcion)
SELECT @usuario_demo, 'Programación básica', 'Preguntas introductorias sobre programación.'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM examenes
    WHERE usuario_id = @usuario_demo AND titulo = 'Programación básica'
);

SET @examen_matematica = (
    SELECT id FROM examenes
    WHERE usuario_id = @usuario_demo AND titulo = 'Matemática básica'
    LIMIT 1
);

SET @examen_programacion = (
    SELECT id FROM examenes
    WHERE usuario_id = @usuario_demo AND titulo = 'Programación básica'
    LIMIT 1
);

INSERT INTO preguntas (examen_id, texto)
SELECT @examen_matematica, '¿Cuánto es 8 × 7?'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM preguntas
    WHERE examen_id = @examen_matematica AND texto = '¿Cuánto es 8 × 7?'
);

INSERT INTO preguntas (examen_id, texto)
SELECT @examen_matematica, '¿Cuál es la mitad de 100?'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM preguntas
    WHERE examen_id = @examen_matematica AND texto = '¿Cuál es la mitad de 100?'
);

INSERT INTO preguntas (examen_id, texto)
SELECT @examen_matematica, '¿Cuánto es 45 + 28?'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM preguntas
    WHERE examen_id = @examen_matematica AND texto = '¿Cuánto es 45 + 28?'
);

INSERT INTO preguntas (examen_id, texto)
SELECT @examen_programacion, '¿Para qué se utiliza una variable en un programa?'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM preguntas
    WHERE examen_id = @examen_programacion
      AND texto = '¿Para qué se utiliza una variable en un programa?'
);

INSERT INTO preguntas (examen_id, texto)
SELECT @examen_programacion, '¿Qué estructura permite repetir instrucciones?'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM preguntas
    WHERE examen_id = @examen_programacion
      AND texto = '¿Qué estructura permite repetir instrucciones?'
);

INSERT INTO preguntas (examen_id, texto)
SELECT @examen_programacion, '¿Qué es una condición en programación?'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM preguntas
    WHERE examen_id = @examen_programacion
      AND texto = '¿Qué es una condición en programación?'
);

COMMIT;
