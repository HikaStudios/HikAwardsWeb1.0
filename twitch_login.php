<?php

session_start();
include ("database.php");
// Constante con el ID del canal de Hikarilof (reemplázala con el ID correcto)
$ID_CANAL_HIKARILOF = '697850700';
$client_id = '';
$client_secret = '';
$redirect_uri = 'http://localhost/Hikawards/twitch_login.php';
$token_url = 'https://id.twitch.tv/oauth2/token';

// Verifica si tiene el código de autorización
if (isset($_GET['code'])) {
    $_SESSION['twitch_code'] = $_GET['code'];

    // Parámetros para solicitar el token de acceso
    $params = array(
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $_SESSION['twitch_code'],
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirect_uri,
    );

    // Sesion de cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    // Respuesta JSON
    $data = json_decode($response, true);

    // Verifica si se obtuvo el token de acceso
    if (isset($data['access_token'])) {
        $access_token = $data['access_token'];

        // Endpoint info del usuario
        $user_info_url = 'https://api.twitch.tv/helix/users';

        // Parámetros para la solicitud de información del usuario

        // Inicia una sesión cURL para obtener información del usuario
        $ch_user_info = curl_init();

        curl_setopt($ch_user_info, CURLOPT_URL, $user_info_url);
        curl_setopt($ch_user_info, CURLOPT_HTTPHEADER, array(
            'Client-ID: ' . $client_id,
            'Authorization: Bearer ' . $access_token,
        ));
        curl_setopt($ch_user_info, CURLOPT_RETURNTRANSFER, true);

        $response_user_info = curl_exec($ch_user_info);
        curl_close($ch_user_info);

        // Decodifica la respuesta JSON
        $user_info_data = json_decode($response_user_info, true);

        // Verifica si se obtuvo la información del usuario correctamente
        if (isset($user_info_data['data'][0]['login'])) {
            // Almacena el nombre de usuario de Twitch en la sesión
            $_SESSION['twitch_id'] = $user_info_data['data'][0]['id'];
            // Almacena el display_name en la sesión
            $_SESSION['twitch_display_name'] = $user_info_data['data'][0]['display_name'];

            if ($_SESSION['twitch_display_name']=='hikarilof') {
                setHikarilofTokenToDB($conn,$access_token);
            }

            if (!getHikarilofTokenFromDB($conn)) {
                echo '<script>alert("La aplicación aún no se ha iniciado");</script>';
               echo '<script>window.location.href = "logout.php";</script>';
                exit();
            }


            // Almacena la URL de la imagen de perfil en la sesión
            $_SESSION['twitch_profile_image_url'] = $user_info_data['data'][0]['profile_image_url'];

            // Almacena la fecha de creación en la sesión
            $_SESSION['twitch_created_at'] = $user_info_data['data'][0]['created_at'];

            // Almacena la información de suscripción en la sesión
            $hikarilof_token = getHikarilofTokenFromDB($conn);
            $_SESSION['twitch_suscribed'] = isSuscribed($client_id, $access_token,$ID_CANAL_HIKARILOF);

            $_SESSION['twitch_follow_date'] = getFollowDate($client_id, $access_token,$ID_CANAL_HIKARILOF);
            //2021-06-14T22:35:25Z

            $_SESSION['twitch_vip'] = isVIP($client_id, $hikarilof_token,$ID_CANAL_HIKARILOF);


            // Redirige al usuario a la página principal o realiza otras acciones necesarias
            header('Location: form.php');
            exit();
        } else {
            // Maneja el error si no se puede obtener la información del usuario
            echo 'Error al obtener la información del usuario.';
        }
    } else {
        // Maneja el error si no se puede obtener el token de acceso
        echo 'Error al obtener el token de acceso.';
    }
} else {


    // Verificar si ya hay un access token para Hikarilof en $_SESSION
    if (getHikarilofTokenFromDB($conn)) {
        // Verificar si el access token de Hikarilof necesita ser actualizado
        $hikarilof_token = refreshAccessToken($_SESSION['hikarilof_token'], $client_id, $client_secret);

        if ($hikarilof_token !== false) {
            // Actualizar el access token en $_SESSION
            setHikarilofTokenToDB($conn,$hikarilof_token);
        } else {
            // Maneja el error si no se puede actualizar el token de Hikarilof
            echo 'Error al actualizar el token de Hikarilof.';
        }
    } 

        // URL de autorización de Twitch
        $authorize_url = 'https://id.twitch.tv/oauth2/authorize';

        // Parámetros de la solicitud de autorización
        $authorize_params = array(
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'user:read:follows user:read:subscriptions channel:read:vips',
        );

        // Redirige al usuario a la página de autorización de Twitch
        header('Location: ' . $authorize_url . '?' . http_build_query($authorize_params));
        exit();

}


