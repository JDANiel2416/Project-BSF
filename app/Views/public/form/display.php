<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>/css/form-public.css">
</head>
<body>

<div class="form-container">
    <header class="form-header">
        <img src="<?php echo PUBLIC_URL; ?>/img/logo-bsf-nigth.png" alt="BSF" class="brand-logo">
    </header>

    <main class="form-card fade-in">
        <div class="form-intro">
            <h1><?php echo $page_title; ?></h1>
            <?php if(!empty($projectDescription)): ?>
                <p class="description"><?php echo nl2br($projectDescription); ?></p>
            <?php endif; ?>
        </div>

        <form id="public-form">
            <div id="questions-render-area">
                <div style="text-align:center; padding:20px; color:#999;">Cargando formulario...</div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Enviar
                </button>
            </div>
        </form>
    </main>

    <footer class="form-footer">
        <p>Powered by BSF Data Collection</p>
    </footer>

    <div id="success-screen" class="success-overlay hidden">
        <div class="success-box fade-in">
            <div class="check-icon"><i class="fas fa-check-circle"></i></div>
            <h2 style="margin-top:0; color:#333;">¡Envío Exitoso!</h2>
            <p style="color:#666;">Gracias por completar este formulario.</p>
            <button onclick="location.reload()" class="btn-outline">Enviar otra respuesta</button>
        </div>
    </div>
</div>

<script>
    const FORM_DATA = {
        projectId: <?php echo $projectId; ?>,
        versionId: <?php echo $formVersionId; ?>,
        questions: <?php echo $questionsJson; ?>,
        submitUrl: "<?php echo PUBLIC_URL; ?>/f/<?php echo $projectId; ?>/submit"
    };
</script>
<script src="<?php echo PUBLIC_URL; ?>/js/form-public.js"></script>

</body>
</html>