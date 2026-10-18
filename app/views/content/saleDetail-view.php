<div class="container is-fluid mb-6">
	<h1 class="title">Reportes</h1>
	<h2 class="subtitle"><i class="fas fa-shopping-bag fa-fw"></i> &nbsp; Información de venta</h2>
</div>

<div class="container pb-6 pt-6">
	<?php
	
		include "./app/views/inc/btn_back.php";

		$code=$insLogin->limpiarCadena($url[1]);

		$datos=$insLogin->seleccionarDatos("Normal","venta INNER JOIN cliente ON venta.cliente_id=cliente.cliente_id INNER JOIN usuario ON venta.usuario_id=usuario.usuario_id INNER JOIN caja ON venta.caja_id=caja.caja_id WHERE (venta_codigo='".$code."')","*",0);
		
		if($datos->rowCount()==1){
			$datos_venta=$datos->fetch();

			if($datos_venta['anulado'] == 1){
				$anulado = " - ANULADA";
			}else{
				$anulado = "";
			}

			/*---------- Datos multimoneda ----------*/
			$forma_pago    = $datos_venta['venta_forma_pago'];
			$total_usd     = floatval($datos_venta['venta_total_usd']);
			$total_bs      = floatval($datos_venta['venta_total_bs']);
			$pagado_usd    = floatval($datos_venta['venta_pagado_usd']);
			$pagado_bs     = floatval($datos_venta['venta_pagado_bs']);
			$cambio_usd    = floatval($datos_venta['venta_cambio_usd']);
			$cambio_bs     = floatval($datos_venta['venta_cambio_bs']);
			$tasa_dolar    = floatval($datos_venta['venta_tasa_dolar']);
	?>
	<h2 class="title has-text-centered">Reporte de la venta <?php echo " (".$code.")".$anulado; ?></h2>
	<div class="columns pb-6 pt-6">
		<div class="column">

			<div class="full-width sale-details text-condensedLight">
				<div class="has-text-weight-bold">Fecha</div>
				<span class="has-text-link"><?php echo date("d-m-Y", strtotime($datos_venta['venta_fecha']))." ".$datos_venta['venta_hora']; ?></span>
			</div>

			<div class="full-width sale-details text-condensedLight">
				<div class="has-text-weight-bold">Nro. de factura</div>
				<span class="has-text-link"><?php echo $datos_venta['venta_id']; ?></span>
			</div>

			<div class="full-width sale-details text-condensedLight">
				<div class="has-text-weight-bold">Código de venta</div>
				<span class="has-text-link"><?php echo $datos_venta['venta_codigo']; ?></span>
			</div>

		</div>

		<div class="column">

			<div class="full-width sale-details text-condensedLight">
				<div class="has-text-weight-bold">Caja</div>
				<span class="has-text-link">Nro. <?php echo $datos_venta['caja_numero']." (".$datos_venta['caja_nombre']; ?>)</span>
			</div>

			<div class="full-width sale-details text-condensedLight">
				<div class="has-text-weight-bold">Vendedor</div>
				<span class="has-text-link"><?php echo $datos_venta['usuario_nombre']." ".$datos_venta['usuario_apellido']; ?></span>
			</div>

			<div class="full-width sale-details text-condensedLight">
				<div class="has-text-weight-bold">Cliente</div>
				<span class="has-text-link"><?php echo $datos_venta['cliente_nombre']." ".$datos_venta['cliente_apellido']; ?></span>
			</div>

		</div>

		<div class="column">

			<div class="full-width sale-details text-condensedLight">
				<div class="has-text-weight-bold">Tasa del día</div>
				<span class="has-text-link">Bs <?php echo number_format($tasa_dolar,4,'.',','); ?> / $1</span>
			</div>

			<div class="full-width sale-details text-condensedLight">
				<div class="has-text-weight-bold">Forma de pago</div>
				<span class="has-text-link"><?php echo $forma_pago; ?></span>
			</div>

		</div>

	</div>

	<div class="columns pb-6 pt-6">
		<div class="column">
			<div class="table-container">
                <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
                    <thead>
                        <tr>
                            <th class="has-text-centered">#</th>
                            <th class="has-text-centered">Producto</th>
                            <th class="has-text-centered">Cant.</th>
                            <th class="has-text-centered">Precio $</th>
                            <th class="has-text-centered">Subtotal $</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        	$detalle_venta=$insLogin->seleccionarDatos("Normal","venta_detalle WHERE venta_codigo='".$datos_venta['venta_codigo']."'","*",0);

                            if($detalle_venta->rowCount()>=1){

                                $detalle_venta=$detalle_venta->fetchAll();
                            	$cc=1;

                                foreach($detalle_venta as $detalle){
                        ?>
                        <tr class="has-text-centered" >
                            <td><?php echo $cc; ?></td>
                            <td><?php echo $detalle['venta_detalle_descripcion']; ?></td>
                            <td><?php echo $detalle['venta_detalle_cantidad']; ?></td>
                            <td>$<?php echo number_format($detalle['venta_detalle_precio_venta'],2,'.',','); ?></td>
                            <td>$<?php echo number_format($detalle['venta_detalle_total'],2,'.',','); ?></td>
                        </tr>
                        <?php
                                $cc++;
                            }
                        ?>
						<tr class="has-text-centered" >
                            <td colspan="3"></td>
                            <td class="has-text-weight-bold">
                                SUB TOTAL
                            </td>
                            <td class="has-text-weight-bold">
                                $<?php echo number_format($datos_venta['venta_subtotal'],2,'.',','); ?>
                            </td>
                        </tr>
						<tr class="has-text-centered" >
                            <td colspan="3"></td>
                            <td class="has-text-weight-bold">
                                IVA
                            </td>
                            <td class="has-text-weight-bold">
                                $<?php echo number_format($datos_venta['venta_iva'],2,'.',','); ?>
                            </td>
                        </tr>
                        <tr class="has-text-centered" >
                            <td colspan="3"></td>
                            <td class="has-text-weight-bold">
                                TOTAL $
                            </td>
                            <td class="has-text-weight-bold">
                                $<?php echo number_format($total_usd,2,'.',','); ?>
                            </td>
                        </tr>
                        <tr class="has-text-centered" >
                            <td colspan="3"></td>
                            <td class="has-text-weight-bold">
                                TOTAL Bs
                            </td>
                            <td class="has-text-weight-bold">
                                Bs <?php echo number_format($total_bs,2,'.',','); ?>
                            </td>
                        </tr>
                        <?php
                            }else{
                        ?>
                        <tr class="has-text-centered" >
                            <td colspan="5">
                                No hay productos agregados
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
		</div>
	</div>

	<!-- ============================================
	     PAGO Y CAMBIO SEGÚN FORMA DE PAGO
	============================================ -->
	<div class="columns pb-6 pt-6">
		<div class="column">
			<div class="box">
				<h4 class="title is-5 has-text-centered mb-4">
					<i class="fas fa-money-bill-wave"></i> &nbsp; Detalle del Pago
				</h4>

				<table class="table is-fullwidth">
					<tbody>
							<?php if($forma_pago == "USD"){ ?>
							<tr>
								<td class="has-text-right has-text-weight-bold">Total pagado:</td>
								<td class="has-text-left">$<?php echo number_format($pagado_usd,2,'.',','); ?></td>
							</tr>
							<tr>
								<td class="has-text-right has-text-weight-bold">Cambio:</td>
								<td class="has-text-left has-text-success">$<?php echo number_format($cambio_usd,2,'.',','); ?></td>
							</tr>
							<?php }elseif($forma_pago == "Bs"){ ?>
							<tr>
								<td class="has-text-right has-text-weight-bold">Total pagado:</td>
								<td class="has-text-left">Bs <?php echo number_format($pagado_bs,2,'.',','); ?></td>
							</tr>
							<tr>
								<td class="has-text-right has-text-weight-bold">Cambio:</td>
								<td class="has-text-left has-text-success">Bs <?php echo number_format($cambio_bs,2,'.',','); ?></td>
							</tr>
							<?php }else{ ?>
							<tr>
								<td class="has-text-right has-text-weight-bold">Pagó en Dólares:</td>
								<td class="has-text-left">$<?php echo number_format($pagado_usd,2,'.',','); ?></td>
							</tr>
							<tr>
								<td class="has-text-right has-text-weight-bold">Pagó en Bolívares:</td>
								<td class="has-text-left">Bs <?php echo number_format($pagado_bs,2,'.',','); ?></td>
							</tr>
							<?php if($cambio_usd > 0){ ?>
							<tr>
								<td class="has-text-right has-text-weight-bold">Cambio en $:</td>
								<td class="has-text-left has-text-success">$<?php echo number_format($cambio_usd,2,'.',','); ?></td>
							</tr>
							<?php } ?>
							<?php if($cambio_bs > 0){ ?>
							<tr>
								<td class="has-text-right has-text-weight-bold">Cambio en Bs:</td>
								<td class="has-text-left has-text-success">Bs <?php echo number_format($cambio_bs,2,'.',','); ?></td>
							</tr>
							<?php } ?>
							<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="columns pb-6 pt-6">
		<p class="has-text-centered full-width">
			<?php
			echo '<button type="button" class="button is-link is-light is-medium" onclick="print_invoice(\''.APP_URL.'app/pdf/invoice.php?code='.$datos_venta['venta_codigo'].'\')" title="Imprimir factura Nro. '.$datos_venta['venta_id'].'" >
			<i class="fas fa-file-invoice-dollar fa-fw"></i> &nbsp; Imprimir factura
			</button> &nbsp;&nbsp; 

			<button type="button" class="button is-link is-light is-medium" onclick="print_ticket(\''.APP_URL.'app/pdf/ticket.php?code='.$datos_venta['venta_codigo'].'\')" title="Imprimir ticket Nro. '.$datos_venta['venta_id'].'" ><i class="fas fa-receipt fa-fw"></i> &nbsp; Imprimir ticket</button>
			
			<button type="button" class="button is-link is-light is-medium" onclick="print_ticket(\''.APP_URL.'app/pdf/reporte_venta.php?code='.$datos_venta['venta_codigo'].'\')" title="Generar PDF. '.$datos_venta['venta_id'].'" ><i class="fas fa-receipt fa-fw"></i> &nbsp; Generar reporte en PDF</button>
			';
			?>
		</p>
	</div>
	<?php
			include "./app/views/inc/print_invoice_script.php";
		}else{
			include "./app/views/inc/error_alert.php";
		}
	?>
</div>
