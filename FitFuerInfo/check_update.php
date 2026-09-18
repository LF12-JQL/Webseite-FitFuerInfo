<?php
// check_update.php — AJAX-Endpunkt für Update-Prüfung
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/version.php';

header('Content-Type: application/json');

// Nur eingeloggte Systemverwalter dürfen prüfen
if (!isLoggedIn() || !isSystemverwalter()) {
    echo json_encode(array('error' => 'Zugriff verweigert.'));
    exit;
}

$url = 'https://api.github.com/repos/' . GITHUB_REPO . '/releases/latest';

$context = stream_context_create(array(
    'http' => array(
        'method' => 'GET',
        'header' => "User-Agent: FitFuerInfo-UpdateChecker\r\n",
        'timeout' => 10
    )
));

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    // Fallback: Tags prüfen falls keine Releases existieren
    $url_tags = 'https://api.github.com/repos/' . GITHUB_REPO . '/tags';
    $response_tags = @file_get_contents($url_tags, false, $context);

    if ($response_tags === false) {
        echo json_encode(array('error' => 'Server konnte nicht erreicht werden.'));
        exit;
    }

    $tags = json_decode($response_tags, true);
    if (empty($tags)) {
        echo json_encode(array(
            'current' => APP_VERSION,
            'latest' => APP_VERSION,
            'update_available' => false,
            'message' => 'Keine Versionen gefunden. Sie verwenden Version ' . APP_VERSION . '.'
        ));
        exit;
    }

    // Neuesten Tag nehmen (Version mit/ohne "v"-Prefix)
    $latest_tag = $tags[0]['name'];
    $latest_version = ltrim($latest_tag, 'vV');
    $download_url = 'https://github.com/' . GITHUB_REPO . '/releases';

    if (version_compare($latest_version, APP_VERSION, '>')) {
        echo json_encode(array(
            'current' => APP_VERSION,
            'latest' => $latest_version,
            'update_available' => true,
            'download_url' => $download_url,
            'message' => 'Neue Version ' . $latest_version . ' verfügbar! (Aktuell: ' . APP_VERSION . ')'
        ));
    } else {
        echo json_encode(array(
            'current' => APP_VERSION,
            'latest' => $latest_version,
            'update_available' => false,
            'message' => 'Sie verwenden bereits die neueste Version (' . APP_VERSION . ').'
        ));
    }
    exit;
}

$data = json_decode($response, true);
$latest_tag = isset($data['tag_name']) ? $data['tag_name'] : '';
$latest_version = ltrim($latest_tag, 'vV');
$download_url = isset($data['html_url']) ? $data['html_url'] : 'https://github.com/' . GITHUB_REPO . '/releases';

if (version_compare($latest_version, APP_VERSION, '>')) {
    echo json_encode(array(
        'current' => APP_VERSION,
        'latest' => $latest_version,
        'update_available' => true,
        'download_url' => $download_url,
        'message' => 'Neue Version ' . $latest_version . ' verfügbar! (Aktuell: ' . APP_VERSION . ')'
    ));
} else {
    echo json_encode(array(
        'current' => APP_VERSION,
        'latest' => $latest_version ? $latest_version : APP_VERSION,
        'update_available' => false,
        'message' => 'Sie verwenden bereits die neueste Version (' . APP_VERSION . ').'
    ));
}
?>
