<div class="container is-fluid mb-6">
	<h1 class="title">Categorías</h1>
	<h2 class="subtitle"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de categorías</h2>
</div>
<div class="container pb-6 pt-6">

	<div class="form-rest mb-6 mt-6"></div>

	<?php
		use app\controllers\categoryController;

		$insCategoria = new categoryController();

		// Recuperar filtro desde la URL (por defecto "todos")
		$filtro = isset($url[1]) && in_array($url[1], ['todos','habilitadas','deshabilitadas']) ? $url[1] : 'todos';

		// Recuperar página desde la URL (por defecto 1)
		$pagina = isset($url[2]) && is_numeric($url[2]) ? (int)$url[2] : 1;

		echo $insCategoria->listarCategoriaControlador($pagina,15,$url[0],"",$filtro);
	?>
</div>
