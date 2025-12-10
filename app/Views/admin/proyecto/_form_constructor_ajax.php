<?php
$questionsJson = $questionsJson ?? '[]';
$pid = $proyecto['id'] ?? 0;
?>
<div class="form-builder-wrapper" data-project-name="<?php echo htmlspecialchars($proyecto['nombre']); ?>"
    data-questions='<?php echo $questionsJson; ?>'>

    <header class="builder-header">
        <div class="builder-title">Constructor de Preguntas</div>
        <div class="builder-actions">
            <button id="btn-preview-builder" class="btn-secondary"><i class="fas fa-eye"></i> Vista previa</button>
            <button id="btn-save-builder" class="btn-primary" data-id="<?php echo $pid; ?>"><i class="fas fa-save"></i>
                Guardar</button>
            <button id="btn-close-builder" class="btn-close-builder" data-id="<?php echo $pid; ?>"
                title="Cerrar">&times;</button>
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
                <input type="text" id="new-question-input" placeholder="Escribe el título de la pregunta..."
                    autocomplete="off">
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

    <div id="settings-question-modal" class="modal-overlay hidden">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2>Configuración</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body-split">
                <div class="settings-sidebar">
                    <ul>
                        <li class="active" data-panel="options">Opciones de pregunta</li>
                        <li data-panel="logic">Lógica de omisión</li>
                        <li data-panel="validation">Criterios de validación</li>
                    </ul>
                </div>
                <div class="settings-content-area">

                    <div id="panel-options" class="settings-panel active">
                        <div class="form-group">
                            <label>Nombre de Columna de Datos:</label>
                            <input type="text" id="set-col-name" class="form-control" readonly
                                style="background-color:#f9f9f9; cursor:not-allowed;">
                            <small style="color:#999; display:block; margin-top:3px;">Generado automáticamente
                                (identificador único).</small>
                        </div>

                        <div class="form-group">
                            <label>Sugerencia Adicional:</label>
                            <input type="text" id="set-hint" class="form-control"
                                placeholder="Ej: Ingrese el valor en soles...">
                        </div>

                        <div class="form-group">
                            <label style="margin-bottom:10px; display:block;">Respuesta Obligatoria:</label>
                            <div class="radio-options-vertical">
                                <label class="radio-label">
                                    <input type="radio" name="set_required" value="yes"> Sí
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="set_required" value="no"> No
                                </label>
                                <label class="radio-label disabled" style="opacity:0.6;">
                                    <input type="radio" name="set_required" value="custom" disabled> Lógica
                                    personalizada
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="panel-logic" class="settings-panel">
                        <label style="font-weight:600; margin-bottom:15px; display:block;">Esta pregunta se mostrará si
                            se cumplen las siguientes condiciones:</label>
                        <div id="logic-conditions-container"></div>
                        <button type="button" id="btn-add-condition" class="btn-link-plus">+ Añadir una
                            condición</button>
                    </div>

                    <div id="panel-validation" class="settings-panel">
                        <label style="font-weight:600; margin-bottom:15px; display:block;">Esta pregunta en solitario se
                            mostrará completa con las siguientes:</label>

                        <div id="validation-container">
                        </div>

                        <button type="button" id="btn-add-validation" class="btn-link-plus"
                            style="margin-bottom: 25px;">+ Añadir un concepto</button>

                        <div style="border-top: 1px solid #eee; padding-top: 20px; margin-top: 10px;">
                            <div class="form-group"
                                style="display:flex; align-items:center; gap:15px; margin-bottom:0;">
                                <label style="min-width:120px; text-align:right; margin:0;">Mensaje De Error:</label>
                                <input type="text" id="set-val-error-msg" class="form-control" style="margin-top:0;"
                                    placeholder="Ej: El valor no cumple con los criterios...">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary close-modal">Cancelar</button>
                <button type="button" id="btn-save-settings" class="btn-primary">Guardar Configuración</button>
            </div>
        </div>
    </div>

</div>