function isVIP($client_id, $access_token, $channel_id) {
    // Endpoint para verificar si un usuario tiene estado VIP en un canal
    $vip_url = 'https://api.twitch.tv/helix/channels/vips';

    $vip_params = array(
        'broadcaster_id' => $channel_id,
        'user_id' => $_SESSION['twitch_id'],
    );

    // Inicia una sesión cURL para verificar el estado VIP
    $ch_vip = curl_init();
    curl_setopt($ch_vip, CURLOPT_URL, $vip_url . '?' . http_build_query($vip_params));
    curl_setopt($ch_vip, CURLOPT_HTTPHEADER, array(
        'Client-ID: ' . $client_id,
        'Authorization: Bearer ' . $access_token,
    ));
    curl_setopt($ch_vip, CURLOPT_RETURNTRANSFER, true);

    $response_vip = curl_exec($ch_vip);

    // Verificar si hay errores en la solicitud cURL
    if (curl_errno($ch_vip)) {
        echo 'Error en la solicitud cURL para verificar el estado VIP: ' . curl_error($ch_vip);
        curl_close($ch_vip);
        exit();
    }

    // Verificar si la respuesta es exitosa (código 200)
    $http_code = curl_getinfo($ch_vip, CURLINFO_HTTP_CODE);

    if ($http_code != 200) {
        $error_message = curl_error($ch_vip);
        echo 'Error en la respuesta de estado VIP HTTP: ' . $http_code . ' - ' . $error_message;
        curl_close($ch_vip);
        exit();
    }  

    // Cerrar la sesión cURL
    curl_close($ch_vip);

    // Decodificar la respuesta JSON
    $vip_data = json_decode($response_vip, true);
    // Verificar si el usuario tiene estado VIP
    return !empty($vip_data['data']);
}





function getFollowDate($client_id, $access_token, $channel_id) {
    // Endpoint para verificar si un usuario sigue a un canal
    $follow_url = 'https://api.twitch.tv/helix/channels/followed';

    // Parámetros para la solicitud de seguimiento
    $follow_params = array(
        'user_id' =>  $_SESSION['twitch_id'], // ID del usuario que sigue (tu usuario)
        'broadcaster_id' => $channel_id, // ID del canal que se está siguiendo
    );

    // Inicia una sesión cURL para verificar si el usuario sigue al canal
    $ch_follow = curl_init();
    curl_setopt($ch_follow, CURLOPT_URL, $follow_url . '?' . http_build_query($follow_params));
    curl_setopt($ch_follow, CURLOPT_HTTPHEADER, array(
        'Client-ID: ' . $client_id,
        'Authorization: Bearer ' . $access_token,
    ));
    curl_setopt($ch_follow, CURLOPT_RETURNTRANSFER, true);

    $response_follow = curl_exec($ch_follow);

    // Verificar si hay errores en la solicitud cURL
    if (curl_errno($ch_follow)) {
        echo 'Error en la solicitud cURL para verificar el seguimiento: ' . curl_error($ch_follow);
        curl_close($ch_follow);
        return false;
    }

    // Verificar si la respuesta es exitosa (código 200)
    $http_code = curl_getinfo($ch_follow, CURLINFO_HTTP_CODE);

    if ($http_code != 200) {
        $error_message = curl_error($ch_follow);
        echo 'Error en la respuesta de Follow HTTP: ' . $http_code . ' - ' . $error_message;
        curl_close($ch_follow);
        return false;
    }

    // Cerrar la sesión cURL
    curl_close($ch_follow);

    // Decodificar la respuesta JSON
    $follow_data = json_decode($response_follow, true);

    // Verificar si el usuario sigue al canal
    if (!empty($follow_data['data'])) {
        //var_dump($follow_data);
        // Obtener la fecha de seguimiento

        return $follow_data['data'][0]['followed_at'];

    } else {
        // El usuario no sigue al canal
        return false;
    }
}

