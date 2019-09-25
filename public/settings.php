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
$config = parse_ini_file('../private/config.ini');
try {
    $dbh = new PDO('mysql:host=' . $config['dbservername'] . ';dbname=' . $config['dbname'], $config['dbusername'], $config['dbpassword'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
} catch (PDOException $e) {
    //echo 'Connection failled: '. $e->getMessage(); // Errormessage kann Sicherheitsrelevantes enthalen
    echo 'Connection failed';
}
session_start();
if ($_SESSION['log'] == 1) {
    echo '<html>
    <head>
    <title>Mail Settings</title>
    </head>
    <body>
    <h1>Mail Settings:</h1><p>Guten Tag, ' . $_SESSION['username'] . '@' . $_SESSION['domain'] . '</p>';
    $randval = rand(0, 99);
    echo '<!-- '. $randval . ' -->';
    if (rand(0,99) == 42) {
        echo '<img src="img/mailcat.gif"/><br>';
    }
    if (isset($_GET['success'])) {
        echo '<p>Erfolgreich geändert.</p>';
    }
    if (isset($_GET['pwnotequal'])) {
        echo '<h3>Passwörter nicht gleich!</h3>';
    }
    if ($_SESSION['admin'] == 1) {
        echo '<a href="admin.php"><p>Admin-Settings (inklusive Maillisten)</p></a>';
    }
    else {
        $abfrage = "SELECT `alias_id` FROM `alias_owner` WHERE `owner_username` LIKE :owner_username AND `owner_domain` LIKE :owner_domain";
        $result = $dbh->prepare($abfrage);
        $result->execute(array(':owner_username' => $_SESSION['username'], ':owner_domain' => $_SESSION['domain']));
        if ($result->rowCount() > 0) {
            echo '<a href="bin/maillistsettings.php"><p>Meine Maillisten verwalten</p></a>';
        }
    }
    echo '<a href="logout.php"><button>Logout</button></a>';
    echo '<h3>Passwort ändern:</h3>
    <form name="changemailpw" method=POST action="bin/changemailpw.php">
    <label>Altes Passwort: <input type="password" name="oldmailpw"/></label>
    <label>Neues Passwort: <input type="password" name="newmailpw"/>(min. 8 Zeichen, benutze nicht ' .  "'" . ')</label>
    <label>Neue Passwort wiederholen: <input type="password" name="newmailpwrep"/></label>';
    if ($config['maildirencryption']) {
        echo '<label><p style="font-size: x-small">Schlüssel-Neuerstellung erzwingen</p><p style="font-size: small">ACHTUNG! Alle alten Mails werden dann wahrscheinlich nicht mehr lesbar sein!<input type="checkbox" name="forcekeyregen"/></p></label>';
    }
    echo '<input type="submit" value="Abschicken"/>
    <h3>Diese Mailadresse löschen:</h3>
    <form name="deletemail" method=POST action="bin/deletemail.php">
    <input type="submit" value="LÖSCHEN"/>
    </form>';

    echo '</body>
    </html>';
    exit;
}
header("Location: index.php");
?>