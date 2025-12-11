<div class="fade-in" style="height:100%; display:flex; flex-direction:column;">

    <?php if (!$hasGpsField && empty($markers)): ?>
        <div class="map-no-gps-alert" style="margin:20px;">
            <i class="fas fa-map-marked-alt"></i>
            <h3>No hay datos geográficos</h3>
            <p>Este formulario no tiene preguntas GPS o no se han registrado respuestas con ubicación válida.</p>
        </div>
    <?php else: ?>

        <div id="map-wrapper" class="map-container-wrapper"
            style="flex-grow:1; width:100%; position:relative; min-height:500px;">

            <button id="btn-toggle-map-fs" class="btn-map-fullscreen" title="Pantalla completa">
                <i class="fas fa-expand"></i>
            </button>

            <button id="btn-toggle-layers" class="btn-map-layers" title="Cambiar vista (Calle/Satélite)">
                <i class="fas fa-layer-group"></i>
            </button>

            <div
                style="position:absolute; top:12px; left:50px; z-index:1000; background:rgba(255,255,255,0.95); padding:6px 12px; border-radius:4px; font-size:12px; box-shadow:0 2px 5px rgba(0,0,0,0.2); border-left: 4px solid #6D4C7F;">
                <strong style="color:#6D4C7F; font-size:13px;"><?php echo count($markers); ?></strong> registros
            </div>

            <div id="project-map-view" style="width:100%; height:100%; z-index:1;"></div>
        </div>

        <div id="modal-submission-detail" class="modal-overlay hidden">
            <div class="modal-content modal-detail-view" style="max-width: 850px !important; height: 95vh;">
                
                <div class="modal-header-clean">
                    <div>
                        <h2 id="modal-detail-title" style="margin:0; font-size:18px; color:#333;">Detalles del Registro</h2>
                        <span id="modal-detail-subtitle" style="font-size:13px; color:#888;">ID: #---</span>
                    </div>
                    <button class="close-modal" style="font-size:24px; color:#aaa; cursor:pointer;">&times;</button>
                </div>

                <div class="modal-body-scrollable">
                    <div id="meta-container"
                        style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:30px; display:flex; gap:30px; border:1px solid #e9ecef;">
                    </div>
                    <h3
                        style="font-size:14px; color:#6D4C7F; margin-bottom:20px; border-bottom:2px solid #f0f0f0; padding-bottom:10px;">
                        DATOS DEL FORMULARIO</h3>
                    <div id="detail-container" class="info-grid"></div>
                </div>

                <div class="modal-footer-integrated">
                    <button id="btn-map-prev" class="nav-btn">
                        <i class="fas fa-arrow-left"></i> Anterior
                    </button>

                    <span id="map-nav-counter" class="nav-counter-badge">1 de X</span>

                    <button id="btn-map-next" class="nav-btn">
                        Siguiente <i class="fas fa-arrow-right"></i>
                    </button>
                </div>

            </div>
        </div>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <script>
            (function () {
                // --- 1. CARGA DE RECURSOS ---
                const cssId = 'leaflet-css';
                if (!document.getElementById(cssId)) {
                    const head = document.getElementsByTagName('head')[0];
                    const link = document.createElement('link');
                    link.id = cssId; link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    head.appendChild(link);
                }

                const markersData = <?php echo json_encode($markers); ?>;
                const questionsDef = <?php echo json_encode($questions); ?>;
                
                let mapInstance = null;
                let layerOSM = null;
                let layerSat = null;
                let currentLayerType = 'osm';
                
                // Índice actual para navegación
                let currentMarkerIndex = 0; 

                const checkLeaflet = setInterval(() => {
                    if (typeof L !== 'undefined') {
                        clearInterval(checkLeaflet);
                        initMap();
                    }
                }, 100);

                function initMap() {
                    const container = document.getElementById('project-map-view');
                    if (container && container._leaflet_id) container._leaflet_id = null;

                    mapInstance = L.map('project-map-view').setView([-9.19, -75.01], 5);

                    // Capas
                    layerOSM = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap',
                        maxZoom: 19
                    });

                    layerSat = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        attribution: 'Tiles &copy; Esri',
                        maxZoom: 18
                    });

                    layerOSM.addTo(mapInstance);

                    // Puntos
                    if (markersData.length > 0) {
                        const bounds = [];
                        markersData.forEach((pt, index) => {
                            const circle = L.circleMarker([pt.lat, pt.lng], {
                                color: '#ffffff', fillColor: '#6D4C7F', fillOpacity: 0.9, weight: 2, radius: 8
                            }).addTo(mapInstance);

                            circle.on('mouseover', function () {
                                this.setStyle({ fillColor: '#a251eeff', radius: 10 });
                                this.getElement().style.cursor = 'pointer';
                            });
                            circle.on('mouseout', function () {
                                this.setStyle({ fillColor: '#6D4C7F', radius: 8 });
                            });
                            
                            // Abrir modal por índice
                            circle.on('click', () => { openMapDetail(index); });

                            bounds.push([pt.lat, pt.lng]);
                        });
                        mapInstance.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                    }
                }

                // Cambiar Capa
                const btnLayers = document.getElementById('btn-toggle-layers');
                if (btnLayers) {
                    btnLayers.onclick = function () {
                        if (!mapInstance) return;
                        if (currentLayerType === 'osm') {
                            mapInstance.removeLayer(layerOSM);
                            mapInstance.addLayer(layerSat);
                            currentLayerType = 'sat';
                            btnLayers.innerHTML = '<i class="fas fa-map"></i>';
                            btnLayers.title = "Ver Mapa de Calles";
                        } else {
                            mapInstance.removeLayer(layerSat);
                            mapInstance.addLayer(layerOSM);
                            currentLayerType = 'osm';
                            btnLayers.innerHTML = '<i class="fas fa-layer-group"></i>';
                            btnLayers.title = "Ver Satélite";
                        }
                    };
                }

                // Pantalla Completa (Lógica "Secuestro de Modal")
                const btnFs = document.getElementById('btn-toggle-map-fs');
                const wrapper = document.getElementById('map-wrapper');
                const modal = document.getElementById('modal-submission-detail');
                const originalParent = wrapper ? wrapper.parentNode : document.body;

                if (btnFs && wrapper && modal) {
                    btnFs.onclick = function () {
                        wrapper.classList.toggle('fullscreen-mode');
                        const isFullscreen = wrapper.classList.contains('fullscreen-mode');
                        const icon = btnFs.querySelector('i');

                        if (isFullscreen) {
                            icon.classList.remove('fa-expand'); icon.classList.add('fa-compress');
                            wrapper.appendChild(modal); // Mover modal DENTRO del mapa
                        } else {
                            icon.classList.remove('fa-compress'); icon.classList.add('fa-expand');
                            originalParent.appendChild(modal); // Devolver modal a su sitio
                        }
                        setTimeout(() => { if (mapInstance) mapInstance.invalidateSize(); }, 100);
                    };
                }

                // Abrir detalle
                window.openMapDetail = function (index) {
                    if (index < 0 || index >= markersData.length) return;
                    
                    currentMarkerIndex = index;
                    renderModalContent();
                    
                    // Centrar mapa suavemente (opcional)
                    const pt = markersData[index];
                    if(mapInstance) {
                        mapInstance.panTo([pt.lat, pt.lng]);
                    }

                    modal.classList.remove('hidden');
                };

                // Renderizar contenido
                function renderModalContent() {
                    const data = markersData[currentMarkerIndex];
                    const meta = document.getElementById('meta-container');
                    const detail = document.getElementById('detail-container');
                    const title = document.getElementById('modal-detail-subtitle');
                    const counter = document.getElementById('map-nav-counter');
                    const btnPrev = document.getElementById('btn-map-prev');
                    const btnNext = document.getElementById('btn-map-next');

                    // Contador "X de Y"
                    counter.textContent = `${currentMarkerIndex + 1} de ${markersData.length}`;
                    
                    // Estado botones
                    btnPrev.disabled = (currentMarkerIndex === 0);
                    btnNext.disabled = (currentMarkerIndex === markersData.length - 1);

                    // Datos Cabecera
                    title.textContent = `ID: #${data.id} | Enviado: ${data.date}`;

                    let st = '<span style="color:#fb6340; font-weight:600;"><i class="fas fa-clock"></i> En revisión</span>';
                    if (data.status === 'approved') st = '<span style="color:#2dce89; font-weight:600;"><i class="fas fa-check-circle"></i> Aprobado</span>';
                    if (data.status === 'rejected') st = '<span style="color:#f5365c; font-weight:600;"><i class="fas fa-times-circle"></i> Rechazado</span>';

                    meta.innerHTML = `<div><span class="info-label">Estado</span><div class="info-value no-border">${st}</div></div>
                        <div><span class="info-label">Usuario</span><div class="info-value no-border">${data.user}</div></div>
                        <div><span class="info-label">Ubicación</span><div class="info-value no-border">${data.lat}, ${data.lng}</div></div>`;

                    // Preguntas y Respuestas
                    detail.innerHTML = '';
                    (data.answers ? questionsDef : []).forEach(q => {
                        let val = data.answers[q.id];
                        let isEmpty = (val == null || val === '');
                        let isPhoto = (q.type === 'photo');
                        let content = val;
                        
                        if (isEmpty) {
                            content = '<span style="color:#ccc; font-style:italic;">Sin respuesta</span>';
                        } else if (isPhoto && val.startsWith('uploads/')) {
                            const url = '<?php echo PUBLIC_URL; ?>/' + val;
                            content = `<a href="${url}" target="_blank"><img src="${url}" class="evidence-img" alt="Foto"></a>`;
                        } else if (q.type === 'date') {
                            // Formato simple si es fecha
                            content = val; 
                        }

                        const div = document.createElement('div');
                        div.className = `info-item ${isPhoto || (val && val.length > 50) ? 'full-width' : ''}`;
                        div.innerHTML = `<span class="info-label">${q.text}</span><div class="info-value ${isPhoto ? 'no-border' : ''}">${content}</div>`;
                        detail.appendChild(div);
                    });
                }

                // Listeners Botones Navegación
                const btnPrev = document.getElementById('btn-map-prev');
                const btnNext = document.getElementById('btn-map-next');

                if(btnPrev) {
                    btnPrev.onclick = function() {
                        if(currentMarkerIndex > 0) {
                            currentMarkerIndex--;
                            renderModalContent(); // Solo renderizamos, no movemos mapa para no marear
                            // Opcional: window.openMapDetail(currentMarkerIndex); si quieres mover mapa
                        }
                    };
                }

                if(btnNext) {
                    btnNext.onclick = function() {
                        if(currentMarkerIndex < markersData.length - 1) {
                            currentMarkerIndex++;
                            renderModalContent();
                        }
                    };
                }

                document.querySelectorAll('.close-modal').forEach(b => b.onclick = () => document.getElementById('modal-submission-detail').classList.add('hidden'));
            })();
        </script>
    <?php endif; ?>
</div>