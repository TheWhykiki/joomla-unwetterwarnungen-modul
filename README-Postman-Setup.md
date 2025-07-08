# Postman Setup für Joomla Unwetterwarnung Modul

## 📋 Übersicht

Diese Anleitung erklärt, wie du die Postman Collection und Environments für das `mod_unwetterwarnung` Joomla Modul verwendest.

## 📁 Dateien

- `postman-collection-unwetterwarnung.json` - Hauptsammlung aller API-Endpunkte
- `postman-environment-development.json` - Development Umgebung
- `postman-environment-testing.json` - Testing Umgebung  
- `postman-environment-production.json` - Production Umgebung
- `postman-environment-local.json` - Lokale Entwicklung

## 🚀 Setup

### 1. Import in Postman

1. **Collection importieren:**
   - Öffne Postman
   - Klicke auf "Import"
   - Wähle `postman-collection-unwetterwarnung.json`

2. **Environments importieren:**
   - Klicke auf "Import" 
   - Wähle alle `postman-environment-*.json` Dateien
   - Oder importiere einzeln je nach Bedarf

### 2. API Key konfigurieren

**WICHTIG:** Du musst deinen OpenWeatherMap API Key in jeder Umgebung setzen!

1. Wähle deine gewünschte Umgebung (z.B. "Unwetterwarnung Development")
2. Klicke auf das Auge-Symbol neben der Umgebung
3. Klicke "Edit" 
4. Ersetze `YOUR_*_API_KEY_HERE` mit deinem echten API Key
5. Speichere die Änderungen

## 🌍 Umgebungen

### 🔧 Development
- **Zweck:** Tägliche Entwicklung
- **Location:** Fulda, DE (wie Modul Standard)
- **Sprache:** Deutsch
- **Cache:** 5 Minuten
- **Debug:** Aktiviert

### 🧪 Testing  
- **Zweck:** Automatisierte Tests
- **Location:** Berlin, DE
- **Sprache:** Englisch (für Konsistenz)
- **Cache:** 1 Minute
- **Zusätzliche Test-Locations:** München

### 🏭 Production
- **Zweck:** Live-System Tests
- **Location:** Fulda, DE (Modul Standard)
- **Sprache:** Deutsch
- **Cache:** 15 Minuten
- **Debug:** Deaktiviert
- **Monitoring:** Aktiviert

### 💻 Local Development
- **Zweck:** Lokale Joomla-Entwicklung
- **Location:** Fulda, DE
- **Sprache:** Deutsch
- **Cache:** 30 Sekunden
- **Timeout:** 15 Sekunden (länger für Debug)
- **Joomla URL:** http://localhost/joomla

## 📊 API-Endpunkte

### Weather Alerts
```
GET /data/2.5/onecall
```
- Hauptfunktion des Moduls
- Entspricht `UnwetterwarnungHelper::getWeatherAlerts()`
- Liefert aktuelle Unwetterwarnungen

### Current Weather
```
GET /data/2.5/weather
```
- Entspricht `OpenWeatherAPIHelper::getCurrentWeather()`
- Aktuelle Wetterdaten

### Geocoding
```
GET /geo/1.0/direct     # Ortsname → Koordinaten
GET /geo/1.0/reverse    # Koordinaten → Ortsname
```
- Entspricht `geocodeLocation()` und `reverseGeocode()`

### DWD Integration
```
GET https://maps.dwd.de/geoserver/dwd/wms
```
- Deutsche Wetterdienst Karten
- Für `dwd_map` Layout im Modul

## ✅ Tests

Jeder Request enthält automatische Tests:

- ✅ Status Code Validierung
- ✅ Response Time Check (< 10s)
- ✅ Datenstruktur Validierung
- ✅ API Key Validierung
- ✅ Rate Limit Monitoring

## 🔧 Anpassungen

### Koordinaten ändern
```json
"latitude": "50.264024",    // Deine Breite
"longitude": "9.319105"     // Deine Länge
```

### Sprache ändern
```json
"language": "de"    // de, en, fr, es, etc.
```

### Timeout anpassen
```json
"request_timeout": "10000"  // Millisekunden
```

## 🚨 Sicherheit

- **API Keys:** Niemals in Git committen!
- **Production:** Verwende separate API Keys
- **Rate Limits:** Beachte OpenWeatherMap Limits
- **Secrets:** Nutze Postman's Secret Variables

## 🔍 Troubleshooting

### API Key Fehler (401)
```
Unauthorized - Invalid API key
```
→ Prüfe deinen API Key in den Environment Variables

### Rate Limit (429)
```
Too Many Requests
```
→ Warte oder verwende höheres OpenWeatherMap Abo

### Timeout Fehler
```
Request timeout
```
→ Erhöhe `request_timeout` in der Umgebung

### Keine Warnungen
```
"alerts": []
```
→ Normal wenn keine Warnungen aktiv sind

## 📚 Referenzen

- [OpenWeatherMap API Docs](https://openweathermap.org/api)
- [Joomla Modul GitHub](https://github.com/whykiki/mod_unwetterwarnung)
- [DWD Geoserver](https://maps.dwd.de/geoserver/)

## 🤝 Support

Bei Problemen:
1. Prüfe API Key Konfiguration
2. Teste mit "API Key Validation" Request
3. Überprüfe Rate Limits
4. Kontaktiere Support bei persistenten Problemen

---

**Tipp:** Starte immer mit der "API Key Validation" um sicherzustellen, dass alles korrekt konfiguriert ist! 🚀
