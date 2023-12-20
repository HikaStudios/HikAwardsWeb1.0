<?php
session_start();
include("database.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['votos'])) {
    $votos = $_POST['votos'];

    $userPoints = getUserPoints();
    var_dump($userPoints);

    for ($i = 0; $i <= 2; $i++) {
        $id_twitch = $_SESSION['twitch_id'];
        $nombre_twitch = $_SESSION['twitch_display_name'];
        $valor_voto = 0;
        if ($i == 0) {
            $valor_voto = $userPoints['first_place'];
        } elseif ($i == 1) {
            $valor_voto = $userPoints['second_place'];
        } elseif ($i == 2) {
            $valor_voto = $userPoints['third_place'];
        }


        $categoria_id = $_SESSION['categoria'];
        $candidato_id = $votos[$i];
        $sql = "INSERT INTO votos (id_twitch, nombre_twitch, valor_voto, id_candidato, id_categoria, fecha_voto) 
                VALUES ('$id_twitch', '$nombre_twitch', $valor_voto, $candidato_id, $categoria_id, NOW())";

        if ($conn->query($sql) === TRUE) {
            // Éxito al guardar el voto
        } else {
            echo 'Error al guardar el voto: ' . $conn->error;
        }
    }

    $conn->close();
} else {
    echo 'Error al procesar la solicitud.';
}
?>
