<?php include PROJECT_ROOT . '/app/Views/public/layout/header.php'; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<style>
    #map { height: 600px; width: 100%; border-radius: var(--radio-borde); box-shadow: var(--sombra-suave); }
</style>

<div class="content-section">
    <div class="container">
        <div class="page-intro">
            <h1 class="scroll-animated fade-in-up">Nuestra Ubicación</h1>
            <p class="section-subtitle scroll-animated fade-in-up" style="transition-delay: 0.1s;">
                Encuéntranos en el corazón de Villa El Salvador. Aquí es donde nuestras iniciativas cobran vida.
            </p>
        </div>
        <div id="map" class="scroll-animated fade-in-up" style="transition-delay: 0.2s;"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    const lat = <?php echo $data['lat']; ?>;
    const lng = <?php echo $data['lng']; ?>;
    const zoom = <?php echo $data['zoom']; ?>;
    const popupMessage = "<?php echo $data['popup_message']; ?>";

    const map = L.map('map').setView([lat, lng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup(popupMessage)
        .openPopup();
</script>

<?php include PROJECT_ROOT . '/app/Views/public/layout/footer.php'; ?>