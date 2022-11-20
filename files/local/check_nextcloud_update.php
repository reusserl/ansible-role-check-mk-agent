#! /usr/bin/env php
<?php
$baseDir = "/var/www/nextcloud";
function getNewVersion($baseDir)
{
    require $baseDir . "/config/config.php";
    $pdo = new PDO(sprintf("mysql:host=%s;dbname=%s", $CONFIG["dbhost"], $CONFIG["dbname"]), $CONFIG["dbuser"], $CONFIG["dbpassword"]);
    $query = $pdo->query("
        SELECT `configvalue`
        FROM `oc_appconfig`
        WHERE `appid` = 'core' AND `configkey` = 'lastupdateResult'
    ");
    if (!$query->rowCount()) {
        return null;
    }
    $json = json_decode($query->fetchObject()->configvalue, true);
    if ($json === null) {
        return null;
    }
    if (!is_array($json)) {
        return null;
    }
    if (empty($json)) {
        return true;
    }
    if (!isset($json["version"])) {
        return null;
    }
    if (!is_string($json["version"])) {
        return null;
    }
    return $json["version"];
}
function getCurrentVersion($baseDir)
{
    require $baseDir . "/version.php";
    return implode(".", $OC_Version);
}
function output($state, $message)
{
    printf("%d Nextcloud_Version - %s\n", $state, $message);
}
$newVersion = getNewVersion($baseDir);
$currentVersion = getCurrentVersion($baseDir);
if ($newVersion === null) {
    output(1, "Unable to read new version");
} elseif (!is_string($currentVersion)) {
    output(1, "Unable to read current version");
} elseif ($newVersion === true or $newVersion === $currentVersion) {
    output(0, sprintf("No update available (installed version: %s)", $currentVersion));
} else {
    output(1, sprintf("An update to version %s is available (installed version: %s)", $newVersion, $currentVersion));
}