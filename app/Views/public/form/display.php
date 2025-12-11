<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>/css/form-public.css">
    <link rel="icon" href="<?php echo PUBLIC_URL; ?>/img/logo-bsf.png" type="image/png">
</head>

<body>

    <div class="form-wrapper">

        <div class="form-header-brand">
            <img src="<?php echo PUBLIC_URL; ?>/img/logo-bsf-nigth.png" alt="BSF" class="brand-logo">
        </div>

        <main class="form-card fade-in-up">
            <div class="form-accent-bar"></div>

            <div class="form-intro">
                <h1><?php echo $page_title; ?></h1>
                <?php if (!empty($projectDescription)): ?>
                    <p class="description"><?php echo nl2br($projectDescription); ?></p>
                <?php endif; ?>
            </div>

            <form id="public-form">
                <div id="questions-render-area">
                    <div class="loading-state">
                        <i class="fas fa-circle-notch fa-spin"></i>
                        <p>Cargando formulario...</p>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <span>ENVIAR</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </main>

        <footer class="form-footer">
            <p>&copy; <?php echo date('Y'); ?> Banderas Sin Fronteras</p>
            <p class="tiny">Plataforma de Recolección de Datos</p>
        </footer>

        <div id="success-screen" class="success-overlay hidden">
            <div class="success-box fade-in">
                <div class="check-icon-wrapper">
                    <i class="fas fa-check"></i>
                </div>
                <h2>¡Envío Exitoso!</h2>
                <p>La información ha sido registrada correctamente en el sistema.</p>
                <button onclick="location.reload()" class="btn-outline-primary">
                    <i class="fas fa-plus"></i> Enviar otro registro
                </button>
            </div>
        </div>

        <div id="gps-modal" class="gps-modal-overlay hidden">
            <div class="gps-modal-content">
                <div class="gps-modal-header">
                    <span>Seleccionar Ubicación</span>
                    <button type="button" id="btn-close-gps"
                        style="background:none;border:none;color:white;font-size:20px;cursor:pointer;">&times;</button>
                </div>
                <div id="gps-map-container" class="gps-map-container"></div>
                <div class="gps-modal-footer">
                    <div style="flex-grow:1; font-size:12px; color:#666; display:flex; align-items:center;">
                        <i class="fas fa-info-circle" style="margin-right:5px;"></i> Arrastra el marcador
                    </div>
                    <button type="button" id="btn-confirm-gps" class="btn-submit"
                        style="padding: 8px 20px; font-size:13px;">Confirmar</button>
                </div>
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script src="<?php echo PUBLIC_URL; ?>/js/form-public.js"></script>

</body>

</html>