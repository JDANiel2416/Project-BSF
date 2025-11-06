document.addEventListener('DOMContentLoaded', function() {

    const modalOpciones = document.getElementById('modal-nuevo-proyecto');
    const modalDetalles = document.getElementById('modal-detalles-proyecto');
    const nuevoProyectoBtn = document.querySelector('.btn-nuevo');
    const crearBorradorBtn = document.getElementById('crear-borrador-btn');
    const regresarBtn = document.getElementById('btn-regresar');
    const closeButtons = document.querySelectorAll('.close-modal');

    // Función para cerrar modal
    function closeAllModals() {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.classList.add('hidden');
        });
    }

    if (nuevoProyectoBtn) {
        nuevoProyectoBtn.addEventListener('click', function(event) {
            event.preventDefault();
            if (modalOpciones) {
                modalOpciones.classList.remove('hidden');
            }
        });
    }

    if (crearBorradorBtn) {
        crearBorradorBtn.addEventListener('click', function(event) {
            event.preventDefault();
            if (modalOpciones) modalOpciones.classList.add('hidden');
            if (modalDetalles) modalDetalles.classList.remove('hidden');
        });
    }

    if (regresarBtn) {
        regresarBtn.addEventListener('click', function(event) {
            event.preventDefault();
            if (modalDetalles) modalDetalles.classList.add('hidden');
            if (modalOpciones) modalOpciones.classList.remove('hidden');
        });
    }

    closeButtons.forEach(button => {
        button.addEventListener('click', closeAllModals);
    });

    document.querySelectorAll('.modal-overlay').forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    closeAllModals();
                }
            });
        }
    });

    // --- BARRA LATERAL RETRÁCTIL ---
    const dashboardContainer = document.querySelector('.dashboard-container.sidebar-container');

    if (dashboardContainer) {
        const toggleBtn = document.getElementById('toggle-sidebar-btn');
        const sidebar = document.querySelector('.sidebar');
        
        const saveSidebarState = () => {
            const isCollapsed = dashboardContainer.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
        };

        const loadSidebarState = () => {
            const savedState = localStorage.getItem('sidebarState');
            if (savedState === 'collapsed') {
                dashboardContainer.classList.add('sidebar-collapsed');
            }
        };

        toggleBtn.addEventListener('click', () => {
            dashboardContainer.classList.toggle('sidebar-collapsed');
            saveSidebarState();
        });
        loadSidebarState();
    }

    // --- CREAR PROYECTO Y REDIRIGIR ---
    const btnCrearProyecto = document.getElementById('btn-crear-proyecto');

    if (btnCrearProyecto) {
        btnCrearProyecto.addEventListener('click', async function(event) {
            event.preventDefault();

            const nombre = document.getElementById('proyecto-nombre').value.trim();
            const sector = document.getElementById('proyecto-sector').value;
            const pais = document.getElementById('proyecto-pais').value;
            const descripcion = document.getElementById('proyecto-descripcion').value.trim();

            if (!nombre || !sector || !pais) {
                alert('Por favor, completa todos los campos obligatorios: Nombre, Sector y País.');
                return;
            }
            
            this.disabled = true;
            this.textContent = 'Creando...';

            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('sector', sector);
            formData.append('pais', pais);
            formData.append('descripcion', descripcion);
            
            try {
                const response = await fetch(`${ADMIN_URL}/proyectos/crear`, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();

                if (result.success) {
                    window.location.href = `${ADMIN_URL}/formularios/constructor/${result.projectId}`;
                } else {
                    alert('Error: ' + result.message);
                    this.disabled = false;
                    this.textContent = 'Crear proyecto';
                }

            } catch (error) {
                console.error('Error en la petición:', error);
                alert('Ocurrió un error inesperado al crear el proyecto.');
                this.disabled = false;
                this.textContent = 'Crear proyecto';
            }
        });
    }

    // --- CONSTRUCTOR DE FORMULARIOS ---
    const formBuilder = document.querySelector('.form-builder-container');
    if (formBuilder) {
        let questions = typeof initialQuestionsData !== 'undefined' ? initialQuestionsData : [];
        let currentlyEditingQuestionId = null;

        const questionsContainer = document.getElementById('questions-container');
        const emptyState = document.getElementById('empty-form-state');
        const addQuestionBtn = document.getElementById('add-question-btn');
        const addQuestionBox = document.getElementById('add-question-box');
        const newQuestionInput = document.getElementById('new-question-input');
        const submitQuestionBtn = document.getElementById('submit-question-btn');
        const closeAddBoxBtn = document.getElementById('close-add-box-btn');
        const typeModal = document.getElementById('question-type-modal');
        const modalQuestionTitle = document.getElementById('modal-question-title');
        const typeOptions = document.querySelectorAll('.type-option');
        const saveFormBtn = document.getElementById('save-form-btn');
        
        const configPanel = document.getElementById('config-panel');
        const closeConfigPanelBtn = document.getElementById('close-config-panel');
        const configTabs = document.querySelectorAll('.config-panel-nav a');
        const configColumnNameInput = document.getElementById('config-column-name');
        const configRequiredRadios = document.querySelectorAll('input[name="required"]');

        const previewBtn = document.getElementById('preview-form-btn');
        const previewModal = document.getElementById('preview-modal-overlay');
        const previewModalBody = document.getElementById('preview-modal-body');
        renderQuestions();

        function renderQuestions() {
            questionsContainer.innerHTML = '';
            emptyState.classList.toggle('hidden', questions.length > 0);

            questions.forEach(q => {
                const questionHTML = `
                    <div class="question-item" data-id="${q.id}">
                        <div class="question-item-type">${q.type}</div>
                        <div class="question-item-text">
                            <span class="question-title" data-action="edit">${q.text}</span>
                            <span class="question-hint">Columna: ${q.columnName}</span>
                        </div>
                        <div class="question-item-controls">
                            <button title="Configurar" class="btn-config"><i class="fas fa-cog"></i></button>
                            <button title="Duplicar" class="btn-duplicate"><i class="fas fa-clone"></i></button>
                            <button title="Eliminar" class="btn-delete"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                `;
                questionsContainer.insertAdjacentHTML('beforeend', questionHTML);
            });
        }

        function addQuestion(text, type) {
            const newQuestion = {
                id: 'q_' + Date.now(),
                text: text,
                type: type,
                columnName: text.toLowerCase().replace(/[^a-z0-9]+/g, '_').slice(0, 30) || 'pregunta',
                required: 'no',
                skipLogic: [],
                validation: []
            };
            questions.push(newQuestion);
            renderQuestions();
        }
        
        function enterEditMode(questionId, spanElement) {
            if (spanElement.querySelector('input')) return;
            const currentText = spanElement.textContent;
            spanElement.innerHTML = '';
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentText;
            input.className = 'edit-question-input';
            const saveEdit = () => {
                const newText = input.value.trim();
                if (newText) {
                    const questionIndex = questions.findIndex(q => q.id === questionId);
                    if(questionIndex !== -1) questions[questionIndex].text = newText;
                }
                renderQuestions();
            };
            input.addEventListener('blur', saveEdit);
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); saveEdit(); }
                if (e.key === 'Escape') { renderQuestions(); }
            });
            spanElement.appendChild(input);
            input.focus();
            input.select();
        }

        questionsContainer.addEventListener('click', e => {
            const questionItem = e.target.closest('.question-item');
            if (!questionItem) return;
            const questionId = questionItem.dataset.id;
            
            if (e.target.dataset.action === 'edit') {
                enterEditMode(questionId, e.target);
                return;
            }
            if (e.target.closest('.btn-delete')) {
                questions = questions.filter(q => q.id !== questionId);
                renderQuestions();
                if (currentlyEditingQuestionId === questionId) configPanel.classList.add('hidden');
            }
            if (e.target.closest('.btn-duplicate')) {
                const originalIndex = questions.findIndex(q => q.id === questionId);
                const originalQuestion = questions[originalIndex];
                const duplicatedQuestion = JSON.parse(JSON.stringify(originalQuestion));
                duplicatedQuestion.id = 'q_' + Date.now();
                duplicatedQuestion.columnName += '_copy';
                questions.splice(originalIndex + 1, 0, duplicatedQuestion);
                renderQuestions();
            }
            if (e.target.closest('.btn-config')) {
                currentlyEditingQuestionId = questionId;
                const questionData = questions.find(q => q.id === questionId);
                populateConfigPanel(questionData);
                configPanel.classList.remove('hidden');
            }
        });

        addQuestionBtn.addEventListener('click', () => {
            addQuestionBox.classList.remove('hidden');
            addQuestionBox.style.display = 'flex';
            addQuestionBtn.classList.add('hidden');
            newQuestionInput.focus();
        });

        closeAddBoxBtn.addEventListener('click', () => {
            addQuestionBox.classList.add('hidden');
            addQuestionBtn.classList.remove('hidden');
        });

        let tempQuestionText = '';
        submitQuestionBtn.addEventListener('click', () => {
            const text = newQuestionInput.value.trim();
            if (text) {
                tempQuestionText = text;
                modalQuestionTitle.textContent = `¿Qué tipo de pregunta es "${text}"?`;
                typeModal.classList.remove('hidden');
            }
        });
        
        typeOptions.forEach(option => {
            option.addEventListener('click', function() {
                addQuestion(tempQuestionText, this.dataset.type);
                typeModal.classList.add('hidden');
                newQuestionInput.value = '';
                newQuestionInput.focus();
            });
        });

        function populateConfigPanel(question) {
            configColumnNameInput.value = question.columnName;
            configRequiredRadios.forEach(radio => {
                radio.checked = radio.value === question.required;
            });
        }
        
        function updateQuestionFromConfig() {
            if (!currentlyEditingQuestionId) return;
            const questionIndex = questions.findIndex(q => q.id === currentlyEditingQuestionId);
            if (questionIndex === -1) return;
            questions[questionIndex].columnName = configColumnNameInput.value;
            questions[questionIndex].required = document.querySelector('input[name="required"]:checked').value;
            renderQuestions();
        }
        
        configColumnNameInput.addEventListener('input', updateQuestionFromConfig);
        configRequiredRadios.forEach(radio => radio.addEventListener('change', updateQuestionFromConfig));
        closeConfigPanelBtn.addEventListener('click', () => configPanel.classList.add('hidden'));
        configTabs.forEach(tab => {
            tab.addEventListener('click', e => {
                e.preventDefault();
                configTabs.forEach(t => t.classList.remove('active'));
                e.target.classList.add('active');
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                document.querySelector(e.target.getAttribute('href')).classList.add('active');
            });
        });
        
        saveFormBtn.addEventListener('click', async () => {
            const projectId = saveFormBtn.dataset.projectId;
            const formDefinitionJson = JSON.stringify(questions);
            saveFormBtn.disabled = true;
            saveFormBtn.textContent = 'Guardando...';
            const formData = new FormData();
            formData.append('form_definition', formDefinitionJson);
            try {
                const response = await fetch(`${ADMIN_URL}/formularios/guardar/${projectId}`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert('Formulario guardado con éxito.');
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error al guardar:', error);
                alert('Ocurrió un error inesperado al guardar el formulario.');
            } finally {
                saveFormBtn.disabled = false;
                saveFormBtn.textContent = 'Guardar';
            }
        });
        
        // --- Modal de Previsualización ---
        function renderPreview() {
            previewModalBody.innerHTML = '';
            if (questions.length === 0) {
                previewModalBody.innerHTML = '<p>Aún no has añadido ninguna pregunta al formulario.</p>';
                return;
            }
            const form = document.createElement('form');
            form.addEventListener('submit', e => {
                e.preventDefault();
                alert('Esto es solo una previsualización. Los datos no se enviarán.');
            });
            questions.forEach(q => {
                const questionDiv = document.createElement('div');
                questionDiv.className = 'preview-question';
                const label = document.createElement('label');
                label.setAttribute('for', q.id);
                label.textContent = q.text;
                if (q.required === 'yes') {
                    const asterisk = document.createElement('span');
                    asterisk.className = 'required-asterisk';
                    asterisk.textContent = ' *';
                    label.appendChild(asterisk);
                }
                questionDiv.appendChild(label);
                let input;
                switch (q.type) {
                    case 'number': input = document.createElement('input'); input.type = 'number'; break;
                    case 'date': input = document.createElement('input'); input.type = 'date'; break;
                    case 'time': input = document.createElement('input'); input.type = 'time'; break;
                    case 'photo': input = document.createElement('input'); input.type = 'file'; input.accept = 'image/*'; break;
                    default: input = document.createElement('input'); input.type = 'text';
                }
                input.id = q.id;
                input.name = q.columnName;
                input.className = 'form-control';
                questionDiv.appendChild(input);
                form.appendChild(questionDiv);
            });
            const submitButton = document.createElement('button');
            submitButton.type = 'submit';
            submitButton.className = 'btn-modal-primary';
            submitButton.textContent = 'Enviar';
            form.appendChild(submitButton);
            previewModalBody.appendChild(form);
        }

        previewBtn.addEventListener('click', () => {
            renderPreview();
            previewModal.classList.remove('hidden');
        });
    }

});