<?php
$page_title = 'Contacto | Banderas Sin Fronteras';
include 'includes/header.php';
?>
<header class="contact-hero">
    <div class="animated fade-in-down">
        <p>¿QUIERES MÁS INFORMACIÓN?</p>
        <h1>Escríbenos</h1>
    </div>
</header>
<div style="padding-top: 0;">
    <div class="container">
        <div class="contact-form-wrapper scroll-animated fade-in-up">
            <form action="#" method="POST">
                <div class="form-group"><label for="nombre">Nombre y Apellido</label><input type="text" id="nombre"
                        name="nombre" class="form-control" required placeholder="Ej: Ana García"></div>
                <div class="form-group"><label for="email">Correo Electrónico</label><input type="email" id="email"
                        name="email" class="form-control" required placeholder="Ej: ana.garcia@email.com"></div>
                <div class="form-group"><label for="mensaje">Solicitud o Mensaje</label><textarea id="mensaje"
                        name="mensaje" class="form-control" required
                        placeholder="Hola, me gustaría saber más sobre..."></textarea></div>
                <div style="text-align: center;"><button type="submit" class="btn btn-primary">Enviar Mensaje</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>