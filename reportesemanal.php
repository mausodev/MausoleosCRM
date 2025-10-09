<?php 
require './controlador/conexion.php';
require './controlador/access_control.php';

// Verificar acceso y obtener datos de sesión
$accessData = verificarAcceso();
$acceso = $accessData['acceso'];
$id_asesor = $accessData['id_asesor'];
$inicial = $accessData['inicial'];
$supervisor = $accessData['supervisor'];
$correo = $accessData['correo'];
$sucursal = $accessData['sucursal'];
$departamento = $accessData['departamento'];
$puesto = $accessData['puesto'];
$rol_venta = $accessData['rol_venta'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Semanal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }
        h1, h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 10px;
            padding: 20px;
            margin-bottom: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .grid-item {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        .grid-header {
            font-weight: bold;
            background-color: #e0e0e0;
        }
        .tachometer {
            margin: 20px 0;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }
        .tachometer-item {
            width: 45%;
            height: 200px;
            border: 1px solid #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            margin-bottom: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(4, 1fr);
            }
            .tachometer-item {
                width: 100%;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php if (!$acceso): ?>
    <?php echo generarOverlayAccesoDenegado(); ?>
    <?php endif; ?>
    
    <h1>Reporte Semanal</h1>

    <!-- Tacómetros -->
    <div class="tachometer">
        <div class="tachometer-item">
            <canvas id="proyeccionDiaria"></canvas>
        </div>
        <div class="tachometer-item">
            <canvas id="ventaDiaria"></canvas>
        </div>
    </div>

    <!-- Ventas Futuras -->
    <h2>Ventas Futuras</h2>
    <div class="grid-container">
        <div class="grid-item grid-header">PLAZA</div>
        <div class="grid-item grid-header">META</div>
        <div class="grid-item grid-header">VENTA</div>
        <div class="grid-item grid-header">AV %</div>
        <div class="grid-item grid-header">EMBUDO</div>
        <div class="grid-item grid-header">META ACUM</div>
        <div class="grid-item grid-header">VENTA ACUM</div>
        <div class="grid-item grid-header">AVANCE ACUMULADO</div>
        
        <!-- Datos de ejemplo - Serán reemplazados por datos dinámicos -->
        <div class="grid-item">Plaza 2</div>
        <div class="grid-item">1200</div>
        <div class="grid-item">900</div>
        <div class="grid-item">75%</div>
        <div class="grid-item">300</div>
        <div class="grid-item">6000</div>
        <div class="grid-item">4500</div>
        <div class="grid-item">70%</div>
    </div>

    <!-- Ventas Periféricos -->
    <h2>Ventas Periféricos</h2>
    <div class="grid-container">
        <div class="grid-item grid-header">PLAZA</div>
        <div class="grid-item grid-header">META</div>
        <div class="grid-item grid-header">VENTA</div>
        <div class="grid-item grid-header">AV %</div>
        <div class="grid-item grid-header">EMBUDO</div>
        <div class="grid-item grid-header">META ACUM</div>
        <div class="grid-item grid-header">VENTA ACUM</div>
        <div class="grid-item grid-header">AVANCE ACUMULADO</div>
        
        <!-- Datos de ejemplo - Serán reemplazados por datos dinámicos -->
        <div class="grid-item">Plaza 3</div>
        <div class="grid-item">1100</div>
        <div class="grid-item">850</div>
        <div class="grid-item">77%</div>
        <div class="grid-item">250</div>
        <div class="grid-item">5500</div>
        <div class="grid-item">4200</div>
        <div class="grid-item">76%</div>
    </div>
</body>
<script>
    // Example data for tachometer charts
    const data = {
        labels: ['Progress'],
        datasets: [{
            data: [75, 25],
            backgroundColor: ['#4caf50', '#e0e0e0'],
            borderWidth: 0
        }]
    };

    const options = {
        circumference: Math.PI,
        rotation: Math.PI,
        cutout: '80%',
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.raw + '%';
                    }
                }
            }
        }
    };

    // Create the tachometer charts
    const ctx1 = document.getElementById('proyeccionDiaria').getContext('2d');
    const ctx2 = document.getElementById('ventaDiaria').getContext('2d');

    // Add labels to the charts
    const proyeccionChart = new Chart(ctx1, {
        type: 'doughnut',
        data: {
            labels: ['Progress'],
            datasets: [{
                data: [75, 25],
                backgroundColor: ['#4caf50', '#e0e0e0'],
                borderWidth: 0
            }]
        },
        options: {
            ...options,
            plugins: {
                ...options.plugins,
                title: {
                    display: true,
                    text: 'Proyección Diaria',
                    position: 'bottom',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });

    const ventaChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Progress'],
            datasets: [{
                data: [80, 20],
                backgroundColor: ['#2196f3', '#e0e0e0'],
                borderWidth: 0
            }]
        },
        options: {
            ...options,
            plugins: {
                ...options.plugins,
                title: {
                    display: true,
                    text: 'Venta Diaria',
                    position: 'bottom',
                    font: {
                        size: 16
                    }
                }
            }
        }
    });

    // Add percentage text in the center of the charts
    function addCenterText(chart, text) {
        Chart.register({
            id: 'centerTextPlugin',
            afterDraw: function(chart) {
                const width = chart.width;
                const height = chart.height;
                const ctx = chart.ctx;
                
                ctx.restore();
                const fontSize = (height / 114).toFixed(2);
                ctx.font = fontSize + "em sans-serif";
                ctx.textBaseline = "middle";
                
                const text = chart.data.datasets[0].data[0] + "%";
                const textX = Math.round((width - ctx.measureText(text).width) / 2);
                const textY = height / 2;
                
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        });
    }
    
    addCenterText(proyeccionChart);
    addCenterText(ventaChart);
</script>

<?php if (!$acceso): ?>
<?php echo generarScriptDeshabilitarElementos(); ?>
<?php endif; ?>
</html>
