<?php
$votes = [];
include("head.php");
showHead("Votación");
echo '<body>';
include("header.php");
?>

<div class="container-fluid mt-5">
    <?php
    if (!isset($_SESSION['twitch_display_name'])) {
        header('Location: index.php');
        exit();
    }

    include("database.php");



    $_SESSION['categoria'] = 2;
    $categoria_id = $_SESSION['categoria'];


    if (!getHikarilofTokenFromDB($conn)) {
        echo '<script>alert("La aplicación aún no se ha iniciado");</script>';
       echo '<script>window.location.href = "index.php";</script>';
        exit();
    }


    $votos_usuario = $conn->query("SELECT id_candidato, valor_voto FROM votos
    WHERE id_twitch = '{$_SESSION['twitch_id']}' AND id_categoria = $categoria_id");

$candidatos = $conn->query("SELECT c.id_candidato, c.nombre_candidato FROM candidato c
            INNER JOIN candidatoxcategoria cxc ON c.id_candidato = cxc.id_candidato
            WHERE id_categoria = $categoria_id
            ORDER BY nombre_candidato LIMIT 5;");



if ($votos_usuario->num_rows > 0) {
    $votos_usuario_ids = [];

    while ($voto = $votos_usuario->fetch_assoc()) {
        $votos_usuario_ids[$voto['id_candidato']] = $voto['valor_voto'];
    }
    
    echo '<div class="container-fluid mt-5">';
    echo '<div class="card-deck">';
    while ($candidato = $candidatos->fetch_assoc()) {
        echo '<div class="card' . (in_array($candidato['id_candidato'], $votes) ? ' selected' : '') . '" style="width: 18rem;">
                <img src="./img/candidato.png" class="card-img-top" alt="Imagen del candidato">
                <div class="card-body">
                    <h5 class="card-title">' . $candidato['nombre_candidato'] . '</h5>
                    <div class="place-overlay"></div>';
    
        if (array_key_exists($candidato['id_candidato'], $votos_usuario_ids)) {
            // Mostrar mensaje con el valor de votos directamente
            echo '<p>Has votado a este candidato con ' . $votos_usuario_ids[$candidato['id_candidato']] . ' puntos</p>';
        }
    
        echo '</div></div>';
    }



    echo '</div>';
    echo '</div>';


    echo '<div class="mt-3 text-center d-flex justify-content-center">
    <button class="btn btn-danger disabled" id="noMod">Solo VIPS y Subs pueden modificar su voto</button>
</div>';
   
    
} else {
   
    $categoria_valida = $conn->query("SELECT id_categoria, nombre_categoria FROM categoria WHERE id_categoria = $categoria_id");

    if ($categoria_valida->num_rows > 0) {
        $categoria = $categoria_valida->fetch_assoc();

        $candidatos = $conn->query("SELECT c.id_candidato, c.nombre_candidato FROM candidato c
            INNER JOIN candidatoxcategoria cxc ON c.id_candidato = cxc.id_candidato
            WHERE id_categoria = $categoria_id
            ORDER BY nombre_candidato LIMIT 5;");

        if ($candidatos) {
            if ($candidatos->num_rows > 0) {
                echo '<div class="card-deck">';
                while ($candidato = $candidatos->fetch_assoc()) {
                    echo '<div class="card' . (in_array($candidato['id_candidato'], $votes) ? ' selected' : '') . '" style="width: 18rem;">
                            <img src="./img/candidato.png" class="card-img-top" alt="Imagen del candidato">
                            <div class="card-body">
                                <h5 class="card-title">' . $candidato['nombre_candidato'] . '</h5>
                                <button class="btn btn-primary vote-button" data-id="' . $candidato['id_candidato'] . '">Votar</button>
                                <div class="place-overlay"></div>
                            </div>
                          </div>';
                }
                echo '</div>';
            } else {
                echo '<p>No hay candidatos en esta categoría.</p>';

            }
        } else {
            echo 'Error en la consulta: ' . $conn->error;
        }


        echo '<div class="mt-3 text-center d-flex justify-content-center">
        <button class="btn btn-success" id="enviarVoto">Enviar Voto</button>
        <button class="btn btn-danger" id="deseleccionar">Deseleccionar</button>
    </div>';
    } else {
        echo '<p>Categoría no válida.</p>';
    }

}
$conn->close();

echo '<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Tus votos valen:</h5>
            <ul>
                <li>Voto al primer puesto: ' . getUserPoints()['first_place'] . ' Puntos </li>
                <li>Voto al segundo puesto: ' . getUserPoints()['second_place'] . ' Puntos</li>
                <li>Voto al tercer puesto: ' . getUserPoints()['third_place'] . ' Puntos</li>
            </ul>
        </div>
    </div>
</div>';

?>

    
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var votes = [];

    document.querySelectorAll(".vote-button").forEach(function(button) {
    button.addEventListener("click", function() {
        // Verificar si el botón está deshabilitado
        if (!this.classList.contains("disabled")) {
            var candidateId = this.getAttribute("data-id");

            if (votes.includes(candidateId)) {
                alert("Ya has votado por este candidato en una posición.");
            } else {
                if (votes.length < 3) {
                    votes.push(candidateId);

                    var placeOverlay = this.parentNode.querySelector(".place-overlay");
                    placeOverlay.innerHTML = getPlaceOverlay(votes.length);
                } else {
                    alert("Ya has alcanzado el límite de 3 votos.");
                }
            }
        } 
    });
});


    document.getElementById("enviarVoto").addEventListener("click", function() {
        if (votes.length >= 3) {

            this.disabled = true;

            $.ajax({
                url: 'send_votos.php', 
                method: 'POST',
                data: { votos: votes 
                },
                success: function(response) {
                    alert("Votos guardados!");
                    console.log(response);
                    setTimeout(function() {
                    location.reload();
                }, 1000);
                },
                error: function(error) {
                    console.error(error);
                    alert("Error al enviar votos.");
                }
            });
        } else {
            alert("Debes seleccionar 3 ganadores.");
        }
    });

    document.getElementById("deseleccionar").addEventListener("click", function() {
        votes = [];
        document.querySelectorAll(".place-overlay").forEach(function(placeOverlay) {
            placeOverlay.innerHTML = '';
        });

        document.querySelectorAll(".card").forEach(function(card) {
            card.classList.remove("selected");
        });
    });

    function getPlaceOverlay(place) {
        switch (place) {
            case 1:
                return '<img src="./img/first_place.png" alt="Primer Lugar" class="place-overlay-img">';
            case 2:
                return '<img src="./img/second_place.png" alt="Segundo Lugar" class="place-overlay-img">';
            case 3:
                return '<img src="./img/third_place.png" alt="Tercer Lugar" class="place-overlay-img">';
            default:
                return '';
        }
    }
});
</script>
</body>
</html>
