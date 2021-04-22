# IP-Symcon SYR SafeTech Connect Modul
Modul um ein SYR SafeTech Connect Gerät via REST API lokal auszulesen.


### Inhaltverzeichnis

1. [Funktionsumfang](#1-funktionsumfang)
2. [Voraussetzungen](#2-voraussetzungen)
3. [Software-Installation](#3-software-installation)
4. [Einrichten der Instanzen in IP-Symcon](#4-einrichten-der-instanzen-in-ip-symcon)
5. [Statusvariablen und Profile](#5-statusvariablen-und-profile)
6. [Webfront](#6-webfront)
7. [PHP Befehlsreferenz](#7-php-befehlsreferenz)

### 1. Funktionsumfang

Alle Daten welche as SafeTech connect Geräte via REST API zur Verfügung stellt können ausgelesen werden.

### 2. Voraussetzungen

- IP-Symcon ab Version 5.x

### 3. Software-Installation

Über das Modul-Control folgende URL hinzufügen.  
`https://github.com/PreinfalkG/IPS_PG-SYR.git`  


### 4. Einrichten der Instanzen in IP-Symcon

- Unter "Instanz hinzufügen" ist das 'SafeTech Connect' - Modul unter dem Hersteller 'SYR' aufgeführt.

#### 4.1 Instanz konfigurieren
__Konfigurationsseite__:

Name       | Beschreibung
---------- | ---------------------------------
aa         | 11
bb         | 22



### 5. Statusvariablen und Profile

Die Variablen und Profile werden automatisch angelegt. Das Löschen einzelner kann zu Fehlfunktionen führen.

##### Statusvariablen

Name               | Typ       	| Beschreibung
------------------ | --------- 	| ----------------
aa         		   | 11			| 1A
bb         		   | 22			| 2B

##### Profile

Nachfolgende Profile werden zusätzlich hinzugefügt und bei jedem "Änderungen übernehmen" der Instanz neu abgespeichert:

* SYR.DisabledEnabled
* SYR.xxx
* SYR.yyy

### 6. WebFront

Die Instanz wird im WebFront angezeigt.

### 7. PHP-Befehlsreferenz

Präfix des Moduls 'STC'

