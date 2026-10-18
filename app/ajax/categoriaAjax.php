<?php
	
	require_once "../../config/app.php";
	require_once "../views/inc/session_start.php";
	require_once "../../autoload.php";
	
	use app\controllers\categoryController;

	if(isset($_POST['modulo_categoria'])){

		$insCategory = new categoryController();

		if($_POST['modulo_categoria']=="registrar"){
			echo $insCategory->registrarCategoriaControlador();
		}

		if($_POST['modulo_categoria']=="actualizar"){
			echo $insCategory->actualizarCategoriaControlador();
		}

		if($_POST['modulo_categoria']=="deshabilitar"){
			echo $insCategory->deshabilitarCategoriaControlador();
		}

		if($_POST['modulo_categoria']=="habilitar"){
			echo $insCategory->habilitarCategoriaControlador();
		}
		
	}else{
		session_destroy();
		header("Location: ".APP_URL."login/");
	}
