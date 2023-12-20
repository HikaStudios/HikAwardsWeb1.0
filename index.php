<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Hika Awards 2023</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php 
    include("header.php");
?>

<div class="container text-center mt-5">
    <h1 class="display-4">¡Bienvenido a los Hika Awards 2023!</h1>
    <p class="lead">Celebremos juntos con premios increíbles.</p>
    <p class="lead">El evento está programado para comenzar el 10 de enero de 2023.</p>

    <div class="my-4">
    <p>Sigue a Hikarilof en sus redes sociales:</p>
    <ul class="list-inline">
        <li class="list-inline-item"><a href="https://www.twitch.tv/hikarilof" target="_blank" class="text-dark"><i class="fab fa-twitch"></i> @hikarilof (Twitch)</a></li>
        <li class="list-inline-item"><a href="https://www.instagram.com/hikarilof/" target="_blank" class="text-dark"><i class="fab fa-instagram"></i> @hikarilof (Instagram)</a></li>
        <li class="list-inline-item"><a href="https://twitter.com/hikarilof" target="_blank" class="text-dark"><i class="fab fa-twitter"></i> @hikarilof (Twitter)</a></li>
    </ul>
</div>


    <p class="lead">¡Únete a nuestro Discord oficial para estar al tanto de todas las novedades!</p>
    <a href="https://discord.gg/hikarilof" target="_blank" class="btn btn-info btn-lg">Discord de Hikarilof</a>

    <p class="mt-4">¿Ya estás emocionado por los premios? Inicia sesión con Twitch y participa.</p>

    <div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Puntuaciones</h5>
            <ul>
                <li>Puntos al primer puesto: 3</li>
                <li>Puntos al segundo puesto: 2</li>
                <li>Puntos al tercer puesto: 1</li>
                <li>Si eres seguidor hace más de 6 meses: 1 punto más</li>
                <li>Si eres seguidor hace más de 1 año: 2 puntos más</li>
                <li>Si tienes VIP: 3 puntos más</li>
                <li>Si tienes Suscripción: 5 puntos más</li>
            </ul>
        </div>
    </div>
</div>

    
    <?php 
    if (isset($_SESSION['twitch_id'])) {
        echo '<a href="form.php" class="btn btn-success btn-lg">Cargar Formulario</a>';
    } else {
        echo '<a href="twitch_login.php" class="btn btn-primary btn-lg">Iniciar sesión con Twitch</a>';
    }
    ?>
   
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkUwe5U02rflj" crossorigin="anonymous">

</body>
</html>
