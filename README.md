# mod_unwetterwarnung - Weather Warning Module

Ein Joomla 5.x Modul zur Anzeige von Wetterwarnungen über die OpenWeatherMap API.

## 🌦️ Funktionen

- **Echtzeit-Wetterwarnungen** über OpenWeatherMap API
- **Drei Layout-Optionen**: Standard, Carousel und interaktive Karte
- **Mehrsprachige Unterstützung** (Deutsch/Englisch)
- **Responsive Design** für alle Geräte
- **Accessibility-Features** (WCAG 2.1 AA konform)
- **Caching-System** für optimale Performance
- **Geocoding-Unterstützung** für Standort-Eingabe

## 📋 Voraussetzungen

- **Joomla 5.0+**
- **PHP 8.1+**
- **OpenWeatherMap API Key** (kostenlos erhältlich)
- **Bootstrap 5** (in Joomla 5.x enthalten)

## 🚀 Installation

### 1. Modul-Installation

1. Lade das komplette `mod_unwetterwarnung` Verzeichnis in `/modules/` hoch
2. Kopiere das `media/mod_unwetterwarnung/` Verzeichnis nach `/media/`
3. Gehe zu **System → Extensions → Discover** im Joomla Backend
4. Klicke auf **Discover** und installiere das Modul

### 2. API-Konfiguration

