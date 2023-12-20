<?php

$servername = "localhost:3306";
$username = "root";
$password = "";
$dbname = "hikawards";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}


function getHikarilofTokenFromDB($conn) {

    // Consulta SQL para obtener el access token de Hikarilof
    $query = "SELECT token FROM tokens where id_token=1";

    // Ejecuta la consulta
    $result = $conn->query($query);

    // Verifica si la consulta fue exitosa
    if ($result) {
        // Obtiene la fila como un array asociativo
        $row = $result->fetch_assoc();

        // Libera el resultado y cierra la conexión
        $result->free();

        // Retorna el access token si existe, de lo contrario, retorna false
        return $row ? $row['token'] : false;
    } else {
        // Maneja el error si la consulta no fue exitosa
        echo 'Error en la consulta SQL: ' . $conn->error;
        return false;
    }
}

function setHikarilofTokenToDB($conn,$access_token) {
    // Consulta SQL para insertar o actualizar el access token de Hikarilof
    $query = "INSERT INTO tokens (id_token, token) VALUES (1, '$access_token') ON DUPLICATE KEY UPDATE token = '$access_token'";

    // Ejecuta la consulta
    $result = $conn->query($query);

    // Verifica si la consulta fue exitosa
    if ($result) {
        // Cierra la conexión
        return true;
    } else {
        // Maneja el error si la consulta no fue exitosa
        echo 'Error en la consulta SQL: ' . $conn->error;
        return false;
    }
}


function getUserPoints() {
    $points = 3; // Puntos predeterminados

    // Verifica si el usuario es seguidor hace más de 6 meses y agrega puntos
    if ($_SESSION['twitch_follow_date'] && strtotime($_SESSION['twitch_follow_date']) < strtotime('-6 months')) {
        $points += 1; // Agrega 1 punto extra
    }

    // Verifica si el usuario es seguidor hace más de 1 año y agrega puntos
    if ($_SESSION['twitch_follow_date'] && strtotime($_SESSION['twitch_follow_date']) < strtotime('-1 year')) {
        $points += 2; // Agrega 2 puntos extra
    }

    // Verifica si el usuario es VIP y agrega puntos
    if ($_SESSION['twitch_vip']) {
        $points += 3; // Agrega 3 puntos extra
    }

    // Verifica si el usuario es suscriptor y agrega puntos
    if ($_SESSION['twitch_suscribed']) {
        $points += 5; // Agrega 5 puntos extra
    }

    return [
        'first_place' => $points,
        'second_place' => $points - 1,
        'third_place' => $points - 2,
    ];
}

?>