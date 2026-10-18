<?php
	
	namespace app\models;
	use \PDO;

	if(file_exists(__DIR__."/../../config/server.php")){
		require_once __DIR__."/../../config/server.php";
	}

	class mainModel{

		private $server=DB_SERVER;
		private $db=DB_NAME;
		private $user=DB_USER;
		private $pass=DB_PASS;
		private $conexion_pdo = null;


		/*=========================================================
		  CONEXIÓN A BD (persistente)
		  - Reutiliza la misma conexión durante toda la petición
		  - Permite transacciones nativas PDO
		=========================================================*/
		protected function conectar(){
			if($this->conexion_pdo === null){
				$this->conexion_pdo = new PDO(
					"mysql:host=".$this->server.";dbname=".$this->db.";charset=utf8mb4",
					$this->user,
					$this->pass,
					[
						PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
						PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
						PDO::ATTR_EMULATE_PREPARES   => false,
					]
				);
			}
			return $this->conexion_pdo;
		}


		/*=========================================================
		  MÉTODOS ORIGINALES
		=========================================================*/

		/*----------  Funcion ejecutar consultas  ----------*/
		protected function ejecutarConsulta($consulta){
			$sql=$this->conectar()->prepare($consulta);
			$sql->execute();
			return $sql;
		}


		/*----------  Funcion limpiar cadenas  ----------*/
		public function limpiarCadena($cadena){

			$palabras=["<script>","</script>","<script src","<script type=","SELECT * FROM","SELECT "," SELECT ","DELETE FROM","INSERT INTO","DROP TABLE","DROP DATABASE","TRUNCATE TABLE","SHOW TABLES","SHOW DATABASES","<?php","?>","--","^","<",">","==",";","::"];

			$cadena=trim($cadena);
			$cadena=stripslashes($cadena);

			foreach($palabras as $palabra){
				$cadena=str_ireplace($palabra, "", $cadena);
			}

			$cadena=trim($cadena);
			$cadena=stripslashes($cadena);

			return $cadena;
		}


		/*---------- Funcion verificar datos (expresion regular) ----------*/
		protected function verificarDatos($filtro,$cadena){
			if(preg_match("/^".$filtro."$/", $cadena)){
				return false;
            }else{
                return true;
            }
		}


		/*----------  Funcion para ejecutar una consulta INSERT preparada  ----------*/
		protected function guardarDatos($tabla,$datos){

			$query="INSERT INTO $tabla (";

			$C=0;
			foreach ($datos as $clave){
				if($C>=1){ $query.=","; }
				$query.=$clave["campo_nombre"];
				$C++;
			}
			
			$query.=") VALUES(";

			$C=0;
			foreach ($datos as $clave){
				if($C>=1){ $query.=","; }
				$query.=$clave["campo_marcador"];
				$C++;
			}

			$query.=")";
			$sql=$this->conectar()->prepare($query);

			foreach ($datos as $clave){
				$sql->bindParam($clave["campo_marcador"],$clave["campo_valor"]);
			}

			$sql->execute();

			return $sql;
		}


		/*---------- Funcion seleccionar datos ----------*/
        public function seleccionarDatos($tipo,$tabla,$campo,$id, $soloActivo=false){
			$tipo=$this->limpiarCadena($tipo);
			$tabla=$this->limpiarCadena($tabla);
			$campo=$this->limpiarCadena($campo);
			$id=$this->limpiarCadena($id);

            if($tipo=="Unico"){
                $sql=$this->conectar()->prepare("SELECT * FROM $tabla WHERE $campo=:ID");
                $sql->bindParam(":ID",$id);
            }elseif($tipo=="Normal"){
            	if ($soloActivo==true) {
            		$sql=$this->conectar()->prepare("SELECT $campo FROM $tabla WHERE estatus=1");
            	}else{
                $sql=$this->conectar()->prepare("SELECT $campo FROM $tabla");
            }
            }
            $sql->execute();

            return $sql;
		}


		/*----------  Funcion para ejecutar una consulta UPDATE preparada  ----------*/
		protected function actualizarDatos($tabla,$datos,$condicion){

			$query="UPDATE $tabla SET ";

			$C=0;
			foreach ($datos as $clave){
				if($C>=1){ $query.=","; }
				$query.=$clave["campo_nombre"]."=".$clave["campo_marcador"];
				$C++;
			}

			$query.=" WHERE ".$condicion["condicion_campo"]."=".$condicion["condicion_marcador"];

			$sql=$this->conectar()->prepare($query);

			foreach ($datos as $clave){
				$sql->bindParam($clave["campo_marcador"],$clave["campo_valor"]);
			}

			$sql->bindParam($condicion["condicion_marcador"],$condicion["condicion_valor"]);

			$sql->execute();

			return $sql;
		}


		/*---------- Funcion eliminar registro ----------*/
        protected function eliminarRegistro($tabla,$campo,$id){
            $sql=$this->conectar()->prepare("DELETE FROM $tabla WHERE $campo=:id");
            $sql->bindParam(":id",$id);
            $sql->execute();
            
            return $sql;
        }

		/*---------- Funcion anular registro ----------*/
		protected function anularRegistro($tabla,$campo,$id){
			$sql=$this->conectar()->prepare("UPDATE $tabla SET anulado = 1 WHERE $campo=:id");
			$sql->bindParam(":id",$id);
			$sql->execute();
			
			return $sql;
		}
		

		/*---------- Paginador de tablas ----------*/
		protected function paginadorTablas($pagina,$numeroPaginas,$url,$botones){
	        $tabla='<nav class="pagination is-centered is-rounded" role="navigation" aria-label="pagination">';

	        if($pagina<=1){
	            $tabla.='
	            <a class="pagination-previous is-disabled" disabled ><i class="fas fa-arrow-alt-circle-left"></i> &nbsp; Anterior</a>
	            <ul class="pagination-list">
	            ';
	        }else{
	            $tabla.='
	            <a class="pagination-previous" href="'.$url.($pagina-1).'/"><i class="fas fa-arrow-alt-circle-left"></i> &nbsp; Anterior</a>
	            <ul class="pagination-list">
	                <li><a class="pagination-link" href="'.$url.'1/">1</a></li>
	                <li><span class="pagination-ellipsis">&hellip;</span></li>
	            ';
	        }


	        $ci=0;
	        for($i=$pagina; $i<=$numeroPaginas; $i++){

	            if($ci>=$botones){
	                break;
	            }

	            if($pagina==$i){
	                $tabla.='<li><a class="pagination-link is-current" href="'.$url.$i.'/">'.$i.'</a></li>';
	            }else{
	                $tabla.='<li><a class="pagination-link" href="'.$url.$i.'/">'.$i.'</a></li>';
	            }

	            $ci++;
	        }


	        if($pagina==$numeroPaginas){
	            $tabla.='
	            </ul>
	            <a class="pagination-next is-disabled" disabled ><i class="fas fa-arrow-alt-circle-right"></i> &nbsp; Siguiente</a>
	            ';
	        }else{
	            $tabla.='
	                <li><span class="pagination-ellipsis">&hellip;</span></li>
	                <li><a class="pagination-link" href="'.$url.$numeroPaginas.'/">'.$numeroPaginas.'</a></li>
	            </ul>
	            <a class="pagination-next" href="'.$url.($pagina+1).'/"><i class="fas fa-arrow-alt-circle-right"></i> &nbsp; Siguiente</a>
	            ';
	        }

	        $tabla.='</nav>';
	        return $tabla;
	    }


	    /*----------  Funcion generar select ----------*/
		public function generarSelect($datos,$campo_db){
			$check_select='';
			$text_select='';
			$count_select=1;
			$select='';
			foreach($datos as $row){

				if($campo_db==$row){
					$check_select='selected=""';
					$text_select=' (Actual)';
				}

				$select.='<option value="'.$row.'" '.$check_select.'>'.$count_select.' - '.$row.$text_select.'</option>';

				$check_select='';
				$text_select='';
				$count_select++;
			}
			return $select;
		}

		/*----------  Funcion generar codigos aleatorios  ----------*/
		protected function generarCodigoAleatorio($longitud,$correlativo){
			$codigo="";
			$caracter="Letra";
			for($i=1; $i<=$longitud; $i++){
				if($caracter=="Letra"){
					$letra_aleatoria=chr(rand(ord("a"),ord("z")));
					$letra_aleatoria=strtoupper($letra_aleatoria);
					$codigo.=$letra_aleatoria;
					$caracter="Numero";
				}else{
					$numero_aleatorio=rand(0,9);
					$codigo.=$numero_aleatorio;
					$caracter="Letra";
				}
			}
			return $codigo."-".$correlativo;
		}


		/*----------  Limitar cadenas de texto  ----------*/
		public function limitarCadena($cadena,$limite,$sufijo){
			if(strlen($cadena)>$limite){
				return substr($cadena,0,$limite).$sufijo;
			}else{
				return $cadena;
			}
		}


		/*=========================================================
		  ⚡ MÉTODOS NUEVOS SEGUROS (Fase 1)
		=========================================================*/


		/*----------  Ejecutar consulta preparada SEGURA  ----------*/
		protected function ejecutarConsultaSegura($consulta, $params = []){
			try {
				$sql = $this->conectar()->prepare($consulta);

				foreach ($params as $clave => $valor) {
					if (is_int($valor)) {
						$tipo = PDO::PARAM_INT;
					} elseif (is_bool($valor)) {
						$tipo = PDO::PARAM_BOOL;
					} elseif (is_null($valor)) {
						$tipo = PDO::PARAM_NULL;
					} else {
						$tipo = PDO::PARAM_STR;
					}

					$marcador = (strpos($clave, ':') === 0) ? $clave : ':'.$clave;
					$sql->bindValue($marcador, $valor, $tipo);
				}

				$sql->execute();
				return $sql;

			} catch (PDOException $e) {
				error_log("[SQL ERROR] ".$e->getMessage()." | Query: ".$consulta);
				return (new PDO("sqlite::memory:"))->query("SELECT 1 WHERE 0");
			}
		}


		/*----------  INSERT seguro (versión con array asociativo)  ----------*/
		protected function insertarSeguro($tabla, $datos){
			if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabla)) {
				throw new \InvalidArgumentException("Nombre de tabla inválido: $tabla");
			}

			$columnas   = array_keys($datos);
			$marcadores = array_map(fn($c) => ':'.$c, $columnas);

			$query = "INSERT INTO $tabla (".implode(',', $columnas).") 
			          VALUES (".implode(',', $marcadores).")";

			$sql = $this->conectar()->prepare($query);

			foreach ($datos as $col => $val) {
				$tipo = is_int($val) ? PDO::PARAM_INT : (is_null($val) ? PDO::PARAM_NULL : PDO::PARAM_STR);
				$sql->bindValue(':'.$col, $val, $tipo);
			}

			$sql->execute();
			return $sql;
		}


		/*----------  UPDATE seguro (versión con array asociativo)  ----------*/
		protected function actualizarSeguro($tabla, $datos, $condicion){
			if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabla)) {
				throw new \InvalidArgumentException("Nombre de tabla inválido: $tabla");
			}

			$sets = [];
			foreach (array_keys($datos) as $col) {
				$sets[] = "$col = :set_$col";
			}

			$wheres = [];
			foreach (array_keys($condicion) as $col) {
				$wheres[] = "$col = :where_$col";
			}

			$query = "UPDATE $tabla SET ".implode(',', $sets)." WHERE ".implode(' AND ', $wheres);
			$sql = $this->conectar()->prepare($query);

			foreach ($datos as $col => $val) {
				$tipo = is_int($val) ? PDO::PARAM_INT : (is_null($val) ? PDO::PARAM_NULL : PDO::PARAM_STR);
				$sql->bindValue(':set_'.$col, $val, $tipo);
			}
			foreach ($condicion as $col => $val) {
				$tipo = is_int($val) ? PDO::PARAM_INT : (is_null($val) ? PDO::PARAM_NULL : PDO::PARAM_STR);
				$sql->bindValue(':where_'.$col, $val, $tipo);
			}

			$sql->execute();
			return $sql;
		}


		/*----------  DELETE seguro (versión con array asociativo)  ----------*/
		protected function eliminarSeguro($tabla, $condicion){
			if (!preg_match('/^[a-zA-Z0-9_]+$/', $tabla)) {
				throw new \InvalidArgumentException("Nombre de tabla inválido: $tabla");
			}

			$wheres = [];
			foreach (array_keys($condicion) as $col) {
				$wheres[] = "$col = :where_$col";
			}

			$query = "DELETE FROM $tabla WHERE ".implode(' AND ', $wheres);
			$sql = $this->conectar()->prepare($query);

			foreach ($condicion as $col => $val) {
				$tipo = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
				$sql->bindValue(':where_'.$col, $val, $tipo);
			}

			$sql->execute();
			return $sql;
		}


		/*=========================================================
		  MÉTODO PÚBLICO — Listar categorías activas
		=========================================================*/
		public function listarCategoriasActivas(){
			return $this->ejecutarConsultaSegura(
				"SELECT * FROM categoria WHERE categoria_estatus = 1 ORDER BY categoria_nombre ASC"
			);
		}


		/*=========================================================
		  🆕 HELPERS DE PERMISOS
		=========================================================*/

		/*----------  Verificar si el usuario actual es admin  ----------*/
		public function esAdmin(){
			return isset($_SESSION['admin']) && $_SESSION['admin'] == 1;
		}

		/*----------  Denegar acceso si no es admin  ----------*/
		protected function requerirAdmin(){
			if(!$this->esAdmin()){
				return json_encode([
					"tipo"   => "simple",
					"titulo" => "Acceso denegado",
					"texto"  => "No tienes permisos para realizar esta acción",
					"icono"  => "error"
				]);
			}
			return null;
		}

		/*----------  Redirigir si no es admin (para vistas)  ----------*/
		public function bloquearSiNoEsAdmin(){
			if(!$this->esAdmin()){
				echo '
				<div class="container is-fluid mb-6">
					<h1 class="title">Acceso restringido</h1>
				</div>
				<div class="container pb-6 pt-6">
					<article class="message is-danger">
						<div class="message-header">
							<p>¡Ocurrió un error!</p>
						</div>
						<div class="message-body has-text-centered">
							<i class="fas fa-lock fa-2x"></i><br><br>
							<strong>No tienes permisos para realizar esta acción</strong>
							<br><br>
							<a href="'.APP_URL.'dashboard/" class="button is-link is-rounded">Volver al inicio</a>
						</div>
					</article>
				</div>
				';
				return true;
			}
			return false;
		}

	    
	}
