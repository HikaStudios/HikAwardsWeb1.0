<?php
session_start();
// Verifica si el usuario ha iniciado sesión con Twitch
if (isset($_SESSION['twitch_id'])) {
    echo '<header class="fixed-top"> <nav class="navbar navbar-expand-lg navbar-light">';
    echo '<div class="d-flex align-items-center">';
    echo '<img src="' . $_SESSION['twitch_profile_image_url'] . '" alt="Profile Picture" class="img-fluid rounded-circle profile-picture">';
    echo '<span class="profile-name ml-2">' . $_SESSION['twitch_display_name'] . '</span>';

    // Muestra la insignia de sub si está suscrito
    if ($_SESSION['twitch_suscribed']) {
        echo '<img src="./img/twitch_sub.png" alt="Subscriber Badge" class="badge-image">';
    }

    // Muestra la insignia de VIP si es VIP
    if ($_SESSION['twitch_vip']) {
        echo '<img src="./img/twitch_vip.png" alt="VIP Badge" class="badge-image">';
    }

    echo '</div>';
    echo '<a href="logout.php" class="btn btn-danger ml-auto">Cerrar sesión</a>';
    echo '</nav></header>';
}
?>
