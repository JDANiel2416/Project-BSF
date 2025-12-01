document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('questions-render-area');
    const form = document.getElementById('public-form');
    const btnSubmit = document.querySelector('.btn-submit');
    const successScreen = document.getElementById('success-screen');
    
    // Asegurar que es un array
    const questions = Array.isArray(FORM_DATA.questions) ? FORM_DATA.questions : [];

    // 1. Renderizar Preguntas
    container.innerHTML = ''; // Limpiar placeholder

    if(questions.length === 0) {
        container.innerHTML = '<p style="color:#777;text-align:center;padding:40px;">Este formulario no tiene preguntas configuradas.</p>';
        btnSubmit.style.display = 'none';
    } else {
        questions.forEach(q => {
            const wrapper = document.createElement('div');
            wrapper.className = 'q-item';
            
            // Etiqueta
            const label = document.createElement('label');
            label.className = 'q-label';
            label.innerHTML = q.text + (q.required === 'yes' ? '<span class="req-star">*</span>' : '');
            wrapper.appendChild(label);

            // Input
            let input;
            if(q.type === 'text') {
                input = document.createElement('input');
                input.type = 'text';
            } else if(q.type === 'number') {
                input = document.createElement('input');
                input.type = 'number';
            } else if(q.type === 'date') {
                input = document.createElement('input');
                input.type = 'date';
            } else if(q.type === 'photo') {
                input = document.createElement('input');
                input.type = 'file';
                input.accept = 'image/*';
            } else if(q.type === 'gps') {
                input = document.createElement('input');
                input.type = 'text';
                input.placeholder = 'Click para obtener ubicación';
                input.readOnly = true;
                input.style.cursor = 'pointer';
                input.onclick = function() {
                    this.value = 'Obteniendo...';
                    if (!navigator.geolocation) {
                        this.value = "GPS no soportado";
                        return;
                    }
                    navigator.geolocation.getCurrentPosition(
                        (pos) => { this.value = pos.coords.latitude + ', ' + pos.coords.longitude; },
                        (err) => { this.value = ''; alert('Error GPS: ' + err.message); }
                    );
                };
            } else if(q.type === 'select') {
                input = document.createElement('select');
                input.innerHTML = '<option value="">Seleccionar...</option><option>Opción A</option><option>Opción B</option>';
            } else {
                input = document.createElement('input');
                input.type = 'text';
            }

            input.className = 'form-control';
            // Usar ID como nombre para garantizar unicidad
            input.name = q.id; 
            if(q.required === 'yes') input.required = true;

            wrapper.appendChild(input);
            container.appendChild(wrapper);
        });
    }

    // 2. Enviar
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const answers = {};
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(inp => {
            if(inp.type === 'file') {
                answers[inp.name] = inp.files.length > 0 ? inp.files[0].name : '';
            } else {
                answers[inp.name] = inp.value;
            }
        });

        const originalBtnHTML = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btnSubmit.disabled = true;

        try {
            const res = await fetch(FORM_DATA.submitUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    version_id: FORM_DATA.versionId,
                    answers: answers
                })
            });
            
            const result = await res.json();
            
            if(result.success) {
                // Mostrar pantalla de éxito
                successScreen.classList.remove('hidden');
            } else {
                alert('Error: ' + result.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = originalBtnHTML;
            }
        } catch(err) {
            console.error(err);
            alert('Error de conexión');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalBtnHTML;
        }
    });
});