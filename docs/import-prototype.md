# Importprototyp 0.1 – 2026-11-28

Testadress: https://www.axenhorn.se/stg_45384/?rf_import_lab=1

Tillfälligt implementerad som ett avgränsat rf_lab_-block i temats functions.php. Endast staging och manage_options; inte den slutliga apparkitekturen. Ingen ändring i publika recept eller privat familjedatabas. Testrecept lagras i aktuell webbläsares localStorage och kan exporteras som JSON. Använd inte som enda receptarkiv.

## Fungerar nu
- Mobilanpassad vy i Varm matstudio-paletten.
- URL-import av Recipe JSON-LD, även nästlade @graph, typ-listor och HowToSection.
- Val mellan flera Recipe-objekt, högst tio.
- Redigerbar granskning av namn, beskrivning, originalets yield, ingredienser, steg och källa. Ingen gissning av portioner.
- Manuell inmatning och enkel rubrikbaserad textfördelning. Ingen AI-tolkning.
- Lokal lagring efter granskningsbekräftelse; export.
- Inloggningskrav, CSRF-nonce, femsekunders importbegränsning. Offentliga IPv4-destinationer valideras och DNS låses med CURLOPT_RESOLVE per hämtning/redirect; normalportar, begränsad storlek och timeout.

## Verifierat
- PHP syntax analyserad med php-parser; JS med node --check. Ingen lokal PHP-runtime tillgänglig, däremot verkliga körningar i WordPress staging.
- Beef Jerky: 8 ingredienser, 6 steg; sparande och omladdning fungerade.
- ICA pannkakor-grundsmet-2083: 6 ingredienser och 4 steg, även efter slutlig härdning av hämtaren.
- Inklistrad egen testtext: två ingredienser och två steg fördelades utan förändring.
- Intern IP-länk avvisas; startsida utan Recipe ger tydligt reservflöde.
- Oinloggad GET till prototypen returnerade 403.

## Återstår
- Testa användarens verkliga Instagram-/Facebooklänkar. Ingen särskild social integration, OCR eller videotranskribering ingår; dessa är inte verifierade som automatisk import.
- Bildimport och bättre parsning av recept utan JSON-LD.
- Manuell mobilgranskning, fler källor och tester av flervals-/redirectfallen.
- Flytta importmodulen till separat app på app.axenhorn.se; individuella konton och familjeisolering måste vara på plats före delad serverlagring. Det äldre receptboksutkastets gemensamma familjelösenord är inte målarkitekturen.
- Adminstyrd publiceringskö separat från privat import. Inga importer publiceras automatiskt.