function isSuscribed($client_id, $access_token,$ID_CANAL_HIKARILOF) {
    $subscription_url = 'https://api.twitch.tv/helix/subscriptions/user';

    $subscription_params = array(
        'broadcaster_id' => $ID_CANAL_HIKARILOF, // Utiliza el ID correcto del canal de Hikarilof
        'user_id' => $_SESSION['twitch_id'],
    );

    // Inicia una sesión cURL para obtener información de suscripción
    $ch_subscription = curl_init();
    curl_setopt($ch_subscription, CURLOPT_URL, $subscription_url . '?' . http_build_query($subscription_params));
    curl_setopt($ch_subscription, CURLOPT_HTTPHEADER, array(
        'Client-ID: ' . $client_id,
        'Authorization: Bearer ' . $access_token,
    ));
    curl_setopt($ch_subscription, CURLOPT_RETURNTRANSFER, true);

    $response_subscription = curl_exec($ch_subscription);

    // Verificar si hay errores en la solicitud cURL
    if (curl_errno($ch_subscription)) {
        echo 'Error en la solicitud cURL: ' . curl_error($ch_subscription);
        exit();
    }

    // Verificar si la respuesta es exitosa (código 200)
    $http_code = curl_getinfo($ch_subscription, CURLINFO_HTTP_CODE);

    if ($http_code != 200) {
        if ($http_code ==404) return false;
        $error_message = curl_error($ch_subscription);
        echo 'Error en la respuesta de sub HTTP: ' . $http_code . ' - ' . $error_message;
        exit();
    }
    // Cerrar la sesión cURL
    curl_close($ch_subscription);

    // Decodificar la respuesta JSON
    $subscription_data = json_decode($response_subscription, true);
    // Verificar si hay información de suscripción
    return !empty($subscription_data['data']);
}

function refreshAccessToken($current_token, $client_id, $client_secret) {
    // Endpoint para refrescar el token de acceso
    $refresh_url = 'https://id.twitch.tv/oauth2/token';

    // Parámetros para la solicitud de actualización de token
    $refresh_params = array(
        'grant_type' => 'refresh_token',
        'refresh_token' => $current_token,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
    );

    // Inicia una sesión cURL para actualizar el token de acceso
    $ch_refresh = curl_init();
    curl_setopt($ch_refresh, CURLOPT_URL, $refresh_url);
    curl_setopt($ch_refresh, CURLOPT_POST, 1);
    curl_setopt($ch_refresh, CURLOPT_POSTFIELDS, http_build_query($refresh_params));
    curl_setopt($ch_refresh, CURLOPT_RETURNTRANSFER, true);

    $response_refresh = curl_exec($ch_refresh);

    // Verificar si hay errores en la solicitud cURL
    if (curl_errno($ch_refresh)) {
        echo 'Error en la solicitud cURL para refrescar el token: ' . curl_error($ch_refresh);
        curl_close($ch_refresh);
        return false;
    }

    // Verificar si la respuesta es exitosa (código 200)
    $http_code = curl_getinfo($ch_refresh, CURLINFO_HTTP_CODE);

    if ($http_code != 200) {
        $error_message = curl_error($ch_refresh);
        echo 'Error en la respuesta de refresco HTTP: ' . $http_code . ' - ' . $error_message;
        curl_close($ch_refresh);
        return false;
    }

    // Cerrar la sesión cURL
    curl_close($ch_refresh);

    // Decodificar la respuesta JSON
    $refresh_data = json_decode($response_refresh, true);

    // Verificar si se obtuvo el nuevo token de acceso
    if (isset($refresh_data['access_token'])) {
        return $refresh_data['access_token'];
    } else {
        echo 'Error al obtener el nuevo token de acceso.';
        return false;
    }
}

?>
