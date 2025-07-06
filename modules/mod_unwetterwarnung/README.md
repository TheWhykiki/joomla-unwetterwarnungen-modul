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
│   │   └── WeatherApiClient.php         # API-Client
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