1. Registriere dich bei [OpenWeatherMap](https://openweathermap.org/api)
2. Erstelle einen kostenlosen API-Key
3. Gehe zu **Extensions → Modules** im Joomla Backend
4. Erstelle eine neue Instanz von **Weather Warning Module**
5. Trage deinen API-Key ein

## ⚙️ Konfiguration

### Basis-Einstellungen

| Parameter | Beschreibung | Standard |
|-----------|-------------|----------|
| **API Key** | OpenWeatherMap API-Schlüssel | - |
| **Location** | Standort (Stadt, Land) | Berlin, DE |
| **Layout** | Anzeigemodus (default/carousel/map) | default |
| **Cache Time** | Cache-Dauer in Minuten | 15 |
| **Language** | Sprache für API-Anfragen | de |

### Layout-spezifische Optionen

#### Standard Layout
- **Max Alerts**: Maximale Anzahl angezeigter Warnungen (1-10)
- **Show Timestamp**: Zeitstempel der letzten Aktualisierung anzeigen
- **Compact Mode**: Kompakte Darstellung für Sidebar

#### Carousel Layout
- **Autoplay**: Automatisches Weiterschalten
- **Interval**: Anzeigedauer pro Warnung (3-10 Sekunden)
- **Show Indicators**: Punkt-Navigation anzeigen
- **Show Controls**: Vor/Zurück-Buttons anzeigen

#### Map Layout
- **Map Height**: Kartenhöhe in Pixeln (200-800px)
- **Map Zoom**: Standard-Zoom-Level (5-15)
- **Map Provider**: Kartenanbieter (OpenStreetMap/CartoDB/Satellite)
- **Show Layers**: Layer-Kontrollen anzeigen

## 🎨 Anpassung

### CSS-Anpassungen

Das Modul verwendet CSS-Custom-Properties für einfache Anpassungen:

```css
.mod-unwetterwarnung {
    --severity-extreme: #8B0000;
    --severity-severe: #FF4500;
    --severity-moderate: #FFA500;
    --severity-minor: #FFD700;
    --border-radius: 8px;
    --spacing: 1rem;
}
```

### Template-Overrides

Erstelle Template-Overrides in:
```
/templates/dein-template/html/mod_unwetterwarnung/
├── default.php
├── carousel.php
└── map.php
```

## 📖 Funktions-Dokumentation

### 🏗️ Architektur-Übersicht

Das Modul basiert auf **27 Funktionen** verteilt auf **6 PHP-Dateien**:
- **13 Joomla Standard-Funktionen** (erweitert/überschrieben)
- **14 eigene Funktionen** (komplett selbst entwickelt)

### 🔧 Kern-Funktionen

#### **UnwetterwarnungHelper::getWarnings()**
**Zweck:** Hauptfunktion zum Abrufen und Verarbeiten von Wetter-Warnungen  
**Parameter:** Registry $params, SiteApplication $app  
**Rückgabe:** Array mit formatierten Warnungen  
**Besonderheiten:**
- Implementiert intelligentes Caching (30 Min Standard)
- Validiert API-Schlüssel und Standort-Eingaben
- Unterstützt Fallback-Mechanismen bei API-Fehlern
- Kombiniert Geocoding und Alert-Abfrage in einem Aufruf

#### **OpenWeatherAPIHelper::getWeatherAlerts()**
**Zweck:** Direkter API-Aufruf zu OpenWeatherMap für Wetter-Warnungen  
**Parameter:** Registry $params, SiteApplication $app, float $lat, float $lon, string $lang  
**Rückgabe:** Array mit API-Rohdaten  
**Besonderheiten:**
- Timeout-Behandlung (10 Sekunden)
- Mehrsprachige Unterstützung (DE/EN)
- Automatische Schweregrad-Erkennung
- Ausschluss unnötiger Daten (minutely, hourly, daily)

#### **UnwetterwarnungHelper::getDwdGeoserverConfig()**
**Zweck:** Konfiguration für interaktive DWD-Wetterkarten  
**Parameter:** Registry $params  
**Rückgabe:** Array mit Karten-Konfiguration  
**Besonderheiten:**
- Unterstützt verschiedene Zoom-Level (8-12)
- Konfigurierbare Kartenhöhe (200-800px)
- Integration von Gemeindegrenzen-Layer
- OpenStreetMap als Basis-Layer

### 🌐 API-Integration

#### **OpenWeatherAPIHelper::geocodeLocation()**
**Zweck:** Konvertiert Ortsnamen zu GPS-Koordinaten  
**Parameter:** Registry $params, SiteApplication $app, string $location, int $limit  
**Rückgabe:** Array mit Geocoding-Ergebnissen  
**Besonderheiten:**
- Unterstützt Städte, PLZ und Adressen
- Limit-Parameter für Ergebnis-Anzahl
- Fallback für ungültige Standorte
- Cache-freundliche Implementierung

#### **OpenWeatherAPIHelper::reverseGeocode()**
**Zweck:** Konvertiert GPS-Koordinaten zu Ortsnamen  
**Parameter:** Registry $params, SiteApplication $app, float $lat, float $lon, int $limit  
**Rückgabe:** Array mit Reverse-Geocoding-Ergebnissen  
**Besonderheiten:**
- Präzise Koordinaten-zu-Ort Konvertierung
- Mehrere Ergebnisse für bessere Auswahl
- Internationale Unterstützung
- Error-Handling für ungültige Koordinaten

#### **OpenWeatherAPIHelper::getCurrentWeather()**
**Zweck:** Aktuelle Wetterdaten für Koordinaten abrufen  
**Parameter:** Registry $params, SiteApplication $app, float $lat, float $lon, string $lang  
**Rückgabe:** Array mit aktuellen Wetterdaten  
**Besonderheiten:**
- Temperatur, Luftfeuchtigkeit, Luftdruck
- Metrische Einheiten (Celsius, km/h)
- Wetter-Icons und Beschreibungen
- Sprach-lokalisierte Ausgabe

### 🔄 Datenverarbeitung

#### **UnwetterwarnungHelper::formatAlertData()**
**Zweck:** Transformiert API-Rohdaten in standardisiertes Format  
**Parameter:** array $rawData  
**Rückgabe:** Array mit formatierten Warnungen  
**Besonderheiten:**
- Einheitliche Datenstruktur für Templates
- Schweregrad-Mapping für CSS-Klassen
- Zeitstempel-Formatierung (d.m.Y H:i)
- Fallback-Werte für fehlende Daten

#### **UnwetterwarnungHelper::mapSeverity()**
**Zweck:** Konvertiert API-Schweregrade zu CSS-kompatiblen Klassen  
**Parameter:** string $apiSeverity  
**Rückgabe:** string (danger, warning, info, success)  
**Besonderheiten:**
- Bootstrap-kompatible CSS-Klassen
- Konsistente UI-Darstellung
- Extreme → danger, Severe → warning
- Fallback auf 'info' bei unbekannten Werten

#### **OpenWeatherAPIHelper::processAlerts()**
**Zweck:** Normalisiert und sortiert API-Warnungen  
**Parameter:** array $alerts  
**Rückgabe:** Array mit verarbeiteten Warnungen  
**Besonderheiten:**
- Automatische Sortierung nach Schweregrad
- Vollständige Datenstruktur-Normalisierung
- Intelligente Schweregrad-Erkennung
- Zeitbasierte Dringlichkeits-Berechnung

### 🎯 Hilfsfunktionen

#### **UnwetterwarnungHelper::validateLocation()**
**Zweck:** Validiert und bereinigt Standort-Eingaben  
**Parameter:** string $location  
**Rückgabe:** string (bereinigte Standort-Eingabe)  
**Besonderheiten:**
- Unterstützt Koordinaten-Format (lat,lon)
- String-Filter für Sicherheit
- Exception bei ungültigen Formaten
- Trim und Leerstring-Prüfung

#### **UnwetterwarnungHelper::formatTimestamp()**
**Zweck:** Formatiert Unix-Timestamps für deutsche Anzeige  
**Parameter:** int $timestamp  
**Rückgabe:** string (formatiertes Datum/Zeit)  
**Besonderheiten:**
- Deutsches Format: "d.m.Y H:i"
- Konsistente Darstellung im ganzen Modul
- Unix-Timestamp Konvertierung
- Timezone-bewusste Formatierung

#### **UnwetterwarnungHelper::limitWarnings()**
**Zweck:** Begrenzt Anzahl angezeigter Warnungen  
**Parameter:** array $warnings, int $maxWarnings  
**Rückgabe:** Array mit begrenzten Warnungen  
**Besonderheiten:**
- Verhindert UI-Überladung
- Konfigurierbare Maximal-Anzahl (1-20)
- Array-Slice für Performance
- Beibehaltung der Sortierung

### 🗄️ Cache-Management

#### **UnwetterwarnungHelper::generateCacheKey()**
**Zweck:** Erstellt sichere Cache-Schlüssel  
**Parameter:** string $location, string $apiKey  
**Rückgabe:** string (eindeutiger Cache-Schlüssel)  
**Besonderheiten:**
- MD5-Hash für Sicherheit
- Isolation zwischen verschiedenen Konfigurationen
- Standort- und API-Key-basierte Schlüssel
- Prefix für Cache-Namespace

#### **UnwetterwarnungHelper::getCachedAlerts()**
**Zweck:** Ruft gecachte Warnungsdaten ab  
**Parameter:** string $cacheKey, int $cacheTime, SiteApplication $app  
**Rückgabe:** array|null (gecachte Daten oder null)  
**Besonderheiten:**
- Joomla Cache-System Integration
- Konfigurierbare Cache-Zeit (0-3600s)
- Callback-Controller für Flexibilität
- Exception-Handling für Cache-Fehler

#### **UnwetterwarnungHelper::setCachedAlerts()**
**Zweck:** Speichert Warnungsdaten im Cache  
**Parameter:** string $cacheKey, array $data, int $cacheTime, SiteApplication $app  
**Rückgabe:** void  
**Besonderheiten:**
- Automatische Ablaufzeit-Verwaltung
- Cache-Gruppe für Organisation
- Error-Logging bei Fehlern
- Deaktivierbar durch Cache-Zeit = 0

### 🌍 Koordinaten-Handling

#### **UnwetterwarnungHelper::getCoordinates()**
**Zweck:** Konvertiert Standort-String zu GPS-Koordinaten  
**Parameter:** string $location, Registry $params, SiteApplication $app, OpenWeatherAPIHelper $apiClient  
**Rückgabe:** Array mit 'lat' und 'lon' Schlüsseln  
**Besonderheiten:**
- Erkennt bereits vorhandene Koordinaten (lat,lon)
- Regex-Validation für Koordinaten-Format
- Geocoding-API Integration als Fallback
- Exception bei nicht gefundenen Standorten

### 🔧 API-Hilfsfunktionen

#### **OpenWeatherAPIHelper::buildApiUrl()**
**Zweck:** Erstellt vollständige API-URLs mit Query-Parametern  
**Parameter:** string $endpoint, array $params, string $baseUrl  
**Rückgabe:** string (vollständige API-URL)  
**Besonderheiten:**
- Flexible Basis-URL für verschiedene APIs
- HTTP-Query-Builder Integration
- URL-Encoding für Sicherheit
- Standard-API-Version (2.5)

#### **OpenWeatherAPIHelper::makeRequest()**
**Zweck:** Führt HTTP-Requests mit Error-Handling aus  
**Parameter:** string $url  
**Rückgabe:** array (dekodierte JSON-Antwort)  
**Besonderheiten:**
- 10-Sekunden Timeout
- Custom User-Agent für Identifikation
- HTTP-Status-Code Validierung
- JSON-Parsing mit Error-Handling

#### **OpenWeatherAPIHelper::validateApiKey()**
**Zweck:** Validiert OpenWeatherMap API-Schlüssel Format  
**Parameter:** string $apiKey  
**Rückgabe:** bool (true bei gültigem Format)  
**Besonderheiten:**
- 32-Zeichen alphanumerisch
- Regex-Pattern Validierung
- Statische Methode für einfache Nutzung
- OpenWeatherMap Standard-Format

### 📊 Daten-Analyse

#### **OpenWeatherAPIHelper::determineSeverity()**
**Zweck:** Analysiert Warnungs-Inhalte zur Schweregrad-Bestimmung  
**Parameter:** array $alert  
**Rückgabe:** string (extreme, severe, moderate, minor)  
**Besonderheiten:**
- Keyword-basierte Analyse
- Event-Typ und Beschreibungs-Parsing
- Hierarchische Schweregrad-Zuordnung
- Fallback auf 'minor' bei Unklarheit

#### **OpenWeatherAPIHelper::determineUrgency()**
**Zweck:** Berechnet Dringlichkeit basierend auf Zeitpunkt  
**Parameter:** array $alert  
**Rückgabe:** string (immediate, expected, future)  
**Besonderheiten:**
- Zeitdifferenz-Berechnung zum aktuellen Zeitpunkt
- Immediate: < 1 Stunde
- Expected: < 4 Stunden
- Future: > 4 Stunden

#### **OpenWeatherAPIHelper::determineCertainty()**
**Zweck:** Bestimmt Sicherheits-Level basierend auf Beschreibung  
**Parameter:** array $alert  
**Rückgabe:** string (observed, likely, possible, unlikely)  
**Besonderheiten:**
- Keyword-Analyse in Beschreibungen
- "observed/confirmed" → observed
- "likely" → likely, "possible" → possible
- Fallback auf 'unlikely'

#### **OpenWeatherAPIHelper::getSeverityWeight()**
**Zweck:** Konvertiert Schweregrade zu numerischen Gewichten  
**Parameter:** string $severity  
**Rückgabe:** int (0-4, höher = wichtiger)  
**Besonderheiten:**
- Ermöglicht Sortierung nach Wichtigkeit
- extreme=4, severe=3, moderate=2, minor=1
- Fallback auf 0 für unbekannte Werte
- Verwendet in usort() für Array-Sortierung

### 🎨 Form-Field Integration

#### **LocationField::getInput() [JOOMLA STANDARD]**
**Zweck:** Erstellt HTML-Input für Standort-Auswahl  
**Parameter:** keine (verwendet interne Eigenschaften)  
**Rückgabe:** string (HTML-Markup für Input-Feld)  
**Besonderheiten:**
- Erweitert Joomla's TextField-Klasse
- Autocomplete-Container für Vorschläge
- Hidden-Fields für GPS-Koordinaten
- Data-Attribute für JavaScript-Integration

#### **LocationField::getLabel() [JOOMLA STANDARD]**
**Zweck:** Erstellt HTML-Label mit Hilfstexten  
**Parameter:** keine (verwendet parent::getLabel())  
**Rückgabe:** string (HTML-Markup für Label)  
**Besonderheiten:**
- Erweitert Standard-Label um Hilfstext
- Bootstrap-kompatible Styling-Klassen
- Mehrsprachige Unterstützung
- Kontextuelle Hilfe für Benutzer

#### **LocationField::getCoordinate()**
**Zweck:** Extrahiert gespeicherte Koordinaten-Werte  
**Parameter:** string $type ('lat' oder 'lon')  
**Rückgabe:** string (Koordinaten-Wert)  
**Besonderheiten:**
- Zugriff auf Form-Daten
- Separate Behandlung von Latitude/Longitude
- Fallback auf Leerstring
- Integration mit Joomla's Form-System

#### **LocationField::addLocationFieldScript()**
**Zweck:** Fügt CSS und JavaScript für Autocomplete hinzu  
**Parameter:** keine  
**Rückgabe:** void  
**Besonderheiten:**
- Inline-CSS für Field-Styling
- JavaScript für Autocomplete-Funktionalität
- Document-Integration über getDocument()
- Responsive Design-Berücksichtigung

### 📋 Service Provider & Dispatcher

#### **ServiceProvider::register() [JOOMLA STANDARD]**
**Zweck:** Konfiguriert Dependency Injection für das Modul  
**Parameter:** Container $container  
**Rückgabe:** void  
**Besonderheiten:**
- Registriert ModuleDispatcherFactory
- Registriert HelperFactory
- Registriert Module Service Provider
- Folgt Joomla 5.x+ DI-Patterns

#### **Dispatcher::getLayoutData() [JOOMLA STANDARD]**
**Zweck:** Bereitet Layout-Daten für Template-Rendering vor  
**Parameter:** keine (verwendet parent::getLayoutData())  
**Rückgabe:** array (Layout-Daten für Template)  
**Besonderheiten:**
- Erweitert AbstractModuleDispatcher
- Lädt Wetter-Warnungen über Helper
- Konditionaler DWD-Map-Config Load
- Kombiniert Parent-Daten mit Modul-spezifischen Daten

---

### 🔗 Vollständige GitHub-Dokumentation

Jede Funktion ist mit einem direkten GitHub-Link verknüpft:
- `https://github.com/whykiki/mod_unwetterwarnung#funktionsname`

Dort finden Sie:
- **Detaillierte Code-Beispiele**
- **Parameter-Beschreibungen mit Typen**
- **Rückgabewert-Spezifikationen**
- **Anwendungsbeispiele**
- **Troubleshooting-Tipps**

## 🔧 Entwicklung

### Dateistruktur

```
modules/mod_unwetterwarnung/
├── mod_unwetterwarnung.xml              # Modul-Manifest
├── services/provider.php                # DI Container
├── src/
│   ├── Dispatcher/Dispatcher.php        # Hauptlogik
│   ├── Helper/
│   │   ├── UnwetterwarnungHelper.php    # Datenverarbeitung
│   │   └── OpenWeatherAPIHelper.php         # API-Client
│   └── Field/LocationField.php          # Custom Form Field
├── tmpl/
│   ├── default.php                      # Standard-Layout
│   ├── carousel.php                     # Carousel-Layout
│   └── map.php                          # Karten-Layout
├── language/                            # Sprachdateien
├── tests/playwright/                    # E2E-Tests
├── docs/                               # Dokumentation
├── tasks/                              # Aufgaben-Tracking
└── README.md                           # Diese Datei

media/mod_unwetterwarnung/
├── joomla.asset.json                   # Asset-Management
├── css/mod_unwetterwarnung.css         # Styling
├── js/
│   ├── mod_unwetterwarnung.js          # Hauptfunktionalität
│   └── map-handler.js                  # Karten-Handler
└── images/weather-icons/               # Wetter-Icons
```

### API-Integration

Das Modul nutzt folgende OpenWeatherMap APIs:

- **One Call API 3.0**: Für Wetterwarnungen
- **Geocoding API**: Für Standort-Auflösung
- **Current Weather API**: Für aktuelle Wetterdaten

### Testing

```bash
# Playwright-Tests ausführen
npx playwright test modules/mod_unwetterwarnung/tests/playwright/

# Spezifische Tests
npx playwright test basic.spec.js --project=chromium
```

## 🌍 Mehrsprachigkeit

### Unterstützte Sprachen

- **Deutsch (de-DE)**: Vollständig
- **Englisch (en-GB)**: Vollständig

### Neue Sprachen hinzufügen

1. Kopiere die Sprachdateien:
   ```
   language/de-DE/mod_unwetterwarnung.ini
   language/de-DE/mod_unwetterwarnung.sys.ini
   ```

2. Benenne sie um (z.B. für Französisch):
   ```
   language/fr-FR/mod_unwetterwarnung.ini
   language/fr-FR/mod_unwetterwarnung.sys.ini
   ```

3. Übersetze die Texte in den INI-Dateien

## 🔍 Fehlerbehebung

### Häufige Probleme

#### Keine Warnungen angezeigt
- ✅ API-Key korrekt eingegeben?
- ✅ Standort richtig formatiert? (z.B. "Berlin, DE")
- ✅ Cache geleert? (System → Clear Cache)
- ✅ Joomla-Logs prüfen

#### Karte lädt nicht
- ✅ Leaflet.js verfügbar?
- ✅ JavaScript-Fehler in Browser-Konsole?
- ✅ map-handler.js korrekt geladen?

#### Styling-Probleme
- ✅ CSS-Datei geladen?
- ✅ Template-Konflikte?
- ✅ Bootstrap 5 verfügbar?

### Debug-Modus

Aktiviere Debug-Informationen in der Modulkonfiguration:

```php
// In der Modulkonfiguration
$params->set('debug', true);
```

## 📊 Performance

### Caching-Strategie

- **API-Calls**: 15 Minuten Standard-Cache
- **Geocoding**: 24 Stunden Cache
- **Static Assets**: Browser-Cache via .htaccess

### Optimierungen

- Lazy Loading für Karten-Assets
- Minifizierte CSS/JS-Dateien
- Optimierte API-Anfragen
- Responsive Images

## 🔒 Sicherheit

### Best Practices

- API-Keys werden verschlüsselt gespeichert
- Input-Validierung für alle Parameter
- XSS-Schutz durch HTML-Escaping
- CSRF-Token für Admin-Formulare

## 📄 Lizenz

**GNU General Public License v2.0**

Dieses Modul ist freie Software unter der GPL v2.0 Lizenz.

## 👥 Support

### Community-Support

- **GitHub Issues**: [Repository Issues](https://github.com/whykiki/mod_unwetterwarnung/issues)
- **Joomla Forum**: Modul-Entwicklung Bereich
- **Discord**: Joomla Deutschland Server

### Kommerzielle Unterstützung

Für professionelle Unterstützung, Anpassungen oder Schulungen kontaktiere:

**Whykiki Development**
- Website: [whykiki.de](https://whykiki.de)
- E-Mail: support@whykiki.de

## 🚀 Roadmap

### Version 1.1 (Q2 2024)
- [ ] Multi-Location Support
- [ ] Push-Benachrichtigungen
- [ ] Advanced Filtering
- [ ] Custom Alert Types

### Version 1.2 (Q3 2024)
- [ ] Weather Radar Integration
- [ ] Historical Data View
- [ ] Mobile App Integration
- [ ] Advanced Analytics

## 🤝 Beitragen

Beiträge sind willkommen! Bitte beachte:

1. **Fork** das Repository
2. **Erstelle** einen Feature-Branch
3. **Committe** deine Änderungen
4. **Teste** gründlich
5. **Erstelle** einen Pull Request

### Entwicklungsrichtlinien

- PSR-12 Coding Standards
- Joomla 5.x Best Practices
- 100% Test-Coverage für neue Features
- Dokumentation für alle öffentlichen APIs

---

**Made with ❤️ for the Joomla Community** 
