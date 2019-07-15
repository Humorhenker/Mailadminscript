<?php
/*  Mailadminscript
    Copyright (C) 2019  Paul Schürholz contact AT roteserver . de

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>. */
$config = parse_ini_file('../../private/config.ini');
try {
    $dbh = new PDO('mysql:host=' . $config['dbservername'] . ';dbname=' . $config['dbname'], $config['dbusername'], $config['dbpassword'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (PDOException $e) {
    //echo 'Connection failled: '. $e->getMessage(); // Errormessage kann Sicherheitsrelevantes enthalen
    echo 'Connection failed';
}
session_start();
if (!isset($_SESSION['log']) or $_SESSION['log'] != 1) {
    echo' <h3>Emailadresse hinzufügen:</h3>
    ';
    if (isset($_GET['wrongcaptchacode']) AND $config['captcha']) {
        echo '<h3>Captcha falsch</h3>';
    }
    if (isset($_GET['pwtooshort'])) {
        echo '<h3>Passwort zu kurz. Bitte mindestens 8 Zeichen</h3>';
    }
    if (isset($_GET['mailalreadytaken'])) {
        echo '<h3>Diese Mailadresse besteht leider schon</h3>';
    }
    if (isset($_GET['pwnotequal'])) {
        echo '<h3>Passwörter nicht gleich!</h3>';
    }
    if (isset($_GET[ 'wrongsymbols'])) {
        echo '<h3>Verbotene Symbole in Passwort oder Adresse enthalten!</h3>';
    }
    echo '<form name="createmailuser" method=POST action="createmailuser.php">
    <label>Neue email<input type="text" name="newmailusername"/>@roteserver.de (benutze nicht ' .  "'" . ')</label>
    <label>Neue Passwort<input type="password" name="newmailpw"/>(min. 8 Zeichen, benutze nicht ' .  "'" . ')</label>
    <label>Neue Passwort wiederholen<input type="password" name="newmailpwrep"/></label>';
    if ($config['captcha']) {
    echo '<label><p>Captcha:</p><p>gebe hier bitte den Zahlencode aus dem Bild ein</p><img src="captcha.php"/>
    <input type="text" name="captchacode"/></label>';
    }
    echo '<input type="submit" name="submit" value="Hinzufügen"/>
    </form>
    <p>Dein Konto muss erst freigeschaltet werden, bevor du es benutzen kannst.</p>';
    exit;
}
else {
    header("Location: ../settings.php");
}
?>