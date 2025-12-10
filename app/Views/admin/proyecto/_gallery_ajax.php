<?php
$photoQuestions = array_filter($questions, function ($q) {
    return ($q['type'] ?? '') === 'photo';
});
$galleryItems = [];
if (!empty($submissions)) {
    foreach ($submissions as $sub) {
        $data = json_decode($sub['submission_data'] ?? '{}', true);
        foreach ($photoQuestions as $pq) {
            $qid = $pq['id'];

            if (!empty($data[$qid])) {
                $path = $data[$qid];

                if (is_string($path) && strpos($path, 'uploads/') === 0) {
                    $galleryItems[] = [
                        'id' => uniqid(),
                        'src' => PUBLIC_URL . '/' . $path,
                        'q_id' => $qid,
                        'q_text' => $pq['text'],
                        'date' => $sub['_submission_time'] ?? $sub['created_at'] ?? '',
                        'sub_id' => $sub['_id'] ?? $sub['id']
                    ];
                }
            }
        }
    }
}
usort($galleryItems, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<div class="gallery-wrapper">
    <div class="gallery-tools-bar">
        <div class="gallery-filter-group" style="flex-grow: 1; max-width: 400px;">
            <label><i class="fas fa-camera"></i> Filtrar por Pregunta:</label>
            <select id="gallery-filter-question" class="form-control">
                <option value="all">Todas las fotos</option>
                <?php foreach ($photoQuestions as $pq): ?>
                    <option value="<?php echo $pq['id']; ?>"><?php echo htmlspecialchars($pq['text']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="gallery-filter-group">
            <label><i class="fas fa-calendar-alt"></i> Desde:</label>
            <input type="date" id="gallery-filter-date-start" class="form-control">
        </div>

        <div class="gallery-filter-group">
            <label><i class="fas fa-calendar-alt"></i> Hasta:</label>
            <input type="date" id="gallery-filter-date-end" class="form-control">
        </div>

        <div class="gallery-filter-group" style="justify-content:flex-end;">
            <label>&nbsp;</label>
            <button id="btn-reset-gallery" class="btn-secondary" title="Limpiar filtros">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <div id="gallery-grid-container" class="gallery-grid"></div>

    <div id="gallery-empty-state" class="data-placeholder hidden">
        <i class="fas fa-images"></i>
        <p>No se encontraron imágenes con los filtros seleccionados.</p>
    </div>
</div>

<script>
    window.currentGalleryData = <?php echo json_encode($galleryItems); ?>;
</script>