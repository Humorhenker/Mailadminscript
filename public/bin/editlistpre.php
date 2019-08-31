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
if ($_SESSION['log'] == 1 && $_SESSION['admin']) {
    echo '<html>
    <head>
    <title>Mailliste editieren</title>
    </head>
    <body>
    <a href="maillistsettings.php"><h3>Zurück zur Maillistoberfläche (Editieren abbrechen)</h3></a><br>';
    $abfrage = "SELECT `source`, `destination`, `owner`, `private`, `name` FROM `virtual_aliases` WHERE `id` LIKE :editlistid";
    $result = $dbh->prepare($abfrage);
    $result->execute(array('editlistid' => $_GET['editlistid']));
    while ($lists = $result->fetch()) {
        echo'
        <form name="editlist" method=POST action="editlist.php">
        <label>Listenname:<input name="newlistname" type="text" placeholder="Listenname" value="' . $lists['name'] . '"/></label>
        <label>Listenadresse:<input name="newlistsource" type="text" placeholder="Listenadresse" value="' . $lists['source'] . '"/></label>
        <label>Listenbesitzer:<select name="newlistownerid">';
        $abfrage = "SELECT `id`, `email` FROM `virtual_users`";
        $result = $dbh->query($abfrage);
        while ($emails = $result->fetch()) {
            echo '<option value="' . $emails['id'] . '" ';
            if ($emails['id'] == $lists['owner']) echo ' selected';
            echo '>' . $emails['email'] . '</option>';
        }
        echo '</select></label><br>
        <label>Listenempfänger (durch Leerzeichen getrennt):<br><textarea rows="4" cols="50" name="newlistdestination">' . $lists['destination'] . '</textarea></label>
        <label>Listensicherheitseinstellungen:<select name="newlistprivate">
        <option value="0"';
        if ($lists['private'] == 0) echo ' selected';
        echo '>0 (Jeder kann Mails an die Liste schicken)</option>
        <option value="1"';
        if ($lists['private'] == 1) echo ' selected';
        echo '>1 (Mitglieder der Liste können Mails an die Liste schicken)</option>
        <option value="2"';
        if ($lists['private'] == 2) echo ' selected';
        echo '>2 (Der Besitzer der Liste kann Mails an die Liste schicken)</option>
        </select></label><br>
        <input type="hidden" name="editlistid" value="' . $_GET['editlistid'] . '"/>
        <input type="submit" name="submit" value="Editieren"/>
        </body>
        </html>';
    }
}
?>