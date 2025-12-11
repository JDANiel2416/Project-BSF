document.addEventListener('DOMContentLoaded', function () {

    // --- HELPER: Obtener hora local exacta ---
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

    const startTime = getLocalTime();
    const container = document.getElementById('questions-render-area');
    const form = document.getElementById('public-form');
    const btnSubmit = document.querySelector('.btn-submit');
    const successScreen = document.getElementById('success-screen');
    const questions = Array.isArray(FORM_DATA.questions) ? FORM_DATA.questions : [];

    // --- VARIABLES MAPA ---
    let mapInstance = null;
    let markerInstance = null;
    let currentGpsInput = null; // Input actual que abrió el modal
    const gpsModal = document.getElementById('gps-modal');
    const btnCloseGps = document.getElementById('btn-close-gps');
    const btnConfirmGps = document.getElementById('btn-confirm-gps');

    // --- FUNCIONES MAPA ---
    function initMap() {
        if (mapInstance) return;
        // Coordenadas por defecto (Centro de perú aprox, o neutro)
        const defaultLat = -9.19;
        const defaultLng = -75.01;

        mapInstance = L.map('gps-map-container').setView([defaultLat, defaultLng], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(mapInstance);

        // Evento clic en mapa
        mapInstance.on('click', function (e) {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });
    }

    function updateMarker(lat, lng) {
        if (markerInstance) {
            markerInstance.setLatLng([lat, lng]);
        } else {
            const customIcon = L.divIcon({
                className: 'custom-map-marker',
                html: '<i class="fas fa-location-dot" style="color: #6D4C7F; font-size: 36px; filter: drop-shadow(0 3px 4px rgba(0,0,0,0.3));"></i>',
                iconSize: [36, 36],
                iconAnchor: [18, 36]
            });
            markerInstance = L.marker([lat, lng], { 
                draggable: true,
                icon: customIcon 
            }).addTo(mapInstance);
            
            // Opcional: Actualizar al soltar
            markerInstance.on('dragend', function(e) { });
        }
    }

    function openGpsModal(inputElement) {
        currentGpsInput = inputElement;
        gpsModal.classList.remove('hidden');
        gpsModal.classList.add('active');

        // Retraso pequeño para que Leaflet calcule el tamaño del contenedor correctamente al mostrarse
        setTimeout(() => {
            initMap();
            mapInstance.invalidateSize();

            // Si ya hay valor, centrar ahí
            if (inputElement.value && inputElement.value.includes(',')) {
                const parts = inputElement.value.split(',');
                const lat = parseFloat(parts[0]);
                const lng = parseFloat(parts[1]);
                if (!isNaN(lat) && !isNaN(lng)) {
                    mapInstance.setView([lat, lng], 15);
                    updateMarker(lat, lng);
                    return;
                }
            }

            // Si no hay valor, intentar geolocalizar usuario
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        mapInstance.setView([lat, lng], 16);
                        updateMarker(lat, lng);
                    },
                    (err) => {
                        console.warn("GPS Denied/Error", err);
                        // Mantener vista por defecto
                    },
                    { enableHighAccuracy: true }
                );
            }
        }, 100);
    }

    function closeGpsModal() {
        gpsModal.classList.remove('active');
        setTimeout(() => gpsModal.classList.add('hidden'), 300);
    }

    if (btnCloseGps) btnCloseGps.onclick = closeGpsModal;

    if (btnConfirmGps) {
        btnConfirmGps.onclick = function () {
            if (markerInstance && currentGpsInput) {
                const ll = markerInstance.getLatLng();
                // FORMATO EXACTO SOLICITADO: lat, lng
                currentGpsInput.value = `${ll.lat.toFixed(6)}, ${ll.lng.toFixed(6)}`;
                // Disparar evento para validación
                currentGpsInput.dispatchEvent(new Event('change'));
                closeGpsModal();
            } else {
                alert("Por favor, selecciona un punto en el mapa.");
            }
        };
    }

    // --- VALIDACIÓN INDIVIDUAL ---
    function validateSingleQuestion(q) {
        const wrapper = document.getElementById(`q-wrapper-${q.id}`);
        if (!wrapper || wrapper.style.display === 'none') return true;

        const val = getValue(q.id);
        const inputEl = wrapper.querySelector('.form-control');
        const errorEl = wrapper.querySelector('.error-feedback');

        let isValid = true;
        let errorMsg = '';

        if (q.required === 'yes' && val === '') {
            isValid = false;
            errorMsg = 'Esta pregunta es obligatoria.';
        }

        if (q.type !== 'photo' && isValid && val !== '' && q.validation && q.validation.criteria && q.validation.criteria.length > 0) {
            let criteriaMet = true;
            for (let crit of q.validation.criteria) {
                const op = crit.operator;
                const limit = crit.value;
                const numVal = parseFloat(val);
                const numLimit = parseFloat(limit);
                const isNum = !isNaN(numVal) && !isNaN(numLimit);

                if (op === '=') { if (val != limit) criteriaMet = false; }
                else if (op === '!=') { if (val == limit) criteriaMet = false; }
                else if (isNum) {
                    if (op === '>') { if (!(numVal > numLimit)) criteriaMet = false; }
                    else if (op === '<') { if (!(numVal < numLimit)) criteriaMet = false; }
                    else if (op === '>=') { if (!(numVal >= numLimit)) criteriaMet = false; }
                    else if (op === '<=') { if (!(numVal <= numLimit)) criteriaMet = false; }
                }
            }

            if (!criteriaMet) {
                isValid = false;
                errorMsg = q.validation.errorMessage || 'El valor no cumple con los criterios.';
            }
        }

        if (!isValid) {
            if (inputEl) inputEl.style.borderColor = '#d93025';
            errorEl.textContent = errorMsg;
            errorEl.style.display = 'block';
        } else {
            if (inputEl) inputEl.style.borderColor = '#dce0e4';
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        }

        return isValid;
    }

    // --- RENDERIZAR ---
    container.innerHTML = '';

    if (questions.length === 0) {
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

            if (q.type === 'text') {
                const input = document.createElement('input');
                input.type = 'text'; input.className = 'form-control'; input.name = q.id;
                wrapper.appendChild(input); inputElement = input;
            } else if (q.type === 'number') {
                const input = document.createElement('input');
                input.type = 'number'; input.className = 'form-control'; input.name = q.id;
                wrapper.appendChild(input); inputElement = input;
            } else if (q.type === 'date') {
                const input = document.createElement('input');
                input.type = 'date'; input.className = 'form-control'; input.name = q.id;
                wrapper.appendChild(input); inputElement = input;
            } else if (q.type === 'photo') {
                const input = document.createElement('input');
                input.type = 'file'; input.accept = 'image/*'; input.className = 'form-control'; input.name = q.id;

                // Contenedor de Previsualización
                const previewDiv = document.createElement('div');
                previewDiv.className = 'img-preview-container';
                const previewImg = document.createElement('img');
                previewDiv.appendChild(previewImg);

                // Lógica de vista previa
                input.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (evt) {
                            previewImg.src = evt.target.result;
                            previewDiv.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    } else {
                        previewDiv.style.display = 'none';
                        previewImg.src = '';
                    }
                });

                wrapper.appendChild(input);
                wrapper.appendChild(previewDiv);
                inputElement = input;

            } else if(q.type === 'gps') {
                // Wrapper para el icono
                const group = document.createElement('div');
                group.className = 'gps-input-group';

                const input = document.createElement('input');
                input.type = 'text'; 
                input.placeholder = 'Toque para abrir mapa'; 
                input.readOnly = true; 
                input.className = 'form-control gps-input'; 
                input.name = q.id;
                
                const icon = document.createElement('i');
                icon.className = 'fas fa-location-crosshairs'; 

                input.onclick = function() {
                    openGpsModal(this);
                };

                group.appendChild(input);
                group.appendChild(icon);
                wrapper.appendChild(group);
            } else if (q.type === 'select') {
                const optionsContainer = document.createElement('div');
                optionsContainer.className = 'radio-group';
                const opts = Array.isArray(q.options) ? q.options : [];
                if (opts.length === 0) {
                    optionsContainer.innerHTML = '<span style="color:#999;font-size:12px;">Sin opciones definidas</span>';
                } else {
                    opts.forEach(optVal => {
                        const radioWrapper = document.createElement('label');
                        radioWrapper.className = 'radio-option';
                        const radio = document.createElement('input');
                        radio.type = 'radio'; radio.name = q.id; radio.value = optVal;
                        radio.addEventListener('change', () => validateSingleQuestion(q));
                        const span = document.createElement('span'); span.textContent = optVal;
                        radioWrapper.appendChild(radio); radioWrapper.appendChild(span);
                        optionsContainer.appendChild(radioWrapper);
                    });
                }
                wrapper.appendChild(optionsContainer);
            }

            if (inputElement) {
                inputElement.addEventListener('input', () => validateSingleQuestion(q));
                inputElement.addEventListener('blur', () => validateSingleQuestion(q));
                inputElement.addEventListener('change', () => validateSingleQuestion(q));
            }

            if (q.hint) {
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
            if (!wrapper) return;

            if (!q.logic || !q.logic.conditions || q.logic.conditions.length === 0) {
                wrapper.style.display = 'block'; return;
            }

            let showQuestion = true;
            for (let cond of q.logic.conditions) {
                const sourceId = cond.source;
                const sourceVal = getValue(sourceId);
                const op = cond.operator;
                const targetVal = cond.value;

                if (op === 'filled') { if (sourceVal === '' || sourceVal === null || sourceVal === undefined) showQuestion = false; }
                else if (op === '=') { if (sourceVal != targetVal) showQuestion = false; }
                else if (op === '!=') { if (sourceVal == targetVal) showQuestion = false; }
            }

            if (showQuestion) {
                wrapper.style.display = 'block';
            } else {
                wrapper.style.display = 'none';
                clearValue(q.id, q.type);
            }
        });
    }

    function getValue(qId) {
        const inputs = form.querySelectorAll(`[name="${qId}"]`);
        if (inputs.length === 0) return '';
        if (inputs[0].type === 'radio') {
            const checked = form.querySelector(`[name="${qId}"]:checked`);
            return checked ? checked.value : '';
        }
        if (inputs[0].type === 'file') {
            return inputs[0].files.length > 0 ? inputs[0].files[0].name : '';
        }
        return inputs[0].value;
    }

    function getFile(qId) {
        const input = form.querySelector(`input[type="file"][name="${qId}"]`);
        return (input && input.files.length > 0) ? input.files[0] : null;
    }

    function clearValue(qId, type) {
        if (type === 'select') {
            const checked = form.querySelector(`[name="${qId}"]:checked`);
            if (checked) checked.checked = false;
        } else {
            const input = form.querySelector(`[name="${qId}"]`);
            if (input) input.value = '';
        }
    }

    form.addEventListener('input', evaluateSkipLogic);
    form.addEventListener('change', evaluateSkipLogic);
    evaluateSkipLogic();

    // --- ENVÍO FINAL ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        let formIsValid = true;
        let firstErrorElement = null;

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

        if (!formIsValid) {
            if (firstErrorElement) firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const originalBtnHTML = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btnSubmit.disabled = true;

        const formData = new FormData();
        formData.append('version_id', FORM_DATA.versionId);
        formData.append('start_time', startTime);
        formData.append('end_time', getLocalTime());

        questions.forEach(q => {
            const wrapper = document.getElementById(`q-wrapper-${q.id}`);
            if (wrapper.style.display === 'none') {
                formData.append(`answers[${q.id}]`, '');
                return;
            }

            if (q.type === 'photo') {
                const file = getFile(q.id);
                if (file) {
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
                body: formData
            });
            const result = await res.json();

            if (result.success) {
                successScreen.classList.remove('hidden');
                form.style.display = 'none';
                const intro = document.querySelector('.form-intro');
                if (intro) intro.style.display = 'none';
            } else {
                alert('Error: ' + result.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalBtnHTML;
            }
        } catch (err) {
            console.error(err);
            alert('Error de conexión o archivo muy pesado');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHTML;
        }
    });
});