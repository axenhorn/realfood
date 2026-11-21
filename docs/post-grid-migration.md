# Ersättning av Post Grid

Inventering 2026-11-21 via publik WordPress REST API (renderat sidinnehåll). Inga ändringar har gjorts i WordPress.

| Sida | Sid-ID | Grid-ID |
| --- | --- | --- |
| /alla-recept/ | 465 | 464 |
| /sous-vide/ | 91 | 433 |

Av 11 publicerade sidor innehöll dessa två renderade Post Grid-markörer. Detta utesluter inte användning i widgetar, mallar eller annat innehåll.

Alla recept visar både vanliga inlägg (exempel /beef-jerky/) och äldre recipe-poster (exempel /recipe/oxbringa-brisket-sous-vide/). Första renderade listan innehåller 20 receptlänkar och paginering. Sous vide-listan innehåller 12 receptlänkar, inklusive pico de gallo och inlagd rödlök. Ändra inte urvalet utifrån receptnamnet; läs befintlig gridkonfiguration först.

## Innan implementation

1. Läs konfiguration och metadata för grid 464 och 433 i staging: posttyper, taxonomier, inkluderade/exkluderade ID:n, sortering, antal och paginering.
2. Sök efter Post Grid-shortcodes och block i alla publicerade inlägg, sidor, widgetar och mallar. Spara ursprungliga shortcodevärden inför återställning.
3. Implementera serverrenderade receptkort med vanliga länkar, responsiva bilder och paginering. Bevara nuvarande urval och ordning under pluginbytet; ändrad redaktionell kategorisering är ett separat steg.
4. Använd befintliga permalänkar för både post och recipe. Exponera aldrig wprm_recipe som extra publika receptsidor.

## Acceptans i staging

- Jämför samtliga receptlänkar och ordning före/efter, även senare sidor.
- Kontrollera kategorifilter och sortering mot sparad konfiguration.
- Kontrollera layout på mobil och dator, tangentbordsnavigation och bilder.
- Inaktivera Post Grid först när alla beroenden är ersatta; kontrollera därefter att inga råa shortcodes eller tomma listor finns.
- Kontrollera HTTP-status, titlar, canonical och Recipe-schema på befintliga receptsidor.
- Testa återgång till ursprungliga listor och pluginaktivering.

## Produktion

Gör en separat manuell publicering efter godkända tester. Kopiera inte stagingdatabasen över produktionen. Bevara receptens URL:er. Fullständigt återställningsprov med databas och applikationsstart återstår inför större migreringar. Filbackup och isolerad filåterläsning är redan verifierade enligt README.
