<?php 
// Solo aceptamos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: proyecto.html');
    exit;
}

// Función para obtener valores POST
function post_val($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

// Obtener datos
$empresa  = post_val('Empresa');
$nombre   = post_val('Nombre');
$email    = post_val('Email');
$telefono = post_val('Telefono');
$mensaje  = post_val('Mensaje');

// Sanitizar datos
$empresa_s  = htmlspecialchars($empresa, ENT_QUOTES, 'UTF-8');
$nombre_s   = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
$email_s    = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$telefono_s = htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8');
$mensaje_s  = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');

// Validar email
$email_valido = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
// === Generar PDF ===
require_once __DIR__ . '/fpdf/fpdf.php'; // Asegúrate de tener FPDF

// Genera un nombre de archivo único por registro
$pdfFileName = 'registro_' . date('Ymd_His') . '.pdf';
$pdfPath = __DIR__ . '/' . $pdfFileName;

// Extender FPDF para footer
class PDF_Extended extends FPDF {
    function Footer() {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(100,100,100);
        $this->Cell(0,10,utf8_decode('Generado el: ' . date('Y-m-d H:i:s') . ' - Página ').$this->PageNo().'/{nb}',0,0,'C');
    }
}
 $pdf = new PDF_Extended('P','mm','A4');
 $pdf->AliasNbPages();
 $pdf->AddPage();

 // Márgenes y ancho útil
 $leftMargin = 15;
 $rightMargin = 15;
 $usableWidth = 210 - $leftMargin - $rightMargin; // A4 width 210mm

 // --- Colocar el logo pequeño en una esquina ---
 $cornerLogoPath = 'C:\\xampp\\htdocs\\Ads.Mosphere\\images\\quienes-somos.jpg';
 $logoDrawH = 0;
 if (file_exists($cornerLogoPath)) {
     // pequeño, en esquina superior derecha; subirlo un poco para separar del título
     $logoW = 30; // mm
     $size = @getimagesize($cornerLogoPath);
     if ($size && $size[0] > 0) {
         $logoH = $logoW * ($size[1] / $size[0]);
     } else {
         $logoH = 12;
     }
     $x = 210 - $rightMargin - $logoW; // posicion derecha
     $logoY = 6; // subir el logo (más cerca del borde superior)
     $pdf->Image($cornerLogoPath, $x, $logoY, $logoW, $logoH);
     $logoDrawH = $logoH;
 }

// Centrar título y aplicar efecto elegante (ajustado a logo pequeño)
$pdf->SetFont('Arial','B',26);
$title = utf8_decode('IMPULSA TU MARCA CON PASIÓN');
$sub = utf8_decode('Publicidad, creatividad y resultados en un solo lugar.');

$minStartY = 28; // mínimo Y para el título si no hay logo
// Separación clara: colocar título por debajo del logo + 14 mm de espacio
if (isset($logoY) && $logoDrawH > 0) {
    $startY = $logoY + $logoDrawH + 14;
    if ($startY < $minStartY) { $startY = $minStartY; }
} else {
    $startY = $minStartY;
}
$pdf->SetY($startY);

// Sombra sutil (texto desplazado abajo) para el título
$pdf->SetTextColor(200,200,200);
$pdf->Cell(0,14,$title,0,1,'C');

// Texto principal encima
$pdf->SetXY($leftMargin, $pdf->GetY() - 14);
$pdf->SetTextColor(60,30,120);
$pdf->Cell(0,14,$title,0,1,'C');

// Subtítulo con color gris oscuro
$pdf->SetFont('Arial','I',12);
$pdf->SetTextColor(100,100,100);
$pdf->Cell(0,8,$sub,0,1,'C');

// Barra de acento morada centrada debajo del título
$barW = 120;
$barX = (210 - $barW) / 2;
$pdf->SetFillColor(123,47,247);
$pdf->Rect($barX, $pdf->GetY(), $barW, 4, 'F');
$pdf->Ln(12);


// Línea separadora
$pdf->SetDrawColor(200,200,200);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

// Helper: asegurar espacio suficiente en la página
function ensure_space($pdf, $heightNeeded) {
    // A4 height 297mm, bottom margin ~15mm => umbral aproximado
    $pageBottom = 297 - 15;
    if ($pdf->GetY() + $heightNeeded > $pageBottom) {
        $pdf->AddPage();
    }
}

// Campos principales: cajas con fondo claro
ensure_space($pdf, 80);

// Campos principales: etiquetas con fondo morado y valores en gris claro
$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(255,255,255); // texto blanco para etiquetas
$pdf->SetFillColor(123,47,247); // morado para etiquetas

$pdf->Cell(40,8,utf8_decode('Fecha'),0,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(40,40,40);
$pdf->SetFillColor(245,245,250); // fondo para valores
$pdf->Cell(0,8,utf8_decode(date('Y-m-d H:i:s')),0,1,'L',true);

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(255,255,255);
$pdf->SetFillColor(123,47,247);
$pdf->Cell(40,8,utf8_decode('Empresa'),0,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(40,40,40);
$pdf->SetFillColor(245,245,250);
$pdf->Cell(0,8,utf8_decode($empresa_s ?: '-'),0,1,'L',true);

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(255,255,255);
$pdf->SetFillColor(123,47,247);
$pdf->Cell(40,8,utf8_decode('Nombre'),0,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(40,40,40);
$pdf->SetFillColor(245,245,250);
$pdf->Cell(0,8,utf8_decode($nombre_s ?: '-'),0,1,'L',true);

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(255,255,255);
$pdf->SetFillColor(123,47,247);
$pdf->Cell(40,8,utf8_decode('Email'),0,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(40,40,40);
$pdf->SetFillColor(245,245,250);
$pdf->Cell(0,8,utf8_decode($email_s ?: '-'),0,1,'L',true);

$pdf->SetFont('Arial','B',11);
$pdf->SetTextColor(255,255,255);
$pdf->SetFillColor(123,47,247);
$pdf->Cell(40,8,utf8_decode('Teléfono'),0,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(40,40,40);
$pdf->SetFillColor(245,245,250);
$pdf->Cell(0,8,utf8_decode($telefono_s ?: '-'),0,1,'L',true);


$pdf->Ln(6);

// Mensaje en cuadro
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(123,47,247);
$pdf->Cell(0,8,utf8_decode('Mensaje'),0,1);
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(40,40,40);

// Asegurar espacio antes del mensaje (estimación alta para no romper páginas)
ensure_space($pdf, 80);

// Caja dinámica para el mensaje: escribir primero y luego dibujar el borde según alto real
$x = $pdf->GetX();
$y = $pdf->GetY();
$w = 180;

// Escribir el mensaje dentro del ancho disponible y capturar altura usada
$pdf->SetX($x + 3);
$pdf->SetY($y + 3);
$pdf->MultiCell($w - 6,6,utf8_decode($mensaje_s ?: '-'),0,'L',false);
$endY = $pdf->GetY();
$heightUsed = max(10, $endY - $y + 3);

// Dibujar borde alrededor del contenido (en la página correcta)
$pdf->SetDrawColor(200,200,200);
$pdf->SetLineWidth(0.6);
$pdf->Rect($x, $y, $w, $heightUsed, 'D');

$pdf->SetY($y + $heightUsed + 4);

// Pequeño espacio antes del footer
$pdf->Ln(8);

$pdf->Output('F', $pdfPath); // Guarda el PDF en el servidor

// === Enviar correo ===
$to = 'salvadorjorgerodrigo@gmail.com'; // Cambia por tu correo real
$subject = "Nuevo registro desde el formulario: " . ($nombre ?: 'Sin nombre');
$body = "Se ha recibido un nuevo registro:\n\n";
$body .= "Empresa: $empresa\n";
$body .= "Nombre: $nombre\n";
$body .= "Email: $email\n";
$body .= "Teléfono: $telefono\n\n";
$body .= "Mensaje:\n$mensaje\n\n";
$headers = "From: no-reply@tudominio.com\r\n";
if ($email_valido) {
    $headers .= "Reply-To: $email\r\n";
}
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

@$mail_sent = mail($to, $subject, $body, $headers);
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Registro recibido</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #7b2ff7, #be07f1ff);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            padding: 40px;
            max-width: 600px;
            width: 90%;
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
        }
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
        h2 { color: #7b2ff7; margin-bottom: 10px; font-size: 28px; }
        h3 { color: #555; margin-top: 30px; margin-bottom: 10px; }
        p { color: #333; line-height: 1.6; margin: 5px 0; }
        strong { color: #7b2ff7; }
        a.btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            background: #7b2ff7;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        a.btn:hover {
            background: #f107a3;
            transform: translateY(-3px);
        }
        small { color: #888; display: block; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>✅ Registro recibido</h2>
        <p>Gracias, <strong><?php echo $nombre_s ?: 'usuario'; ?></strong>. Hemos recibido tu solicitud.</p>

        <h3>Detalles enviados</h3>
        <p><strong>Empresa:</strong> <?php echo $empresa_s ?: '-'; ?></p>
        <p><strong>Correo:</strong> <?php echo $email_s ?: '-'; ?></p>
        <p><strong>Teléfono:</strong> <?php echo $telefono_s ?: '-'; ?></p>
        <p><strong>Mensaje:</strong><br><?php echo nl2br($mensaje_s ?: '-'); ?></p>

        <p>
            <?php if ($mail_sent): ?>
                <small>✅ La notificación ha sido enviada al correo de la empresa.</small>
            <?php else: ?>
                <small>⚠️ No se pudo enviar la notificación al correo de la empresa.</small>
            <?php endif; ?>
        </p>

        <a class="btn" href="proyecto.html">Volver al sitio web</a>
    </div>
</body>
</html>
