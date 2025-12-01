<div id="modal-nuevo-proyecto" class="modal-overlay hidden">
    <div class="modal-content fade-in-down">
        <div class="modal-header" style="padding: 10px 20px;">
            <h2 style="font-size: 16px;">Nuevo Proyecto</h2>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <div class="options-grid">
                <div class="option-card" id="opt-crear-cero">
                    <div class="icon"><i class="fas fa-edit"></i></div>
                    <h3>Crear Formulario</h3>
                    <p>Empieza desde cero con el constructor visual.</p>
                </div>
                <div class="option-card disabled" title="Próximamente">
                    <div class="icon"><i class="fas fa-file-excel"></i></div>
                    <h3>Cargar XLSForm</h3>
                    <p>Importar definición desde Excel.</p>
                </div>
                <div class="option-card disabled" title="Próximamente">
                    <div class="icon"><i class="fas fa-copy"></i></div>
                    <h3>Usar Plantilla</h3>
                    <p>Basado en proyectos existentes.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-detalles-proyecto" class="modal-overlay hidden">
    <div class="modal-content fade-in-down">
        <div class="modal-header" style="padding: 10px 20px;">
            <h2 style="font-size: 16px;">Detalles del Proyecto</h2>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-create-project">
                <div class="form-group">
                    <label for="p-nombre">Nombre del Proyecto <span class="required">*</span></label>
                    <input type="text" id="p-nombre" name="nombre" class="form-control" required placeholder="Ej. Censo 2025">
                </div>
                <div class="form-group">
                    <label for="p-descripcion">Descripción</label>
                    <textarea id="p-descripcion" name="descripcion" class="form-control" rows="3" placeholder="Breve resumen..." style="resize: none;"></textarea>
                </div>
                <div class="row">
                    <div class="col-6">
                         <div class="form-group">
                            <label for="p-sector">Sector <span class="required">*</span></label>
                            <select id="p-sector" name="sector" class="form-control" required>
                                <option value="">Seleccionar...</option>
                                <option value="Salud">Salud</option>
                                <option value="Educación">Educación</option>
                                <option value="Protección">Protección</option>
                                <option value="Medio Ambiente">Medio Ambiente</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="p-pais">País <span class="required">*</span></label>
                            <select id="p-pais" name="pais" class="form-control" required>
                                <option value="">Seleccionar...</option>
                                <option value="Perú">Perú</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Colombia">Colombia</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" id="btn-back-options">Atrás</button>
                    <button type="submit" class="btn-primary" id="btn-confirm-create">
                        <span class="btn-text">Crear Proyecto</span>
                        <span class="loader hidden"><i class="fas fa-circle-notch fa-spin"></i></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>