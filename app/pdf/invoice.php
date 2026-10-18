<?php
	
	$peticion_ajax=true;
	$code=(isset($_GET['code'])) ? $_GET['code'] : 0;

	/*---------- Incluyendo configuraciones ----------*/
	require_once "../../config/app.php";
    require_once "../../autoload.php";

	/*---------- Instancia al controlador venta ----------*/
	use app\controllers\saleController;
	$ins_venta = new saleController();

	$datos_venta=$ins_venta->seleccionarDatos("Normal","venta INNER JOIN cliente ON venta.cliente_id=cliente.cliente_id INNER JOIN usuario ON venta.usuario_id=usuario.usuario_id INNER JOIN caja ON venta.caja_id=caja.caja_id WHERE (venta_codigo='$code')","*",0);

	if($datos_venta->rowCount()==1){

		/*---------- Datos de la venta ----------*/
		$datos_venta=$datos_venta->fetch();

		/*---------- Seleccion de datos de la empresa ----------*/
		$datos_empresa=$ins_venta->seleccionarDatos("Normal","empresa LIMIT 1","*",0);
		$datos_empresa=$datos_empresa->fetch();

		/*---------- Datos multimoneda ----------*/
		$forma_pago    = $datos_venta['venta_forma_pago'];
		$total_usd     = floatval($datos_venta['venta_total_usd']);
		$total_bs      = floatval($datos_venta['venta_total_bs']);
		$pagado_usd    = floatval($datos_venta['venta_pagado_usd']);
		$pagado_bs     = floatval($datos_venta['venta_pagado_bs']);
		$cambio_usd    = floatval($datos_venta['venta_cambio_usd']);
		$cambio_bs     = floatval($datos_venta['venta_cambio_bs']);
		$tasa_dolar    = floatval($datos_venta['venta_tasa_dolar']);


		require "./code128.php";

		$pdf = new PDF_Code128('P','mm','Letter');
		$pdf->SetMargins(17,17,17);
		$pdf->AddPage();
		$pdf->Image(APP_URL.'app/views/img/logo.jpg',160,12,42,42,'JPEG');

		$pdf->SetFont('Arial','B',16);
		$pdf->SetTextColor(32,100,210);
		$pdf->Cell(150,10,iconv("UTF-8", "ISO-8859-1",strtoupper($datos_empresa['empresa_nombre'])),0,0,'L');

		$pdf->Ln(9);

		$pdf->SetFont('Arial','',10);
		$pdf->SetTextColor(39,39,51);
		$pdf->Cell(150,9,iconv("UTF-8", "ISO-8859-1",""),0,0,'L');

		$pdf->Ln(5);

		$pdf->Cell(150,9,iconv("UTF-8", "ISO-8859-1",$datos_empresa['empresa_direccion']),0,0,'L');

		$pdf->Ln(5);

		$pdf->Cell(150,9,iconv("UTF-8", "ISO-8859-1","Teléfono: ".$datos_empresa['empresa_telefono']),0,0,'L');

		$pdf->Ln(5);

		$pdf->Cell(150,9,iconv("UTF-8", "ISO-8859-1","Email: ".$datos_empresa['empresa_email']),0,0,'L');

		$pdf->Ln(10);

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(30,7,iconv("UTF-8", "ISO-8859-1",'Fecha de emisión:'),0,0);
		$pdf->SetTextColor(97,97,97);
		$pdf->Cell(116,7,iconv("UTF-8", "ISO-8859-1",date("d/m/Y", strtotime($datos_venta['venta_fecha']))." ".$datos_venta['venta_hora']),0,0,'L');
		$pdf->SetFont('Arial','B',10);
		$pdf->SetTextColor(39,39,51);
		$pdf->Cell(35,7,iconv("UTF-8", "ISO-8859-1",strtoupper('Factura Nro.')),0,0,'C');

		$pdf->Ln(7);

		$pdf->SetFont('Arial','',10);
		$pdf->Cell(12,7,iconv("UTF-8", "ISO-8859-1",'Cajero:'),0,0,'L');
		$pdf->SetTextColor(97,97,97);
		$pdf->Cell(134,7,iconv("UTF-8", "ISO-8859-1",$datos_venta['usuario_nombre']." ".$datos_venta['usuario_apellido']),0,0,'L');
		$pdf->SetFont('Arial','B',10);
		$pdf->SetTextColor(97,97,97);
		$pdf->Cell(35,7,iconv("UTF-8", "ISO-8859-1",strtoupper($datos_venta['venta_id'])),0,0,'C');

		$pdf->Ln(7);

		// Tasa del día
		$pdf->SetFont('Arial','',9);
		$pdf->SetTextColor(39,39,51);
		$pdf->Cell(30,7,iconv("UTF-8", "ISO-8859-1",'Tasa del día:'),0,0);
		$pdf->SetTextColor(97,97,97);
		$pdf->Cell(116,7,iconv("UTF-8", "ISO-8859-1","Bs ".number_format($tasa_dolar,4,'.',',')." / $1 USD"),0,0,'L');

		$pdf->Ln(10);

		if($datos_venta['cliente_id']==1){
			$pdf->SetFont('Arial','',10);
			$pdf->SetTextColor(39,39,51);
			$pdf->Cell(13,7,iconv("UTF-8", "ISO-8859-1",'Cliente:'),0,0);
			$pdf->SetTextColor(97,97,97);
			$pdf->Cell(60,7,iconv("UTF-8", "ISO-8859-1","N/A"),0,0,'L');
			$pdf->SetTextColor(39,39,51);
			$pdf->Cell(8,7,iconv("UTF-8", "ISO-8859-1","Doc: "),0,0,'L');
			$pdf->SetTextColor(97,97,97);
			$pdf->Cell(60,7,iconv("UTF-8", "ISO-8859-1","N/A"),0,0,'L');
			$pdf->SetTextColor(39,39,51);
			$pdf->Cell(7,7,iconv("UTF-8", "ISO-8859-1",'Tel:'),0,0,'L');
			$pdf->SetTextColor(97,97,97);
			$pdf->Cell(35,7,iconv("UTF-8", "ISO-8859-1","N/A"),0,0);
			$pdf->SetTextColor(39,39,51);

			$pdf->Ln(7);

			$pdf->SetTextColor(39,39,51);
			$pdf->Cell(6,7,iconv("UTF-8", "ISO-8859-1",'Dir:'),0,0);
			$pdf->SetTextColor(97,97,97);
			$pdf->Cell(109,7,iconv("UTF-8", "ISO-8859-1","N/A"),0,0);
		}else{
			$pdf->SetFont('Arial','',10);
			$pdf->SetTextColor(39,39,51);
			$pdf->Cell(13,7,iconv("UTF-8", "ISO-8859-1",'Cliente:'),0,0);
			$pdf->SetTextColor(97,97,97);
			$pdf->Cell(60,7,iconv("UTF-8", "ISO-8859-1",$datos_venta['cliente_nombre']." ".$datos_venta['cliente_apellido']),0,0,'L');
			$pdf->SetTextColor(39,39,51);
			$pdf->Cell(8,7,iconv("UTF-8", "ISO-8859-1","Doc: "),0,0,'L');
			$pdf->SetTextColor(97,97,97);
			$pdf->Cell(60,7,iconv("UTF-8", "ISO-8859-1",$datos_venta['cliente_tipo_documento']." ".$datos_venta['cliente_numero_documento']),0,0,'L');
			$pdf->SetTextColor(39,39,51);
			$pdf->Cell(7,7,iconv("UTF-8", "ISO-8859-1",'Tel:'),0,0,'L');
			$pdf->SetTextColor(97,97,97);
			$pdf->Cell(35,7,iconv("UTF-8", "ISO-8859-1",$datos_venta['cliente_telefono']),0,0);
			$pdf->SetTextColor(39,39,51);

			$pdf->Ln(7);

			$pdf->SetTextColor(39,39,51);
			$pdf->Cell(6,7,iconv("UTF-8", "ISO-8859-1",'Dir:'),0,0);
			$pdf->SetTextColor(97,97,97);
			$pdf->Cell(109,7,iconv("UTF-8", "ISO-8859-1",$datos_venta['cliente_provincia'].", ".$datos_venta['cliente_ciudad'].", ".$datos_venta['cliente_direccion']),0,0);
		}

		$pdf->Ln(9);

		$pdf->SetFillColor(23,83,201);
		$pdf->SetDrawColor(23,83,201);
		$pdf->SetTextColor(255,255,255);
		$pdf->Cell(100,8,iconv("UTF-8", "ISO-8859-1",'Descripción'),1,0,'C',true);
		$pdf->Cell(15,8,iconv("UTF-8", "ISO-8859-1",'Cant.'),1,0,'C',true);
		$pdf->Cell(32,8,iconv("UTF-8", "ISO-8859-1",'Precio $'),1,0,'C',true);
		$pdf->Cell(34,8,iconv("UTF-8", "ISO-8859-1",'Subtotal $'),1,0,'C',true);

		$pdf->Ln(8);

		$pdf->SetFont('Arial','',9);
		$pdf->SetTextColor(39,39,51);

		/*----------  Seleccionando detalles de la venta  ----------*/
		$venta_detalle=$ins_venta->seleccionarDatos("Normal","venta_detalle WHERE venta_codigo='".$datos_venta['venta_codigo']."'","*",0);
		$venta_detalle=$venta_detalle->fetchAll();
		foreach($venta_detalle as $detalle){
			$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",$ins_venta->limitarCadena($detalle['venta_detalle_descripcion'],80,"...")),'L',0,'C');
			$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",$detalle['venta_detalle_cantidad']),'L',0,'C');
			$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",number_format($detalle['venta_detalle_precio_venta'],2,'.',',')),'L',0,'C');
			$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1",number_format($detalle['venta_detalle_total'],2,'.',',')),'LR',0,'C');
			$pdf->Ln(7);
		}

		$pdf->SetFont('Arial','B',9);
		$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'T',0,'C');
		$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'T',0,'C');
		$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'SUBTOTAL'),'T',0,'C');
		$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","$".number_format($datos_venta['venta_subtotal'],2,'.',',')),'T',0,'C');

		$pdf->Ln(7);

		$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
		$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
		$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'IVA'),'T',0,'C');
		$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","$".number_format($datos_venta['venta_iva'],2,'.',',').' ('.$datos_empresa['iva'].'%)'),'T',0,'C');

		$pdf->Ln(7);

		$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
		$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
		$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'TOTAL A PAGAR'),'T',0,'C');
		$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","$".number_format($total_usd,2,'.',',')),'T',0,'C');

		$pdf->Ln(7);

		/*=============================================
		  PAGO Y CAMBIO SEGÚN FORMA DE PAGO
		=============================================*/
		$pdf->SetFont('Arial','B',9);
		$pdf->SetFillColor(240,240,240);

		if($forma_pago == "USD"){
			// Solo dólares
			$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'TOTAL PAGADO'),'',0,'C');
			$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","$".number_format($pagado_usd,2,'.',',')),'',0,'C');

			$pdf->Ln(7);
			$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'CAMBIO'),'',0,'C');
			$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","$".number_format($cambio_usd,2,'.',',')),'',0,'C');
		}elseif($forma_pago == "Bs"){
			// Solo bolívares
			$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'TOTAL PAGADO'),'',0,'C');
			$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","Bs ".number_format($pagado_bs,2,'.',',')),'',0,'C');

			$pdf->Ln(7);
			$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'CAMBIO'),'',0,'C');
			$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","Bs ".number_format($cambio_bs,2,'.',',')),'',0,'C');
		}else{
			// Mixto: mostrar desglose
			$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'PAGÓ EN $'),'',0,'C');
			$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","$".number_format($pagado_usd,2,'.',',')),'',0,'C');

			$pdf->Ln(7);
			$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
			$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'PAGÓ EN Bs'),'',0,'C');
			$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","Bs ".number_format($pagado_bs,2,'.',',')),'',0,'C');

			$pdf->Ln(7);

			if($cambio_usd > 0){
				$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
				$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
				$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'CAMBIO $'),'',0,'C');
				$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","$".number_format($cambio_usd,2,'.',',')),'',0,'C');
				$pdf->Ln(7);
			}
			if($cambio_bs > 0){
				$pdf->Cell(100,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
				$pdf->Cell(15,7,iconv("UTF-8", "ISO-8859-1",''),'',0,'C');
				$pdf->Cell(32,7,iconv("UTF-8", "ISO-8859-1",'CAMBIO Bs'),'',0,'C');
				$pdf->Cell(34,7,iconv("UTF-8", "ISO-8859-1","Bs ".number_format($cambio_bs,2,'.',',')),'',0,'C');
			}
		}

		$pdf->Ln(12);

		$pdf->SetFont('Arial','',9);
		$pdf->SetTextColor(39,39,51);

		$pdf->Ln(9);

		$pdf->SetFillColor(39,39,51);
		$pdf->SetDrawColor(23,83,201);
        $pdf->Code128(72,$pdf->GetY(),$datos_venta['venta_codigo'],70,20);
        $pdf->SetXY(12,$pdf->GetY()+21);
        $pdf->SetFont('Arial','',12);
        $pdf->MultiCell(0,5,iconv("UTF-8", "ISO-8859-1",$datos_venta['venta_codigo']),0,'C',false);

		$pdf->Output("I","Factura_Nro".$datos_venta['venta_id'].".pdf",true);

	}else{
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title><?php echo APP_NAME; ?></title>
	<?php include '../views/inc/head.php'; ?>
</head>
<body>
	<div class="main-container">
        <section class="hero-body">
            <div class="hero-body">
                <p class="has-text-centered has-text-white pb-3">
                    <i class="fas fa-rocket fa-5x"></i>
                </p>
                <p class="title has-text-white">¡Ocurrió un error!</p>
                <p class="subtitle has-text-white">No hemos encontrado datos de la venta</p>
            </div>
        </section>
    </div>
	<?php include '../views/inc/script.php'; ?>
</body>
</html>
<?php } ?>
