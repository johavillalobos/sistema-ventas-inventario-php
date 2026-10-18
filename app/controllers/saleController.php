<?php

	namespace app\controllers;
	use app\models\mainModel;

	class saleController extends mainModel{

		/*---------- Controlador buscar codigo de producto ----------*/
        public function buscarCodigoVentaControlador(){

            /*== Recuperando codigo de busqueda ==*/
			$producto=$this->limpiarCadena($_POST['buscar_codigo']);

			/*== Comprobando que no este vacio el campo ==*/
			if($producto==""){
				return '
				<article class="message is-warning mt-4 mb-4">
					 <div class="message-header">
					    <p>¡Ocurrio un error inesperado!</p>
					 </div>
				    <div class="message-body has-text-centered">
				    	<i class="fas fa-exclamation-triangle fa-2x"></i><br>
						Debes de introducir el Nombre, Marca o Modelo del producto
				    </div>
				</article>';
				exit();
            }

            /*== Seleccionando productos en la DB ==*/
            $param_producto = '%'.$producto.'%';

            $datos_productos = $this->ejecutarConsultaSegura(
                "SELECT * FROM producto 
                 WHERE (producto_nombre LIKE :producto1 OR producto_marca LIKE :producto2) 
                 ORDER BY producto_nombre ASC",
                [
                    'producto1' => $param_producto,
                    'producto2' => $param_producto,
                ]
            );

            if($datos_productos->rowCount()>=1){

				$datos_productos=$datos_productos->fetchAll();

				$tabla='<div class="table-container mb-6"><table class="table is-striped is-narrow is-hoverable is-fullwidth"><tbody>';

				foreach($datos_productos as $rows){
					$tabla.='
					<tr class="has-text-left" >
                        <td><i class="fas fa-box fa-fw"></i> &nbsp; '.$rows['producto_nombre'].'</td>
                        <td class="has-text-centered">
                            <button type="button" class="button is-link is-rounded is-small" onclick="agregar_codigo(\''.$rows['producto_codigo'].'\')"><i class="fas fa-plus-circle"></i></button>
                        </td>
                    </tr>
                    ';
				}

				$tabla.='</tbody></table></div>';
				return $tabla;
			}else{
				return '<article class="message is-warning mt-4 mb-4">
					 <div class="message-header">
					    <p>¡Ocurrio un error inesperado!</p>
					 </div>
				    <div class="message-body has-text-centered">
				    	<i class="fas fa-exclamation-triangle fa-2x"></i><br>
						No hemos encontrado ningún producto en el sistema que coincida con <strong>“'.$producto.'”
				    </div>
				</article>';

				exit();
			}
        }


        /*---------- Controlador agregar producto a venta ----------*/
        public function agregarProductoCarritoControlador(){

            /*== Recuperando codigo del producto ==*/
            $codigo=$this->limpiarCadena($_POST['producto_codigo']);

            if($codigo==""){
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"Debes de introducir el código de barras del producto",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            /*== Verificando integridad de los datos ==*/
            if($this->verificarDatos("[a-zA-Z0-9- ]{1,70}",$codigo)){
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"El código de barras no coincide con el formato solicitado",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            /*== Comprobando producto en la DB ==*/
            $check_producto = $this->ejecutarConsultaSegura(
                "SELECT * FROM producto WHERE producto_codigo = :codigo",
                ['codigo' => $codigo]
            );
            if($check_producto->rowCount()<=0){
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el producto con código de barras : '$codigo'",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }else{
                $campos=$check_producto->fetch();
            }

            /*== Codigo de producto ==*/
            $codigo=$campos['producto_codigo'];

            if(empty($_SESSION['datos_producto_venta'][$codigo])){

                $detalle_cantidad=1;

                $stock_total=$campos['producto_stock_total']-$detalle_cantidad;

                if($stock_total<0){
                    $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Lo sentimos, no hay existencias disponibles del producto seleccionado",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
                }

                $detalle_total=$detalle_cantidad*$campos['producto_precio_venta'];
                $detalle_total=number_format($detalle_total,MONEDA_DECIMALES,'.','');

                $_SESSION['datos_producto_venta'][$codigo]=[
                    "producto_id"=>$campos['producto_id'],
					"producto_codigo"=>$campos['producto_codigo'],
					"producto_stock_total"=>$stock_total,
					"producto_stock_total_old"=>$campos['producto_stock_total'],
                    "venta_detalle_precio_compra"=>$campos['producto_precio_compra'],
                    "venta_detalle_precio_venta"=>$campos['producto_precio_venta'],
                    "venta_detalle_cantidad"=>1,
                    "venta_detalle_total"=>$detalle_total,
                    "venta_detalle_descripcion"=>$campos['producto_nombre']
                ];

                $_SESSION['alerta_producto_agregado']="Se agrego <strong>".$campos['producto_nombre']."</strong> a la venta";
            }else{
                $detalle_cantidad=($_SESSION['datos_producto_venta'][$codigo]['venta_detalle_cantidad'])+1;

                $stock_total=$campos['producto_stock_total']-$detalle_cantidad;

                if($stock_total<0){
                    $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Lo sentimos, no hay existencias disponibles del producto seleccionado",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
                }

                $detalle_total=$detalle_cantidad*$campos['producto_precio_venta'];
                $detalle_total=number_format($detalle_total,MONEDA_DECIMALES,'.','');

                $_SESSION['datos_producto_venta'][$codigo]=[
                    "producto_id"=>$campos['producto_id'],
					"producto_codigo"=>$campos['producto_codigo'],
					"producto_stock_total"=>$stock_total,
					"producto_stock_total_old"=>$campos['producto_stock_total'],
                    "venta_detalle_precio_compra"=>$campos['producto_precio_compra'],
                    "venta_detalle_precio_venta"=>$campos['producto_precio_venta'],
                    "venta_detalle_cantidad"=>$detalle_cantidad,
                    "venta_detalle_total"=>$detalle_total,
                    "venta_detalle_descripcion"=>$campos['producto_nombre']
                ];

                $_SESSION['alerta_producto_agregado']="Se agrego +1 <strong>".$campos['producto_nombre']."</strong> a la venta. Total en carrito: <strong>$detalle_cantidad</strong>";
            }

            $alerta=[
				"tipo"=>"redireccionar",
				"url"=>APP_URL."saleNew/"
			];

			return json_encode($alerta);
        }


        /*---------- Controlador remover producto de venta ----------*/
        public function removerProductoCarritoControlador(){

            /*== Recuperando codigo del producto ==*/
            $codigo=$this->limpiarCadena($_POST['producto_codigo']);

            unset($_SESSION['datos_producto_venta'][$codigo]);

            if(empty($_SESSION['datos_producto_venta'][$codigo])){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"¡Producto removido!",
					"texto"=>"El producto se ha removido de la venta",
					"icono"=>"success"
				];
				
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido remover el producto, por favor intente nuevamente",
					"icono"=>"error"
				];
            }
            return json_encode($alerta);
        }

		/*---------- Controlador remover todos los productos de la venta ----------*/
		public function removerCarritoControlador(){

			unset($_SESSION['datos_producto_venta']);

			if(empty($_SESSION['datos_producto_venta'])){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"¡Productos removidos!",
					"texto"=>"Los productos se han removido de la venta",
					"icono"=>"success"
				];
				
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido remover los productos, por favor intente nuevamente",
					"icono"=>"error"
				];
			}
			return json_encode($alerta);
		}
	

        /*---------- Controlador actualizar producto de venta ----------*/
        public function actualizarProductoCarritoControlador(){

            /*== Recuperando codigo & cantidad del producto ==*/
            $codigo=$this->limpiarCadena($_POST['producto_codigo']);
            $cantidad=$this->limpiarCadena($_POST['producto_cantidad']);

            /*== comprobando campos vacios ==*/
            if($codigo=="" || $cantidad==""){
            	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No podemos actualizar la cantidad de productos debido a que faltan algunos parámetros de configuración",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            /*== comprobando cantidad de productos ==*/
            if($cantidad<=0){
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"Debes de introducir una cantidad mayor a 0",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }

            /*== Comprobando producto en la DB ==*/
            $check_producto = $this->ejecutarConsultaSegura(
                "SELECT * FROM producto WHERE producto_codigo = :codigo",
                ['codigo' => $codigo]
            );
            if($check_producto->rowCount()<=0){
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el producto con código de barras : '$codigo'",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
            }else{
                $campos=$check_producto->fetch();
            }

            /*== comprobando producto en carrito ==*/
            if(!empty($_SESSION['datos_producto_venta'][$codigo])){

                if($_SESSION['datos_producto_venta'][$codigo]["venta_detalle_cantidad"]==$cantidad){
                    $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"No has modificado la cantidad de productos",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
                }

                if($cantidad>$_SESSION['datos_producto_venta'][$codigo]["venta_detalle_cantidad"]){
                    $diferencia_productos="agrego +".($cantidad-$_SESSION['datos_producto_venta'][$codigo]["venta_detalle_cantidad"]);
                }else{
                    $diferencia_productos="quito -".($_SESSION['datos_producto_venta'][$codigo]["venta_detalle_cantidad"]-$cantidad);
                }


                $detalle_cantidad=$cantidad;

                $stock_total=$campos['producto_stock_total']-$detalle_cantidad;

                if($stock_total<0){
                    $alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"Lo sentimos, no hay existencias suficientes del producto seleccionado. Existencias disponibles: ".($stock_total+$detalle_cantidad)."",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
                }

                $detalle_total=$detalle_cantidad*$campos['producto_precio_venta'];
                $detalle_total=number_format($detalle_total,MONEDA_DECIMALES,'.','');

                $_SESSION['datos_producto_venta'][$codigo]=[
                    "producto_id"=>$campos['producto_id'],
					"producto_codigo"=>$campos['producto_codigo'],
					"producto_stock_total"=>$stock_total,
					"producto_stock_total_old"=>$campos['producto_stock_total'],
                    "venta_detalle_precio_compra"=>$campos['producto_precio_compra'],
                    "venta_detalle_precio_venta"=>$campos['producto_precio_venta'],
                    "venta_detalle_cantidad"=>$detalle_cantidad,
                    "venta_detalle_total"=>$detalle_total,
                    "venta_detalle_descripcion"=>$campos['producto_nombre']
                ];

                $_SESSION['alerta_producto_agregado']="Se $diferencia_productos <strong>".$campos['producto_nombre']."</strong> a la venta. Total en carrito <strong>$detalle_cantidad</strong>";

                $alerta=[
					"tipo"=>"redireccionar",
					"url"=>APP_URL."saleNew/"
				];

				return json_encode($alerta);
            }else{
                $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado el producto que desea actualizar en el carrito",
					"icono"=>"error"
				];
				return json_encode($alerta);
            }
        }


        /*---------- Controlador buscar cliente ----------*/
        public function buscarClienteVentaControlador(){

            /*== Recuperando termino de busqueda ==*/
			$cliente=$this->limpiarCadena($_POST['buscar_cliente']);

			/*== Comprobando que no este vacio el campo ==*/
			if($cliente==""){
				return '
				<article class="message is-warning mt-4 mb-4">
					 <div class="message-header">
					    <p>¡Ocurrio un error inesperado!</p>
					 </div>
				    <div class="message-body has-text-centered">
				    	<i class="fas fa-exclamation-triangle fa-2x"></i><br>
						Debes de introducir el Numero de documento, Nombre, Apellido o Teléfono del cliente
				    </div>
				</article>';
				exit();
            }

            /*== Seleccionando clientes en la DB ==*/
            $param_cliente = '%'.$cliente.'%';

            $datos_cliente = $this->ejecutarConsultaSegura(
                "SELECT * FROM cliente 
                 WHERE (cliente_id != 1) 
                 AND (cliente_numero_documento LIKE :cliente1 
                   OR cliente_nombre LIKE :cliente2 
                   OR cliente_apellido LIKE :cliente3 
                   OR cliente_telefono LIKE :cliente4) 
                 ORDER BY cliente_nombre ASC",
                [
                    'cliente1' => $param_cliente,
                    'cliente2' => $param_cliente,
                    'cliente3' => $param_cliente,
                    'cliente4' => $param_cliente,
                ]
            );

            if($datos_cliente->rowCount()>=1){

				$datos_cliente=$datos_cliente->fetchAll();

				$tabla='<div class="table-container mb-6"><table class="table is-striped is-narrow is-hoverable is-fullwidth"><tbody>';

				foreach($datos_cliente as $rows){
					$tabla.='
					<tr>
                        <td class="has-text-left" ><i class="fas fa-male fa-fw"></i> &nbsp; '.$rows['cliente_nombre'].' '.$rows['cliente_apellido'].' ('.$rows['cliente_tipo_documento'].': '.$rows['cliente_numero_documento'].')</td>
                        <td class="has-text-centered" >
                            <button type="button" class="button is-link is-rounded is-small" onclick="agregar_cliente('.$rows['cliente_id'].')"><i class="fas fa-user-plus"></i></button>
                        </td>
                    </tr>
                    ';
				}

				$tabla.='</tbody></table></div>';
				return $tabla;
			}else{
				return '
				<article class="message is-warning mt-4 mb-4">
					 <div class="message-header">
					    <p>¡Ocurrio un error inesperado!</p>
					 </div>
				    <div class="message-body has-text-centered">
				    	<i class="fas fa-exclamation-triangle fa-2x"></i><br>
						No hemos encontrado ningún cliente en el sistema que coincida con <strong>“'.$cliente.'”</strong>
				    </div>
				</article>';
				exit();
			}
        }


        /*---------- Controlador agregar cliente ----------*/
        public function agregarClienteVentaControlador(){

            /*== Recuperando id del cliente ==*/
			$id=$this->limpiarCadena($_POST['cliente_id']);

			/*== Comprobando cliente en la DB ==*/
			$check_cliente = $this->ejecutarConsultaSegura(
                "SELECT * FROM cliente WHERE cliente_id = :id",
                ['id' => $id]
            );
			if($check_cliente->rowCount()<=0){
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido agregar el cliente debido a un error, por favor intente nuevamente",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
			}else{
				$campos=$check_cliente->fetch();
            }

			// ✅ Siempre actualizar el cliente (permite cambiar sin restricciones)
			$_SESSION['datos_cliente_venta']=[
				"cliente_id"=>$campos['cliente_id'],
				"cliente_tipo_documento"=>$campos['cliente_tipo_documento'],
				"cliente_numero_documento"=>$campos['cliente_numero_documento'],
				"cliente_nombre"=>$campos['cliente_nombre'],
				"cliente_apellido"=>$campos['cliente_apellido']
			];

			$alerta=[
				"tipo"=>"recargar",
				"titulo"=>"¡Cliente asignado!",
				"texto"=>"Se asignó a ".$campos['cliente_nombre']." ".$campos['cliente_apellido']." como cliente de esta venta",
				"icono"=>"success"
			];

            return json_encode($alerta);
        }


        /*---------- Controlador remover cliente ----------*/
        public function removerClienteVentaControlador(){

			unset($_SESSION['datos_cliente_venta']);

			if(empty($_SESSION['datos_cliente_venta'])){
				$alerta=[
					"tipo"=>"recargar",
					"titulo"=>"¡Cliente removido!",
					"texto"=>"Los datos del cliente se han quitado de la venta",
					"icono"=>"success"
				];
			}else{
				$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido remover el cliente, por favor intente nuevamente",
					"icono"=>"error"
				];	
			}
			return json_encode($alerta);
        }


        /*---------- Controlador registrar venta ----------*/
        public function registrarVentaControlador(){

            $caja=$this->limpiarCadena($_POST['venta_caja'] ?? '');
            $venta_pagado_usd=$this->limpiarCadena($_POST['venta_pagado_usd'] ?? '0');
            $venta_pagado_bs=$this->limpiarCadena($_POST['venta_pagado_bs'] ?? '0');
            $venta_tasa_dolar=$this->limpiarCadena($_POST['venta_tasa_dolar'] ?? '0');

            /*== Obtener el IVA de la empresa ==*/
            $check_empresa = $this->ejecutarConsultaSegura("SELECT iva FROM empresa LIMIT 1");
            $empresa_iva = $check_empresa->fetch();
            $iva_porcentaje_efectivo = floatval($empresa_iva['iva']);

            /*== Si NO es admin, forzar su caja asignada ==*/
            if(!$this->esAdmin()){
                $caja = $_SESSION['caja'];
            }

            /*==================== VALIDACIONES PREVIAS ====================*/
            if($_SESSION['venta_total']<=0 || (!isset($_SESSION['datos_producto_venta']) && count($_SESSION['datos_producto_venta'])<=0)){
                return json_encode([
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"No ha agregado productos a esta venta",
                    "icono"=>"error"
                ]);
            }

            if(!isset($_SESSION['datos_cliente_venta'])){
                return json_encode([
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"No ha seleccionado ningún cliente para realizar esta venta",
                    "icono"=>"error"
                ]);
            }

            if($venta_tasa_dolar<=0){
                return json_encode([
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"La tasa del dólar no es válida. Por favor verifica los datos de la empresa.",
                    "icono"=>"error"
                ]);
            }

            if($this->verificarDatos("[0-9.]{1,25}",$venta_pagado_usd) || $this->verificarDatos("[0-9.]{1,25}",$venta_pagado_bs)){
                return json_encode([
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"El total pagado por el cliente no coincide con el formato solicitado",
                    "icono"=>"error"
                ]);
            }

            /*== Comprobando cliente ==*/
            $check_cliente = $this->ejecutarConsultaSegura(
                "SELECT cliente_id FROM cliente WHERE cliente_id = :cliente_id",
                ['cliente_id' => $_SESSION['datos_cliente_venta']['cliente_id']]
            );
            if($check_cliente->rowCount()<=0){
                return json_encode([
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"No hemos encontrado el cliente registrado en el sistema",
                    "icono"=>"error"
                ]);
            }

            /*== Comprobando caja ==*/
            $check_caja = $this->ejecutarConsultaSegura(
                "SELECT * FROM caja WHERE caja_id = :caja",
                ['caja' => $caja]
            );
            if($check_caja->rowCount()<=0){
                return json_encode([
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"La caja no está registrada en el sistema",
                    "icono"=>"error"
                ]);
            }
            $datos_caja = $check_caja->fetch();


            /*==================== CÁLCULOS ====================*/
            $venta_tasa_dolar = floatval($venta_tasa_dolar);
            $venta_pagado_usd = floatval($venta_pagado_usd);
            $venta_pagado_bs  = floatval($venta_pagado_bs);

            $venta_total_usd = floatval($_SESSION['venta_total']);
            $venta_subtotal  = number_format($_SESSION['venta_subtotal'],MONEDA_DECIMALES,'.','');
            $venta_iva       = number_format($_SESSION['venta_iva'],MONEDA_DECIMALES,'.','');

            $venta_fecha = date("Y-m-d");
            $venta_hora  = date("h:i a");

            $pagado_bs_en_usd = $venta_pagado_bs / $venta_tasa_dolar;
            $total_pagado_usd = $venta_pagado_usd + $pagado_bs_en_usd;
            $total_pagado_bs  = $total_pagado_usd * $venta_tasa_dolar;

            $total_bs = $venta_total_usd * $venta_tasa_dolar;

            $venta_cambio_usd=0;
            $venta_cambio_bs=0;
            $efectivo_bs_entrante=0;
            $efectivo_usd_entrante=0;

            if($total_pagado_usd < $venta_total_usd){
                return json_encode([
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"El total pagado por el cliente es menor al total de la venta. Falta por pagar.",
                    "icono"=>"error"
                ]);
            }

            $sobrante_usd = $total_pagado_usd - $venta_total_usd;
            $sobrante_bs  = $sobrante_usd * $venta_tasa_dolar;

            if($venta_pagado_usd > 0 && $venta_pagado_bs == 0){
                $venta_cambio_usd = $sobrante_usd;
                $venta_cambio_bs  = 0;
                $efectivo_usd_entrante = $venta_pagado_usd - $venta_cambio_usd;
                $efectivo_bs_entrante  = 0;
            }elseif($venta_pagado_usd == 0 && $venta_pagado_bs > 0){
                $venta_cambio_usd = 0;
                $venta_cambio_bs  = $sobrante_bs;
                $efectivo_usd_entrante = 0;
                $efectivo_bs_entrante  = $venta_pagado_bs - $venta_cambio_bs;
            }else{
                $venta_cambio_usd = 0;
                $venta_cambio_bs  = $sobrante_bs;
                $efectivo_usd_entrante = $venta_pagado_usd;
                $efectivo_bs_entrante  = $venta_pagado_bs - $venta_cambio_bs;
            }

            $venta_total_usd_fmt   = number_format($venta_total_usd,MONEDA_DECIMALES,'.','');
            $venta_total_bs_fmt    = number_format($total_bs,MONEDA_DECIMALES,'.','');
            $venta_pagado_usd_fmt  = number_format($venta_pagado_usd,MONEDA_DECIMALES,'.','');
            $venta_pagado_bs_fmt   = number_format($venta_pagado_bs,MONEDA_DECIMALES,'.','');
            $venta_pagado_equiv_fmt= number_format($total_pagado_usd,MONEDA_DECIMALES,'.','');
            $venta_cambio_usd_fmt  = number_format($venta_cambio_usd,MONEDA_DECIMALES,'.','');
            $venta_cambio_bs_fmt   = number_format($venta_cambio_bs,MONEDA_DECIMALES,'.','');
            $efectivo_bs_fmt       = number_format($efectivo_bs_entrante,MONEDA_DECIMALES,'.','');
            $efectivo_usd_fmt      = number_format($efectivo_usd_entrante,MONEDA_DECIMALES,'.','');
            $tasa_fmt              = number_format($venta_tasa_dolar,4,'.','');

            if($venta_pagado_usd > 0 && $venta_pagado_bs > 0){
                $venta_forma_pago = "Mixto";
            }elseif($venta_pagado_usd > 0){
                $venta_forma_pago = "USD";
            }else{
                $venta_forma_pago = "Bs";
            }


            /*=========================================================
              🆕 TRANSACCIÓN NATIVA PDO
            =========================================================*/
            $conexion = $this->conectar();
            $conexion->beginTransaction();

            try {

                /*== 1. ACTUALIZAR STOCK CON BLOQUEO (FOR UPDATE) ==*/
                foreach($_SESSION['datos_producto_venta'] as $productos){

                    $check_producto = $this->ejecutarConsultaSegura(
                        "SELECT * FROM producto 
                         WHERE producto_id = :producto_id 
                           AND producto_codigo = :producto_codigo 
                         FOR UPDATE",
                        [
                            'producto_id'     => $productos['producto_id'],
                            'producto_codigo' => $productos['producto_codigo'],
                        ]
                    );

                    if($check_producto->rowCount() < 1){
                        throw new Exception("Producto no encontrado: ".$productos['producto_codigo']);
                    }

                    $datos_producto = $check_producto->fetch();
                    $cantidad_vendida = intval($productos['venta_detalle_cantidad']);
                    $nuevo_stock = intval($datos_producto['producto_stock_total']) - $cantidad_vendida;

                    if($nuevo_stock < 0){
                        throw new Exception("Stock insuficiente para: ".$datos_producto['producto_nombre']);
                    }

                    $_SESSION['datos_producto_venta'][$productos['producto_codigo']]['producto_stock_total'] = $nuevo_stock;
                    $_SESSION['datos_producto_venta'][$productos['producto_codigo']]['producto_stock_total_old'] = $datos_producto['producto_stock_total'];

                    $datos_producto_up = [
                        [
                            "campo_nombre"   => "producto_stock_total",
                            "campo_marcador" => ":Stock",
                            "campo_valor"    => $nuevo_stock
                        ]
                    ];

                    $condicion = [
                        "condicion_campo"    => "producto_id",
                        "condicion_marcador" => ":ID",
                        "condicion_valor"    => $productos['producto_id']
                    ];

                    $actualizado = $this->actualizarDatos("producto",$datos_producto_up,$condicion);
                    if($actualizado->rowCount() != 1){
                        throw new Exception("No se pudo actualizar stock de: ".$datos_producto['producto_nombre']);
                    }
                }

                /*== 2. GENERAR CÓDIGO DE VENTA ==*/
                $correlativo = $this->ejecutarConsultaSegura("SELECT venta_id FROM venta");
                $correlativo = ($correlativo->rowCount()) + 1;
                $codigo_venta = $this->generarCodigoAleatorio(10,$correlativo);

                /*== 3. INSERTAR VENTA ==*/
                $datos_venta_reg = [
                    ["campo_nombre"=>"venta_codigo",        "campo_marcador"=>":Codigo",     "campo_valor"=>$codigo_venta],
                    ["campo_nombre"=>"venta_fecha",         "campo_marcador"=>":Fecha",      "campo_valor"=>$venta_fecha],
                    ["campo_nombre"=>"venta_hora",          "campo_marcador"=>":Hora",       "campo_valor"=>$venta_hora],
                    ["campo_nombre"=>"venta_subtotal",      "campo_marcador"=>":Subtotal",   "campo_valor"=>$venta_subtotal],
                    ["campo_nombre"=>"venta_iva",           "campo_marcador"=>":IVA",        "campo_valor"=>$venta_iva],
                    ["campo_nombre"=>"venta_total",         "campo_marcador"=>":Total",      "campo_valor"=>$venta_total_usd_fmt],
                    ["campo_nombre"=>"venta_pagado",        "campo_marcador"=>":Pagado",     "campo_valor"=>$venta_pagado_equiv_fmt],
                    ["campo_nombre"=>"venta_cambio",        "campo_marcador"=>":Cambio",     "campo_valor"=>$venta_cambio_usd_fmt],
                    ["campo_nombre"=>"usuario_id",          "campo_marcador"=>":Usuario",    "campo_valor"=>$_SESSION['id']],
                    ["campo_nombre"=>"cliente_id",          "campo_marcador"=>":Cliente",    "campo_valor"=>$_SESSION['datos_cliente_venta']['cliente_id']],
                    ["campo_nombre"=>"caja_id",             "campo_marcador"=>":Caja",       "campo_valor"=>$caja],
                    ["campo_nombre"=>"venta_forma_pago",    "campo_marcador"=>":FormaPago",  "campo_valor"=>$venta_forma_pago],
                    ["campo_nombre"=>"venta_tasa_dolar",    "campo_marcador"=>":TasaDolar",  "campo_valor"=>$tasa_fmt],
                    ["campo_nombre"=>"venta_total_usd",     "campo_marcador"=>":TotalUSD",   "campo_valor"=>$venta_total_usd_fmt],
                    ["campo_nombre"=>"venta_total_bs",      "campo_marcador"=>":TotalBS",    "campo_valor"=>$venta_total_bs_fmt],
                    ["campo_nombre"=>"venta_pagado_usd",    "campo_marcador"=>":PagadoUSD",  "campo_valor"=>$venta_pagado_usd_fmt],
                    ["campo_nombre"=>"venta_pagado_bs",     "campo_marcador"=>":PagadoBS",   "campo_valor"=>$venta_pagado_bs_fmt],
                    ["campo_nombre"=>"venta_cambio_usd",    "campo_marcador"=>":CambioUSD",  "campo_valor"=>$venta_cambio_usd_fmt],
                    ["campo_nombre"=>"venta_cambio_bs",     "campo_marcador"=>":CambioBS",   "campo_valor"=>$venta_cambio_bs_fmt],
                ];

                $agregar_venta = $this->guardarDatos("venta",$datos_venta_reg);
                if($agregar_venta->rowCount() != 1){
                    throw new Exception("No se pudo registrar la venta en la tabla venta");
                }

                /*== 4. INSERTAR DETALLES ==*/
                foreach($_SESSION['datos_producto_venta'] as $venta_detalle){

                    $datos_venta_detalle_reg = [
                        ["campo_nombre"=>"venta_detalle_cantidad",        "campo_marcador"=>":Cantidad",     "campo_valor"=>$venta_detalle['venta_detalle_cantidad']],
                        ["campo_nombre"=>"venta_detalle_precio_compra",   "campo_marcador"=>":PrecioCompra", "campo_valor"=>$venta_detalle['venta_detalle_precio_compra']],
                        ["campo_nombre"=>"venta_detalle_precio_venta",    "campo_marcador"=>":PrecioVenta",  "campo_valor"=>$venta_detalle['venta_detalle_precio_venta']],
                        ["campo_nombre"=>"venta_detalle_total",           "campo_marcador"=>":Total",        "campo_valor"=>$venta_detalle['venta_detalle_total']],
                        ["campo_nombre"=>"venta_detalle_descripcion",     "campo_marcador"=>":Descripcion",  "campo_valor"=>$venta_detalle['venta_detalle_descripcion']],
                        ["campo_nombre"=>"venta_codigo",                  "campo_marcador"=>":VentaCodigo",  "campo_valor"=>$codigo_venta],
                        ["campo_nombre"=>"producto_id",                   "campo_marcador"=>":Producto",     "campo_valor"=>$venta_detalle['producto_id']]
                    ];

                    $agregar_detalle = $this->guardarDatos("venta_detalle",$datos_venta_detalle_reg);
                    if($agregar_detalle->rowCount() != 1){
                        throw new Exception("No se pudo registrar el detalle de: ".$venta_detalle['venta_detalle_descripcion']);
                    }
                }

                /*== 5. ACTUALIZAR CAJA ==*/
                $nuevo_efectivo_bs  = floatval($datos_caja['caja_efectivo']) + floatval($efectivo_bs_fmt);
                $nuevo_efectivo_usd = floatval($datos_caja['caja_efectivo_usd']) + floatval($efectivo_usd_fmt);

                $nuevo_efectivo_bs  = number_format($nuevo_efectivo_bs,MONEDA_DECIMALES,'.','');
                $nuevo_efectivo_usd = number_format($nuevo_efectivo_usd,MONEDA_DECIMALES,'.','');

                $datos_caja_up = [
                    ["campo_nombre"=>"caja_efectivo",     "campo_marcador"=>":Efectivo",    "campo_valor"=>$nuevo_efectivo_bs],
                    ["campo_nombre"=>"caja_efectivo_usd", "campo_marcador"=>":EfectivoUSD", "campo_valor"=>$nuevo_efectivo_usd]
                ];

                $condicion_caja = [
                    "condicion_campo"    => "caja_id",
                    "condicion_marcador" => ":ID",
                    "condicion_valor"    => $caja
                ];

                $actualizar_caja = $this->actualizarDatos("caja",$datos_caja_up,$condicion_caja);
                if($actualizar_caja->rowCount() != 1){
                    throw new Exception("No se pudo actualizar la caja");
                }

                /*== 6. COMMIT ==*/
                $conexion->commit();

            } catch (Exception $e) {

                if($conexion->inTransaction()){
                    $conexion->rollBack();
                }

                error_log("[VENTA ERROR] " . $e->getMessage());

                return json_encode([
                    "tipo"=>"simple",
                    "titulo"=>"Ocurrió un error inesperado",
                    "texto"=>"No hemos podido registrar la venta: ".$e->getMessage(),
                    "icono"=>"error"
                ]);
            }

            /*== Vaciando variables de sesion ==*/
            unset($_SESSION['venta_total']);
            unset($_SESSION['datos_cliente_venta']);
            unset($_SESSION['datos_producto_venta']);

            $_SESSION['venta_codigo_factura'] = $codigo_venta;

            return json_encode([
                "tipo"=>"recargar",
                "titulo"=>"¡Venta registrada!",
                "texto"=>"La venta se registró con éxito en el sistema",
                "icono"=>"success"
            ]);
        }


        /*----------  Controlador listar venta  ----------*/
		public function listarVentaControlador($pagina,$registros,$url,$busqueda){

			# 🔒 Solo admin
			$denegado = $this->requerirAdmin();
			if($denegado) return $denegado;

			$pagina=$this->limpiarCadena($pagina);
			$registros=$this->limpiarCadena($registros);

			$url=$this->limpiarCadena($url);
			$url=APP_URL.$url."/";

			$busqueda=$this->limpiarCadena($busqueda);
			$tabla="";

			$pagina = (isset($pagina) && $pagina>0) ? (int) $pagina : 1;
			$inicio = ($pagina>0) ? (($pagina * $registros)-$registros) : 0;

			$inicio_int    = (int) $inicio;
			$registros_int = (int) $registros;

			$campos_tablas="venta.venta_id,venta.venta_codigo,venta.venta_fecha,venta.venta_hora,venta.venta_total,venta.venta_total_usd,venta.venta_total_bs,venta.venta_tasa_dolar,venta.usuario_id,venta.cliente_id,venta.caja_id,usuario.usuario_id,usuario.usuario_nombre,usuario.usuario_apellido,cliente.cliente_id,cliente.cliente_nombre,cliente.cliente_apellido,venta.venta_forma_pago,venta.anulado";

			if(isset($busqueda) && $busqueda!=""){

				$consulta_datos = "SELECT $campos_tablas FROM venta 
				                   INNER JOIN cliente ON venta.cliente_id=cliente.cliente_id 
				                   INNER JOIN usuario ON venta.usuario_id=usuario.usuario_id 
				                   WHERE (venta.venta_codigo = :busqueda) 
				                   ORDER BY venta.venta_id DESC 
				                   LIMIT $inicio_int, $registros_int";

				$consulta_total = "SELECT COUNT(venta_id) FROM venta WHERE (venta.venta_codigo = :busqueda)";

				$params = ['busqueda' => $busqueda];

			}else{

				$consulta_datos = "SELECT $campos_tablas FROM venta 
				                   INNER JOIN cliente ON venta.cliente_id=cliente.cliente_id 
				                   INNER JOIN usuario ON venta.usuario_id=usuario.usuario_id 
				                   ORDER BY venta.venta_id DESC 
				                   LIMIT $inicio_int, $registros_int";

				$consulta_total = "SELECT COUNT(venta_id) FROM venta";

				$params = [];

			}

			$datos = $this->ejecutarConsultaSegura($consulta_datos, $params);
			$datos = $datos->fetchAll();

			$total = $this->ejecutarConsultaSegura($consulta_total, $params);
			$total = (int) $total->fetchColumn();

			$numeroPaginas =ceil($total/$registros);

			$tabla.='
		        <div class="table-container">
		        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
		            <thead>
		                <tr>
		                    <th class="has-text-centered">NRO.</th>
		                    <th class="has-text-centered">Codigo</th>
		                    <th class="has-text-centered">Fecha</th>
		                    <th class="has-text-centered">Cliente</th>
		                    <th class="has-text-centered">Total $</th>
		                    <th class="has-text-centered">Total Bs</th>
							<th class="has-text-centered">Anulado</th>
		                    <th class="has-text-centered">Opciones</th>
		                </tr>
		            </thead>
		            <tbody>
		    ';

		    if($total>=1 && $pagina<=$numeroPaginas){
				$contador=$inicio+1;
				$pag_inicio=$inicio+1;
				foreach($datos as $rows){
					if($rows["anulado"] == 0){
						$anular = '
							<button type="button" class="button is-danger is-rounded is-small" onclick="anularVenta('.$rows['venta_id'].', \''.APP_URL.'\')">
								Anular
							</button>
						';
						$anulado = "NO";
					}else{
						$anular = '';
						$anulado = "SI";
					}
					
					$tabla.='
						<tr class="has-text-centered" >
							<td>'.$rows['venta_id'].'</td>
							<td>'.$rows['venta_codigo'].'</td>
							<td>'.date("d-m-Y", strtotime($rows['venta_fecha'])).' '.$rows['venta_hora'].'</td>
							<td>'.$this->limitarCadena($rows['cliente_nombre'].' '.$rows['cliente_apellido'],30,"...").'</td>
							<td><strong>$'.number_format($rows['venta_total_usd'],2,'.',',').'</strong></td>
							<td><strong>Bs '.number_format($rows['venta_total_bs'],2,'.',',').'</strong></td>
			                <td>'.$anulado.'</td>
							<td>

			                	<button type="button" class="button is-link is-outlined is-rounded is-small btn-sale-options" onclick="print_invoice(\''.APP_URL.'app/pdf/invoice.php?code='.$rows['venta_codigo'].'\')" title="Imprimir factura Nro. '.$rows['venta_id'].'" >
	                                <i class="fas fa-file-invoice-dollar fa-fw"></i>
	                            </button>

                                <button type="button" class="button is-link is-outlined is-rounded is-small btn-sale-options" onclick="print_ticket(\''.APP_URL.'app/pdf/ticket.php?code='.$rows['venta_codigo'].'\')" title="Imprimir ticket Nro. '.$rows['venta_id'].'" >
                                    <i class="fas fa-receipt fa-fw"></i>
                                </button>
								
			                    <a href="'.APP_URL.'saleDetail/'.$rows['venta_codigo'].'/" class="button is-link is-rounded is-small" title="Informacion de venta Nro. '.$rows['venta_id'].'" >
			                    	<i class="fas fa-shopping-bag fa-fw"></i>
			                    </a>

								'.$anular.'
			                </td>
						</tr>
					';
					$contador++;
				}
				$pag_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='
						<tr class="has-text-centered" >
			                <td colspan="8">
			                    <a href="'.$url.'1/" class="button is-link is-rounded is-small mt-4 mb-4">
			                        Haga clic acá para recargar el listado
			                    </a>
			                </td>
			            </tr>
					';
				}else{
					$tabla.='
						<tr class="has-text-centered" >
			                <td colspan="8">
			                    No hay registros en el sistema
			                </td>
			            </tr>
					';
				}
			}

			$tabla.='</tbody></table></div>';

			### Paginacion ###
			if($total>0 && $pagina<=$numeroPaginas){
				$tabla.='<p class="has-text-right">Mostrando ventas <strong>'.$pag_inicio.'</strong> al <strong>'.$pag_final.'</strong> de un <strong>total de '.$total.'</strong></p>';

				$tabla.=$this->paginadorTablas($pagina,$numeroPaginas,$url,7);
			}

			return $tabla;
		}


		/*----------  Controlador eliminar venta  ----------*/
		public function eliminarVentaControlador(){

			# 🔒 Solo admin
			$denegado = $this->requerirAdmin();
			if($denegado) return $denegado;

			$id=$this->limpiarCadena($_POST['venta_id']);

			# Verificando venta #
		    $datos = $this->ejecutarConsultaSegura(
				"SELECT * FROM venta WHERE venta_id = :id",
				['id' => $id]
			);
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la venta en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }

		    # Verificando detalles de venta #
		    $check_detalle_venta = $this->ejecutarConsultaSegura(
				"SELECT venta_detalle_id FROM venta_detalle WHERE venta_codigo = :venta_codigo",
				['venta_codigo' => $datos['venta_codigo']]
			);
		    $check_detalle_venta=$check_detalle_venta->rowCount();

		    if($check_detalle_venta>0){

		        $eliminarVentaDetalle=$this->eliminarRegistro("venta_detalle","venta_codigo",$datos['venta_codigo']);

		        if($eliminarVentaDetalle->rowCount()!=$check_detalle_venta){
		        	$alerta=[
						"tipo"=>"simple",
						"titulo"=>"Ocurrió un error inesperado",
						"texto"=>"No hemos podido eliminar la venta del sistema, por favor intente nuevamente",
						"icono"=>"error"
					];
					return json_encode($alerta);
			        exit();
		        }

		    }


		    $eliminarVenta=$this->eliminarRegistro("venta","venta_id",$id);

		    if($eliminarVenta->rowCount()==1){

		        $alerta=[
					"tipo"=>"recargar",
					"titulo"=>"Venta eliminada",
					"texto"=>"La venta ha sido eliminada del sistema correctamente",
					"icono"=>"success"
				];

		    }else{
		    	$alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos podido eliminar la venta del sistema, por favor intente nuevamente",
					"icono"=>"error"
				];
		    }

		    return json_encode($alerta);
		}


		/*----------  Controlador anular venta  ----------*/
		public function anularVentaControlador(){

			# 🔒 Solo admin
			$denegado = $this->requerirAdmin();
			if($denegado) return $denegado;

			$id=$this->limpiarCadena($_POST['venta_id']);

			# Verificando venta #
		    $datos = $this->ejecutarConsultaSegura(
				"SELECT * FROM venta WHERE venta_id = :id",
				['id' => $id]
			);
		    if($datos->rowCount()<=0){
		        $alerta=[
					"tipo"=>"simple",
					"titulo"=>"Ocurrió un error inesperado",
					"texto"=>"No hemos encontrado la venta en el sistema",
					"icono"=>"error"
				];
				return json_encode($alerta);
		        exit();
		    }else{
		    	$datos=$datos->fetch();
		    }


		    $eliminarVenta=$this->anularRegistro("venta","venta_id",$id);
			$alerta=[
				"tipo"=>"recargar",
				"titulo"=>"Venta ".$_POST['venta_id']." anulada",
				"texto"=>"La venta ha sido anulada del sistema correctamente",
				"icono"=>"success"
			];

		    return json_encode($alerta);
		}



	}
