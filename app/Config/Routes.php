<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->post('api/registro', 'Home::registrar');
$routes->post('api/login', 'Home::login');
$routes->post('api/logout', 'Home::logout');
$routes->get('api/examenes', 'Home::listarExamenes');
$routes->post('api/examenes/guardar', 'Home::guardarExamen');
$routes->post('api/examenes/eliminar', 'Home::eliminarExamen');
$routes->get('api/preguntas', 'Home::listarPreguntas');
$routes->post('api/preguntas/guardar', 'Home::guardarPregunta');
$routes->post('api/preguntas/eliminar', 'Home::eliminarPregunta');
$routes->post('api/preguntas/sortear', 'Home::sortearPreguntas');
