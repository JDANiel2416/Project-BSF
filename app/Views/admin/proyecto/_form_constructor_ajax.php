<?php 
$questionsJson = $questionsJson ?? '[]'; 
$pid = $proyecto['id'] ?? 0;
?>
<div class="form-builder-wrapper" data-project-name="<?php echo htmlspecialchars($proyecto['nombre']); ?>" data-questions='<?php echo $questionsJson; ?>'>
    
    <header class="builder-header">
        <div class="builder-title">Constructor de Preguntas</div>
        <div class="builder-actions">
            <button id="btn-preview-builder" class="btn-secondary"><i class="fas fa-eye"></i> Vista previa</button>
            <button id="btn-save-builder" class="btn-primary" data-id="<?php echo $pid; ?>"><i class="fas fa-save"></i> Guardar</button>
            <button id="btn-close-builder" class="btn-close-builder" data-id="<?php echo $pid; ?>" title="Cerrar">&times;</button>
        </div>
    </header>

    <div class="builder-area">
        
        <div id="questions-container" class="questions-list"></div>

        <div class="add-question-zone">
            
            <div id="empty-state-container" class="empty-state-text hidden">
                <p>Tu formulario está vacío</p>
                <span>Usa el botón + para agregar tu primera pregunta.</span>
            </div>

            <button id="add-question-btn" class="btn-circle-add" title="Añadir pregunta">
                <i class="fas fa-plus"></i>
            </button>

            <div id="add-question-box" class="add-question-panel hidden">
                <input type="text" id="new-question-input" placeholder="Escribe el título de la pregunta..." autocomplete="off">
                <button id="confirm-add-btn" class="btn-add-confirm">Añadir</button>
            </div>
        </div>
    </div>

    <div id="question-type-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Selecciona el tipo</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="options-grid">
                    <div class="option-card" data-type="text">
                        <div class="icon"><i class="fas fa-font"></i></div>
                        <h3>Texto</h3>
                    </div>
                    <div class="option-card" data-type="number">
                        <div class="icon"><i class="fas fa-hashtag"></i></div>
                        <h3>Número</h3>
                    </div>
                    <div class="option-card" data-type="date">
                        <div class="icon"><i class="fas fa-calendar"></i></div>
                        <h3>Fecha</h3>
                    </div>
                    <div class="option-card" data-type="photo">
                        <div class="icon"><i class="fas fa-camera"></i></div>
                        <h3>Foto</h3>
                    </div>
                    <div class="option-card" data-type="gps">
                        <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                        <h3>GPS</h3>
                    </div>
                    <div class="option-card" data-type="select">
                        <div class="icon"><i class="fas fa-list"></i></div>
                        <h3>Selección</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>