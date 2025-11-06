<?php include PROJECT_ROOT . '/app/Views/admin/layout/header.php'; ?>

<div class="form-builder-container">
    <header class="form-builder-header">
        <h1><?php echo htmlspecialchars($proyecto['nombre']); ?></h1>
        <div class="form-builder-actions">
            <button id="preview-form-btn" class="btn-secondary">Previsualizar</button>
            <button id="save-form-btn" class="btn-primary" data-project-id="<?php echo htmlspecialchars($proyectoId); ?>">Guardar</button>
            <a href="<?php echo ADMIN_URL; ?>/proyectos/resumen/<?php echo htmlspecialchars($proyectoId); ?>" class="btn-close-builder" title="Cerrar">&times;</a>
        </div>
    </header>

    <main class="form-builder-main">
        <div id="questions-container">
        </div>

        <div id="empty-form-state" class="empty-form-state">
            <p>Este formulario está vacío.</p>
            <span>Puedes agregar preguntas haciendo clic en el signo '+' que aparece a continuación.</span>
        </div>

        <button id="add-question-btn" class="add-question-btn" aria-label="Añadir pregunta">+</button>
        
        <div id="add-question-box" class="add-question-box hidden">
            <input type="text" id="new-question-input" placeholder="Escribe tu pregunta aquí...">
            <button id="submit-question-btn" class="btn-primary">+ Agregar Pregunta</button>
            <button id="close-add-box-btn" class="close-add-box-btn" aria-label="Cerrar">&times;</button>
        </div>
    </main>

    <!-- PANEL DE CONFIGURACIÓN DE PREGUNTA -->
    <aside id="config-panel" class="config-panel hidden">
        <div class="config-panel-header">
            <h3><i class="fas fa-cog"></i> Configuración</h3>
            <button id="close-config-panel" class="close-modal">&times;</button>
        </div>
        <div class="config-panel-body">
            <nav class="config-panel-nav">
                <a href="#tab-options" class="active">Opciones de pregunta</a>
                <a href="#tab-skip-logic">Lógica de omisión</a>
                <a href="#tab-validation">Criterios de validación</a>
            </nav>
            <div class="config-panel-content">
                <div id="tab-options" class="tab-content active">
                    <div class="form-group-config">
                        <label for="config-column-name">Nombre de Columna de Datos:</label>
                        <input type="text" id="config-column-name" class="input-config">
                    </div>
                     <div class="form-group-config">
                        <label>Respuesta Obligatoria:</label>
                        <div class="radio-group">
                            <label><input type="radio" name="required" value="no"> No</label>
                            <label><input type="radio" name="required" value="yes"> Sí</label>
                            <label><input type="radio" name="required" value="logic"> Lógica personalizada</label>
                        </div>
                    </div>
                </div>
                <div id="tab-skip-logic" class="tab-content">
                    <p>Esta pregunta solo se mostrará si cumple con las siguientes condiciones.</p>
                    <div id="skip-logic-rules-container"></div>
                    <button id="add-skip-condition-btn" class="btn-secondary">+ Añadir una condición</button>
                </div>
                <div id="tab-validation" class="tab-content">
                     <p>La respuesta a esta pregunta debe cumplir las siguientes condiciones.</p>
                     <button class="btn-secondary">+ Añadir una condición</button>
                </div>
            </div>
        </div>
    </aside>
</div>

<!-- Modal para seleccionar el tipo de pregunta -->
<div id="question-type-modal" class="question-type-modal-overlay hidden">
    <div class="question-type-modal-content">
        <div class="question-type-modal-header">
            <h3 id="modal-question-title"></h3>
            <button id="close-type-modal-btn" class="close-modal">&times;</button>
        </div>
        <div class="question-type-grid">
            <button data-type="text" class="type-option"><i class="fas fa-font"></i><span>Texto</span></button>
            <button data-type="number" class="type-option"><i class="fas fa-hashtag"></i><span>Número</span></button>
            <button data-type="decimal" class="type-option"><span>1.0</span><span>Decimal</span></button>
            <button data-type="date" class="type-option"><i class="fas fa-calendar-alt"></i><span>Fecha</span></button>
            <button data-type="time" class="type-option"><i class="fas fa-clock"></i><span>Hora</span></button>
            <button data-type="datetime" class="type-option"><i class="fas fa-calendar-check"></i><span>Fecha y hora</span></button>
            <button data-type="photo" class="type-option"><i class="fas fa-camera"></i><span>Foto</span></button>
            <button data-type="audio" class="type-option"><i class="fas fa-microphone"></i><span>Audio</span></button>
            <button data-type="video" class="type-option"><i class="fas fa-video"></i><span>Video</span></button>
        </div>
    </div>
</div>

<!-- MODAL PARA LA PREVISUALIZACIÓN -->
<div id="preview-modal-overlay" class="modal-overlay hidden">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h2>Previsualización de Formulario</h2>
            <button id="close-preview-modal" class="close-modal">&times;</button>
        </div>
        <div class="modal-body" id="preview-modal-body">
        </div>
    </div>
</div>

<script>
    const initialQuestionsData = <?php echo $questionsJson; ?>;
</script>
<?php include PROJECT_ROOT . '/app/Views/admin/layout/footer.php'; ?>