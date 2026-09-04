# Planungsphase FitFuerInfo

## Ablauf

### Vorbereitung
- Aufgabenstellung analysieren
- Aufgaben verteilen
- Benötigte Zusatzteile festlegen: PHP, MySQL/phpMyAdmin und XAMPP
- Anforderungen an Benutzerrechte und Sicherheit zusammentragen
- Offene Fragen für den Auftraggeber sammeln

### Datenbank und Oberfläche planen
- Aufbau der SQL-Datenbank planen
- Benötigte Tabellen und Beziehungen zwischen den Tabellen festlegen
- Benutzerrechte und Rollen planen
- Aufbau und Navigation der Website planen
- PHP-Grundlagen und die Verbindung zwischen PHP und MySQL recherchieren
- Sichere Passwortspeicherung und Sessions in PHP recherchieren und ausprobieren

## Webseite

### Aufbau und Aussehen
- Responsive Design
- Übersichtliche Navigation
- Login-Seite
- Startseite/Übersicht
- Kursverwaltung
- Raumverwaltung
- Anzeige der aktuellen Raumbelegung
- Seite zum Buchen eines Raumes
- Verwaltungsbereich für den Systemverwalter

### Funktionen
- Mitarbeiter können sich anmelden
- Mitarbeiter können eigene Kursprofile erstellen, bearbeiten und löschen
- Mitarbeiter können die Kursprofile anderer Mitarbeiter ansehen
- Der Systemverwalter kann Mitarbeitern zusätzliche Rechte für Kurse und Räume geben
- Mitarbeiter können Räume und deren Ausstattung einsehen
- Räume können für bestimmte Kurse gebucht werden
- Bei der Buchung wird geprüft, ob der Raum zum Kurs passt
- Mitarbeiter können ihre eigenen Buchungen löschen
- Der Systemverwalter kann Buchungen löschen und Räume verwalten

## Umsetzung
- Datenbank mit MySQL/phpMyAdmin erstellen
- Datenbankverbindung mit PHP herstellen
- Login und Benutzerverwaltung einstellen
- Benutzerrechte umsetzen
- Kursverwaltung programmieren
- Raumverwaltung programmieren
- Raumbuchung mit Prüfung der Kursanforderungen programmieren
- Oberfläche gestalten und Funktionen miteinander verbinden

## Testen
- Funktionen in einer Testumgebung testen und nicht auf einem Produktivsystem
- Login und Benutzerrechte testen
- Prüfen, ob Mitarbeiter nur erlaubte Aktionen durchführen können
- Passwortspeicherung und Sicherheitsmaßnahmen überprüfen
- Kursverwaltung testen
- Raumverwaltung und Raumbuchung testen
- Prüfen, ob ungeeignete Räume für einen Kurs abgelehnt werden
- Gesamtes System testen
- Webseite auf dem Schulrechner mit XAMPP 5.6.36 testen
- Auftretende Fehler dokumentieren und beheben

## Fragen an den Auftraggeber
- Wie soll die Authentifizierung der Benutzer stattfinden?
- Welche Informationen müssen bei einem Mitarbeiter-Account gespeichert werden?
- Welche Räume und Softwarepakete gibt es?
- Welche Informationen muss ein Kursprofil zusätzlich zu Teilnehmerzahl und benötigter Software enthalten?
- Wie sollen Buchungszeiten festgelegt werden?
- Darf ein Raum gleichzeitig für mehrere Kurse gebucht werden?
- Gibt es weitere Benutzerrollen außer Mitarbeiter und Systemverwalter?
- Soll die Website nur lokal im Firmennetzwerk erreichbar sein oder auch von außerhalb?
