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

## Verifierad konfiguration och införande

WordPress-admin kontrollerad 2026-11-21:
- Grid 464: posttyper wprm_recipe och recipe, endast publish, datum fallande, 20 per sida, inga valda taxonomifilter eller inkluderade/exkluderade ID:n. Vanlig paginering.
- Grid 433: enbart recipe, 12 per sida, slumpmässig sortering, inga valda taxonomifilter eller inkluderade/exkluderade ID:n. Vanlig paginering. Namnet Sous Vide innebär alltså inte ett kategorifilter.
- Admin visar totalt sex gridkonfigurationer: 1777, 464, 462 (utkast), 433, 353 och 339. Användningen av de återstående konfigurationerna behöver fortfarande granskas.

Real Food 0.1.0 har publicerats och aktiverats i staging, körning [44943958715](https://github.com/axenhorn/realfood/actions/runs/44943958715). Sidan 465 använder nu `[real_food_recipes collection="all"]`; originalet var `[post_grid id="464"]`.
Första kontrollen visade 20 kort på sida 1 och 12 på sida 2 samt noindex. Desktoputseendet granskades. Detta är inte ett fullständigt funktionstest av alla länkar eller mobilvyn.

Komponenten använder WPRM:s parent_post_id för att länka interna receptposter till publicerade föräldrasidor. Originalets posttyper ska bevaras; den tidigare observationen om vanliga inlägg avsåg länkarnas mål, inte gridens posttypurval.

Kvar: migrera sidan 91, verifiera alla beroenden inklusive widgetar, testa mobil och återgång, inaktivera Post Grid i staging och kör kontroller utan det. Ingen produktionspublicering eller inaktivering av Post Grid har gjorts.
