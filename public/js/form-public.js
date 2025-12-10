document.addEventListener('DOMContentLoaded', function() {
    
    // --- HELPER: Obtener hora local exacta (YYYY-MM-DD HH:MM:SS) ---
    const getLocalTime = () => {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    };

    // 1. CAPTURAR HORA DE INICIO
    const startTime = getLocalTime();

    const container = document.getElementById('questions-render-area');
    const form = document.getElementById('public-form');
    const btnSubmit = document.querySelector('.btn-submit');
    const successScreen = document.getElementById('success-screen');
    
    const questions = Array.isArray(FORM_DATA.questions) ? FORM_DATA.questions : [];

    // --- VALIDACIÓN INDIVIDUAL ---
    function validateSingleQuestion(q) {
        const wrapper = document.getElementById(`q-wrapper-${q.id}`);
        if(!wrapper || wrapper.style.display === 'none') return true;

        const val = getValue(q.id); // Para archivos, esto devuelve el nombre, suficiente para validar 'required'
        const inputEl = wrapper.querySelector('.form-control'); 
        const errorEl = wrapper.querySelector('.error-feedback');
        
        let isValid = true;
        let errorMsg = '';

        // Validación de requerido (si es file, getValue devuelve nombre si hay archivo seleccionado)
        if(q.required === 'yes' && val === '') {
            isValid = false;
            errorMsg = 'Esta pregunta es obligatoria.';
        }

        // Validaciones numéricas/lógicas (solo si no es archivo)
        if(q.type !== 'photo' && isValid && val !== '' && q.validation && q.validation.criteria && q.validation.criteria.length > 0) {
            let criteriaMet = true;
            for(let crit of q.validation.criteria) {
                const op = crit.operator;
                const limit = crit.value;
                const numVal = parseFloat(val);
                const numLimit = parseFloat(limit);
                const isNum = !isNaN(numVal) && !isNaN(numLimit);

                if(op === '=') { if(val != limit) criteriaMet = false; }
                else if(op === '!=') { if(val == limit) criteriaMet = false; }
                else if(isNum) {
                    if(op === '>') { if(!(numVal > numLimit)) criteriaMet = false; }
                    else if(op === '<') { if(!(numVal < numLimit)) criteriaMet = false; }
                    else if(op === '>=') { if(!(numVal >= numLimit)) criteriaMet = false; }
                    else if(op === '<=') { if(!(numVal <= numLimit)) criteriaMet = false; }
                }
            }

            if(!criteriaMet) {
                isValid = false;
                errorMsg = q.validation.errorMessage || 'El valor no cumple con los criterios.';
            }
        }

        if(!isValid) {
            if(inputEl) inputEl.style.borderColor = '#d93025';
            errorEl.textContent = errorMsg;
            errorEl.style.display = 'block';
        } else {
            if(inputEl) inputEl.style.borderColor = '#ccc';
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        }

        return isValid;
    }

    // --- RENDERIZAR ---
    container.innerHTML = ''; 

    if(questions.length === 0) {
        container.innerHTML = '<p style="color:#777;text-align:center;padding:40px;">Este formulario no tiene preguntas configuradas.</p>';
        btnSubmit.style.display = 'none';
    } else {
        questions.forEach(q => {
            const wrapper = document.createElement('div');
            wrapper.className = 'q-item fade-in';
            wrapper.id = `q-wrapper-${q.id}`; 
            
            const label = document.createElement('label');
            label.className = 'q-label';
            label.innerHTML = q.text + (q.required === 'yes' ? '<span class="req-star">*</span>' : '');
            wrapper.appendChild(label);

            let inputElement = null;

            if(q.type === 'text') {
                const input = document.createElement('input');
                input.type = 'text'; input.className = 'form-control'; input.name = q.id;
                wrapper.appendChild(input); inputElement = input;
            } else if(q.type === 'number') {
                const input = document.createElement('input');
                input.type = 'number'; input.className = 'form-control'; input.name = q.id;
                wrapper.appendChild(input); inputElement = input;
            } else if(q.type === 'date') {
                const input = document.createElement('input');
                input.type = 'date'; input.className = 'form-control'; input.name = q.id;
                wrapper.appendChild(input); inputElement = input;
            } else if(q.type === 'photo') {
                const input = document.createElement('input');
                input.type = 'file'; input.accept = 'image/*'; input.className = 'form-control'; input.name = q.id;
                wrapper.appendChild(input); inputElement = input; // Listener change funciona para files
            } else if(q.type === 'gps') {
                const input = document.createElement('input');
                input.type = 'text'; input.placeholder = 'Click para obtener ubicación'; input.readOnly = true;
                input.className = 'form-control gps-input'; input.style.cursor = 'pointer'; input.name = q.id;
                input.onclick = function() {
                    this.value = 'Obteniendo...';
                    if (!navigator.geolocation) { this.value = "GPS no soportado"; return; }
                    navigator.geolocation.getCurrentPosition(
                        (pos) => { this.value = pos.coords.latitude + ', ' + pos.coords.longitude; validateSingleQuestion(q); },
                        (err) => { this.value = ''; alert('Error GPS: ' + err.message); }
                    );
                };
                wrapper.appendChild(input);
            } else if(q.type === 'select') {
                const optionsContainer = document.createElement('div');
                optionsContainer.className = 'radio-group';
                const opts = Array.isArray(q.options) ? q.options : [];
                if(opts.length === 0) {
                    optionsContainer.innerHTML = '<span style="color:#999;font-size:12px;">Sin opciones definidas</span>';
                } else {
                    opts.forEach(optVal => {
                        const radioWrapper = document.createElement('label');
                        radioWrapper.className = 'radio-option';
                        radioWrapper.style.display = 'flex'; radioWrapper.style.alignItems = 'center'; radioWrapper.style.marginBottom = '8px'; radioWrapper.style.cursor = 'pointer';
                        const radio = document.createElement('input');
                        radio.type = 'radio'; radio.name = q.id; radio.value = optVal; radio.style.marginRight = '10px';
                        radio.addEventListener('change', () => validateSingleQuestion(q));
                        const span = document.createElement('span'); span.textContent = optVal;
                        radioWrapper.appendChild(radio); radioWrapper.appendChild(span);
                        optionsContainer.appendChild(radioWrapper);
                    });
                }
                wrapper.appendChild(optionsContainer);
            } 

            if(inputElement) {
                inputElement.addEventListener('input', () => validateSingleQuestion(q));
                inputElement.addEventListener('blur', () => validateSingleQuestion(q));
                inputElement.addEventListener('change', () => validateSingleQuestion(q));
            }

            if(q.hint) {
                const hint = document.createElement('small');
                hint.className = 'q-hint';
                hint.style.display = 'block'; hint.style.color = '#666'; hint.style.marginTop = '5px'; hint.style.fontSize = '12px';
                hint.textContent = q.hint;
                wrapper.appendChild(hint);
            }

            const errorMsg = document.createElement('div');
            errorMsg.className = 'error-feedback';
            errorMsg.style.color = '#d93025'; errorMsg.style.fontSize = '12px'; errorMsg.style.marginTop = '5px'; errorMsg.style.display = 'none';
            wrapper.appendChild(errorMsg);

            container.appendChild(wrapper);
        });
    }

    // --- LÓGICA DE OMISIÓN ---
    function evaluateSkipLogic() {
        questions.forEach(q => {
            const wrapper = document.getElementById(`q-wrapper-${q.id}`);
            if(!wrapper) return;

            if(!q.logic || !q.logic.conditions || q.logic.conditions.length === 0) {
                wrapper.style.display = 'block'; return;
            }

            let showQuestion = true;
            for(let cond of q.logic.conditions) {
                const sourceId = cond.source;
                const sourceVal = getValue(sourceId);
                const op = cond.operator;
                const targetVal = cond.value;

                if (op === 'filled') { if (sourceVal === '' || sourceVal === null || sourceVal === undefined) showQuestion = false; }
                else if (op === '=') { if (sourceVal != targetVal) showQuestion = false; }
                else if (op === '!=') { if (sourceVal == targetVal) showQuestion = false; }
            }

            if(showQuestion) {
                wrapper.style.display = 'block';
            } else {
                wrapper.style.display = 'none';
                clearValue(q.id, q.type);
                const errorEl = wrapper.querySelector('.error-feedback');
                const inputEl = wrapper.querySelector('.form-control');
                if(errorEl) errorEl.style.display = 'none';
                if(inputEl) inputEl.style.borderColor = '#ccc';
            }
        });
    }

    // Helper: Obtener valor (para texto o validación)
    function getValue(qId) {
        const inputs = form.querySelectorAll(`[name="${qId}"]`);
        if(inputs.length === 0) return '';
        
        // Radio buttons
        if(inputs[0].type === 'radio') {
            const checked = form.querySelector(`[name="${qId}"]:checked`);
            return checked ? checked.value : '';
        }
        
        // File inputs: validamos si tiene archivo
        if(inputs[0].type === 'file') {
            return inputs[0].files.length > 0 ? inputs[0].files[0].name : '';
        }

        return inputs[0].value;
    }

    // Helper: Obtener el objeto File real (para envío)
    function getFile(qId) {
        const input = form.querySelector(`input[type="file"][name="${qId}"]`);
        return (input && input.files.length > 0) ? input.files[0] : null;
    }

    function clearValue(qId, type) {
        if(type === 'select') {
            const checked = form.querySelector(`[name="${qId}"]:checked`);
            if(checked) checked.checked = false;
        } else {
            const input = form.querySelector(`[name="${qId}"]`);
            if(input) input.value = '';
        }
    }

    form.addEventListener('input', evaluateSkipLogic);
    form.addEventListener('change', evaluateSkipLogic);
    evaluateSkipLogic();

    // --- ENVÍO FINAL (MODIFICADO PARA FORMDATA) ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        let formIsValid = true;
        let firstErrorElement = null;
        
        // Validar primero
        questions.forEach(q => {
            const isValid = validateSingleQuestion(q);
            if (!isValid) {
                formIsValid = false;
                const wrapper = document.getElementById(`q-wrapper-${q.id}`);
                if (!firstErrorElement && wrapper.style.display !== 'none') {
                    firstErrorElement = wrapper;
                }
            }
        });

        if(!formIsValid) {
            if(firstErrorElement) firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const originalBtnHTML = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btnSubmit.disabled = true;

        // Construir FormData para enviar archivos reales
        const formData = new FormData();
        formData.append('version_id', FORM_DATA.versionId);
        formData.append('start_time', startTime);
        formData.append('end_time', getLocalTime());

        // Agregar respuestas
        questions.forEach(q => {
            const wrapper = document.getElementById(`q-wrapper-${q.id}`);
            // Si está oculta, enviamos vacío o no enviamos
            if(wrapper.style.display === 'none') {
                formData.append(`answers[${q.id}]`, '');
                return;
            }

            if(q.type === 'photo') {
                const file = getFile(q.id);
                if(file) {
                    formData.append(`answers[${q.id}]`, file);
                } else {
                    formData.append(`answers[${q.id}]`, '');
                }
            } else {
                const val = getValue(q.id);
                formData.append(`answers[${q.id}]`, val);
            }
        });

        try {
            const res = await fetch(FORM_DATA.submitUrl, {
                method: 'POST',
                // NO poner Content-Type: application/json, fetch lo pone automático para FormData
                body: formData
            });
            
            const result = await res.json();
            
            if(result.success) {
                successScreen.classList.remove('hidden');
                form.style.display = 'none'; 
                const intro = document.querySelector('.form-intro');
                if(intro) intro.style.display = 'none';
            } else {
                alert('Error: ' + result.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalBtnHTML;
            }
        } catch(err) {
            console.error(err);
            alert('Error de conexión o archivo muy pesado');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHTML;
        }
    });
});