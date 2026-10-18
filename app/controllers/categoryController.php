<?php

	namespace app\controllers;
	use app\models\mainModel;

	class categoryController extends mainModel{

		/*----------  Controlador registrar categoria  ----------*/
		public function registrarCategoriaControlador(){

			# 🔒 Solo admin
			$denegado = $this->requerirAdmin();
			if($denegado) return $denegado;

			# Almacenando datos#
		    $nombre=$this->limpiarCadena($_POST['categoria_nombre']);
		    

		    # Verificando campos obligatorios #
            if($nombre==""){
            	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}",$nombre)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NOMBRE no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }


		    # Verificando nombre #
		    $check_nombre = $this->ejecutarConsultaSegura(
				"SELECT categoria_nombre FROM categoria WHERE categoria_nombre = :nombre",
				['nombre' => $nombre]
			);
		    if($check_nombre->rowCount()>0){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NOMBRE ingresado ya se encuentra registrado, por favor elija otro",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }


		    $categoria_datos_reg=[
				[
					"campo_nombre"=>"categoria_nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$nombre
				],
				[
					"campo_nombre"=>"categoria_estatus",
					"campo_marcador"=>":Estatus",
					"campo_valor"=>1
				]
			];

			$registrar_categoria=$this->guardarDatos("categoria",$categoria_datos_reg);

			if($registrar_categoria->rowCount()==1){
				$alerta=[
					"tipo"=>"limpiar",
					"titulo"=>"Categoría registrada",
					"texto"=>"La categoría ".$nombre." se registro con exito",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No se pudo registrar la categoría, por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}


		/*----------  Controlador listar categoria  ----------*/
		public function listarCategoriaControlador($pagina,$registros,$url,$busqueda,$estatus="todos"){

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);

			$url=$this->limpiarCadena($url);

			// URL base para links (sin filtro ni página)
			$url_base = APP_URL.$url."/";

			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			// Validar estatus (por seguridad, solo permitir los 3 valores válidos)
			if(!in_array($estatus, ['todos','habilitadas','deshabilitadas'])){
				$estatus = 'todos';
			}

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			$inicio_int    = (int) $inicio;
			$registros_int = (int) $registros;

			// === Construir condición WHERE según filtro ===
			$where_estatus = "";
			$params_estatus = [];

			if($estatus == 'habilitadas'){
				$where_estatus = " AND categoria_estatus = :estatus_filtro ";
				$params_estatus['estatus_filtro'] = 1;
			}elseif($estatus == 'deshabilitadas'){
				$where_estatus = " AND categoria_estatus = :estatus_filtro ";
				$params_estatus['estatus_filtro'] = 0;
			}

			// === Consultas ===
			if(isset($busqueda) && $busqueda!=""){

				$param_busqueda = '%'.$busqueda.'%';

				$consulta_datos = "SELECT * FROM categoria 
				                   WHERE categoria_nombre LIKE :busqueda 
				                   $where_estatus
				                   ORDER BY categoria_nombre ASC 
				                   LIMIT $inicio_int, $registros_int";

				$consulta_total = "SELECT COUNT(categoria_id) FROM categoria 
				                   WHERE categoria_nombre LIKE :busqueda 
				                   $where_estatus";

				$params = array_merge(['busqueda' => $param_busqueda], $params_estatus);

			}else{

				$consulta_datos = "SELECT * FROM categoria 
				                   WHERE 1=1 
				                   $where_estatus
				                   ORDER BY categoria_nombre ASC 
				                   LIMIT $inicio_int, $registros_int";

				$consulta_total = "SELECT COUNT(categoria_id) FROM categoria 
				                   WHERE 1=1 
				                   $where_estatus";

				$params = $params_estatus;

			}

			$datos = $this->ejecutarConsultaSegura($consulta_datos, $params);
			$datos = $datos->fetchAll();

			$total = $this->ejecutarConsultaSegura($consulta_total, $params);
			$total = (int) $total->fetchColumn();

			$numeroPaginas =ceil($total/$registros);

			// === URL que incluye el filtro (para paginación) ===
			$url_filtro_actual = APP_URL.$url."/".$estatus."/";

			// === Detectar si es admin ===
			$es_admin = $this->esAdmin();

			// === Generar los botones de filtro ===
			// Detectar si estamos en modo búsqueda (para armar links correctos)
			if(isset($busqueda) && $busqueda!=""){
				$url_filtro_todos          = APP_URL.$url."/".$busqueda."/todos/";
				$url_filtro_habilitadas    = APP_URL.$url."/".$busqueda."/habilitadas/";
				$url_filtro_deshabilitadas = APP_URL.$url."/".$busqueda."/deshabilitadas/";
			}else{
				$url_filtro_todos          = APP_URL.$url."/todos/";
				$url_filtro_habilitadas    = APP_URL.$url."/habilitadas/";
				$url_filtro_deshabilitadas = APP_URL.$url."/deshabilitadas/";
			}

			$botones_filtro = '
			<div class="field is-grouped mb-5">
				<div class="control">
					<label class="label">Filtrar por estatus:</label>
				</div>
				<div class="control">
					<a href="'.$url_filtro_todos.'" class="button '.($estatus=='todos'?'is-link':'is-light').' is-rounded is-small">
						<i class="fas fa-list fa-fw"></i> &nbsp; Todas
					</a>
				</div>
				<div class="control">
					<a href="'.$url_filtro_habilitadas.'" class="button '.($estatus=='habilitadas'?'is-success':'is-light').' is-rounded is-small">
						<i class="fas fa-check-circle fa-fw"></i> &nbsp; Habilitadas
					</a>
				</div>
				<div class="control">
					<a href="'.$url_filtro_deshabilitadas.'" class="button '.($estatus=='deshabilitadas'?'is-danger':'is-light').' is-rounded is-small">
						<i class="fas fa-ban fa-fw"></i> &nbsp; Deshabilitadas
					</a>
				</div>
			</div>
			';

			$tabla.= $botones_filtro;

			// === Cabecera de la tabla: columnas según admin ===
			if($es_admin){
				$tabla.='
					<div class="table-container">
					<table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
						<thead>
							<tr>
								<th class="has-text-centered">#</th>
								<th class="has-text-centered">Nombre</th>
								<th class="has-text-centered">Estatus</th>
								<th class="has-text-centered">Productos</th>
								<th class="has-text-centered">Actualizar</th>
								<th class="has-text-centered">Habilitar / Deshabilitar</th>
							</tr>
						</thead>
						<tbody>
				';
			}else{
				$tabla.='
					<div class="table-container">
					<table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
						<thead>
							<tr>
								<th class="has-text-centered">#</th>
								<th class="has-text-centered">Nombre</th>
								<th class="has-text-centered">Estatus</th>
								<th class="has-text-centered">Productos</th>
							</tr>
						</thead>
						<tbody>
				';
			}

		    if($total>=1 && $pagina<=$numeroPaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				foreach($datos as $rows){

					// Determinar estatus visual
					if($rows['categoria_estatus'] == 1){
						$estatus_badge = '<span class="tag is-success">Habilitada</span>';
					}else{
						$estatus_badge = '<span class="tag is-danger">Deshabilitada</span>';
					}

					// Si es admin, mostrar botones de acción
					if($es_admin){

						if($rows['categoria_estatus'] == 1){
							$btn_accion = '
								<form class="FormularioAjax is-inline-block" action="'.APP_URL.'app/ajax/categoriaAjax.php" method="POST" autocomplete="off">
									<input type="hidden" name="modulo_categoria" value="deshabilitar">
									<input type="hidden" name="categoria_id" value="'.$rows['categoria_id'].'">
									<button type="submit" class="button is-warning is-rounded is-small" title="Deshabilitar categoría">
										<i class="fas fa-ban fa-fw"></i>
									</button>
								</form>
							';
						}else{
							$btn_accion = '
								<form class="FormularioAjax is-inline-block" action="'.APP_URL.'app/ajax/categoriaAjax.php" method="POST" autocomplete="off">
									<input type="hidden" name="modulo_categoria" value="habilitar">
									<input type="hidden" name="categoria_id" value="'.$rows['categoria_id'].'">
									<button type="submit" class="button is-success is-rounded is-small" title="Habilitar categoría">
										<i class="fas fa-check fa-fw"></i>
									</button>
								</form>
							';
						}

						$tabla.='
							<tr class="has-text-centered" >
								<td>'.$contador.'</td>
								<td>'.$rows['categoria_nombre'].'</td>
								<td>'.$estatus_badge.'</td>
								<td>
				                    <a href="'.APP_URL.'productCategory/'.$rows['categoria_id'].'/" class="button is-info is-rounded is-small">
				                    	<i class="fas fa-boxes fa-fw"></i>
				                    </a>
				                </td>
				                <td>
				                    <a href="'.APP_URL.'categoryUpdate/'.$rows['categoria_id'].'/" class="button is-success is-rounded is-small">
				                    	<i class="fas fa-sync fa-fw"></i>
				                    </a>
				                </td>
				                <td>
				                	'.$btn_accion.'
				                </td>
							</tr>
						';
					}else{
						// Vista para usuario NO admin (solo lectura)
						$tabla.='
							<tr class="has-text-centered" >
								<td>'.$contador.'</td>
								<td>'.$rows['categoria_nombre'].'</td>
								<td>'.$estatus_badge.'</td>
								<td>
				                    <a href="'.APP_URL.'productCategory/'.$rows['categoria_id'].'/" class="button is-info is-rounded is-small">
				                    	<i class="fas fa-boxes fa-fw"></i>
				                    </a>
				                </td>
							</tr>
						';
					}
					$contador++;
				}
				$pag_final=$contador-1;
			}else{
				$colspan = $es_admin ? 6 : 4;
				if($total>=1){
					$tabla.='
						<tr class="has-text-centered" >
			                <td colspan="'.$colspan.'">
			                    <a href="'.$url_filtro_actual.'1/" class="button is-link is-rounded is-small mt-4 mb-4">
			                        Haga clic acá para recargar el listado
			                    </a>
			                </td>
			            </tr>
					';
				}else{
					$tabla.='
						<tr class="has-text-centered" >
			                <td colspan="'.$colspan.'">
			                    No hay registros en el sistema
			                </td>
			            </tr>
					';
				}
			}

			$tabla.='</tbody></table></div>';

			### Paginacion ###
			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando categorías <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';

				// Paginador con URL que incluye el filtro
				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url_filtro_actual,7);
			}

			return $tabla;
		}


		/*----------  Controlador deshabilitar categoria  ----------*/
		public function deshabilitarCategoriaControlador(){

			# 🔒 Solo admin
			$denegado = $this->requerirAdmin();
			if($denegado) return $denegado;

			$id=$this->limpiarCadena($_POST['categoria_id']);

			# Verificando categoria #
		    $datos = $this->ejecutarConsultaSegura(
				"SELECT * FROM categoria WHERE categoria_id = :id",
				['id' => $id]
			);
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la categoría en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Verificando que esté habilitada #
		    if($datos['categoria_estatus'] == 0){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Categoría ya deshabilitada",
					"texto"=>"Esta categoría ya se encuentra deshabilitada en el sistema",
					"icono"=>"warning"
				];
				return json_encode($alerta);
		        exit();
		    }

		    # Deshabilitando categoría #
		    $categoria_datos_up=[
				[
					"campo_nombre"=>"categoria_estatus",
					"campo_marcador"=>":Estatus",
					"campo_valor"=>0
				]
			];

			$condicion=[
				"condicion_campo"=>"categoria_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("categoria",$categoria_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Categoría deshabilitada",
					"texto"=>"La categoría ".$datos['categoria_nombre']." ha sido deshabilitada correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido deshabilitar la categoría ".$datos['categoria_nombre'].", por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}


		/*----------  Controlador habilitar categoria  ----------*/
		public function habilitarCategoriaControlador(){

			# 🔒 Solo admin
			$denegado = $this->requerirAdmin();
			if($denegado) return $denegado;

			$id=$this->limpiarCadena($_POST['categoria_id']);

			# Verificando categoria #
		    $datos = $this->ejecutarConsultaSegura(
				"SELECT * FROM categoria WHERE categoria_id = :id",
				['id' => $id]
			);
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la categoría en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Verificando que esté deshabilitada #
		    if($datos['categoria_estatus'] == 1){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Categoría ya habilitada",
					"texto"=>"Esta categoría ya se encuentra habilitada en el sistema",
					"icono"=>"warning"
				];
				return json_encode($alerta);
		        exit();
		    }

		    # Habilitando categoría #
		    $categoria_datos_up=[
				[
					"campo_nombre"=>"categoria_estatus",
					"campo_marcador"=>":Estatus",
					"campo_valor"=>1
				]
			];

			$condicion=[
				"condicion_campo"=>"categoria_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("categoria",$categoria_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Categoría habilitada",
					"texto"=>"La categoría ".$datos['categoria_nombre']." ha sido habilitada correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido habilitar la categoría ".$datos['categoria_nombre'].", por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}


		/*----------  Controlador actualizar categoria  ----------*/
		public function actualizarCategoriaControlador(){

			# 🔒 Solo admin
			$denegado = $this->requerirAdmin();
			if($denegado) return $denegado;

			$id=$this->limpiarCadena($_POST['categoria_id']);

			# Verificando categoria #
		    $datos = $this->ejecutarConsultaSegura(
				"SELECT * FROM categoria WHERE categoria_id = :id",
				['id' => $id]
			);
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la categoría en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Almacenando datos#
		    $nombre=$this->limpiarCadena($_POST['categoria_nombre']);
		   

		    # Verificando campos obligatorios #
            if($nombre==""){
            	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No has llenado todos los campos que son obligatorios",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            # Verificando integridad de los datos #
		    if($this->verificarDatos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}",$nombre)){
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El NOMBRE no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }


		    # Verificando nombre #
		    if($datos['categoria_nombre']!=$nombre){
			    $check_nombre = $this->ejecutarConsultaSegura(
					"SELECT categoria_nombre FROM categoria WHERE categoria_nombre = :nombre",
					['nombre' => $nombre]
				);
			    if($check_nombre->rowCount()>0){
			    	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"El NOMBRE ingresado ya se encuentra registrado, por favor elija otro",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
			    }
		    }


		    $categoria_datos_up=[
				[
					"campo_nombre"=>"categoria_nombre",
					"campo_marcador"=>":Nombre",
					"campo_valor"=>$nombre
				]
			];

			$condicion=[
				"condicion_campo"=>"categoria_id",
				"condicion_marcador"=>":ID",
				"condicion_valor"=>$id
			];

			if($this->actualizarDatos("categoria",$categoria_datos_up,$condicion)){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Categoría actualizada",
					"texto"=>"Los datos de la categoría ".$datos['categoria_nombre']." se actualizaron correctamente",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido actualizar los datos de la categoría ".$datos['categoria_nombre'].", por favor intente nuevamente",
					"icono"=>"error"
				];
			}

			return json_encode($alerta);
		}

	}
