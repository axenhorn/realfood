# Receptadresser – migrationsunderlag 2026-11-22

Status: förberett och kontrollerat mot publik sajt. Inte aktiverat.

## Resultat

- 32 unika recept; 26 nya huvudadresser och 6 oförändrade.
- 18 ändrade publika titlar samt namnet i ett separat WPRM-recept (julskinka, ID 1749).
- 40 explicita 301-regler, inklusive alternativa rotadresser.
- Alla 25 äldre recept svarar i dag 200 både under /recipe/ och direkt under domänen; rotvarianten anger /recipe/ som canonical. Samma recept-ID verifierat på båda varianterna.
- Inga kollisioner mellan föreslagna receptadresser eller mot de 11 publicerade sidorna. Utkast, befintliga redirect-regler och reserverade routes återstår att kontrollera i administrationen.
- Alla 32 nuvarande huvudadresser svarar 200. Elva mål finns redan som alternativa adresser till samma recept, 15 mål svarar 404 före migreringen.

## Filer

- `inventory.json`: aktuella recept-ID:n, gamla/nya titlar och sökvägar, canonical samt HTTP-kontroller.
- `titles.csv`: föreslagna titeländringar. WPRM:s schema-namn är observerat, rå databastext ska jämföras före ändring.
- `redirects.csv`: källsökväg, slutlig målsökväg, status 301 och recept-ID. Generiskt underlag, inte verifierad importfil för Redirection.

## Genomförande i ordning

1. Läs befintliga redirect-regler, utkast och Search Console-data. Ta aktuell databasbackup. Kontrollera att recept-ID:n stämmer i testmiljön.
2. Verifiera routing för posttypen `recipe` vid roten, inklusive permalink, canonical, sitemap och interna receptlänkar. Slugbyte ensamt tar inte bort posttypens /recipe/-bas.
3. Använd testmiljön med riktiga permalänkar för att testa adresser. Den har för närvarande frågeparametrar; enbart tester mot ?p=ID räcker inte.
4. Ändra bara specificerade titlar och slugs. Bevara ingredienser, instruktioner, bilder och betyg. Ändra inte fungerande rotadresser i onödan.
5. Lägg in 301-reglerna samtidigt som nya adresser aktiveras. Uppdatera äldre redirect-källor direkt till slutmålet. Reglerna ska gälla exakta sökvägar, inte en generell regel som tar bort alla förekomster av ordet recept.
6. Uppdatera interna länkar, canonical, sitemap, receptdata och delningsmetadata. Verifiera samtliga källor → 301 → mål med 200, rätt recept-ID och canonical; inga kedjor/loopar.
7. Gör produktionsmigreringen som separat steg från temalanseringen. Följ upp indexering, trafik och 404. Behåll omdirigeringarna långsiktigt.

## Adresser och titlar

| ID | Nuvarande sökväg | Slutadress | Titel efter städning |
|---|---|---|---|
| 1723 | `/beef-jerky/` | `/beef-jerky/` | Äkta Beef Jerky |
| 320 | `/recept-julskinka-sous-vide/` | `/julskinka-sous-vide/` | Perfekt julskinka – Sous Vide |
| 916 | `/flankstek-sous-vide/` | `/flankstek-sous-vide/` | Mör och saftig Flankstek Sous Vide |
| 868 | `/flapsteak-sous-vide/` | `/flapsteak-sous-vide/` | Mör och saftig Flap steak Sous Vide |
| 847 | `/blue-cheese-dipp-en-amerikansk-klassiker/` | `/blue-cheese-dipp-en-amerikansk-klassiker/` | Blue Cheese Dipp – En Amerikansk klassiker |
| 838 | `/kycklingvingar-med-honung-och-vitlok-sous-vide/` | `/kycklingvingar-med-honung-och-vitlok-sous-vide/` | Kycklingvingar med honung och vitlök – Sous Vide |
| 824 | `/pilgrimsmusslor-sous-vide/` | `/pilgrimsmusslor-sous-vide/` | Perfekta pilgrimsmusslor – Sous Vide |
| 753 | `/recipe/oxbringa-brisket-sous-vide/` | `/oxbringa-brisket-sous-vide/` | Oxbringa (Brisket) Sous Vide |
| 741 | `/recipe/trip-tip-sous-vide-barbecue/` | `/trip-tip-sous-vide-barbecue/` | Trip Tip Sous Vide barbecue |
| 707 | `/recipe/inlagd-chili-hela/` | `/inlagd-chili-hela/` | Inlagd Chili (hela) |
| 684 | `/recipe/egg-bites-sous-vide/` | `/egg-bites-sous-vide/` | Egg Bites Sous Vide |
| 671 | `/recipe/picanha-sous-vide/` | `/picanha-sous-vide/` | Picanha Sous Vide |
| 649 | `/recipe/habanerohotsauce/` | `/habanerohotsauce/` | Habanero Hot Sauce |
| 498 | `/recipe/recept-chokladmousse/` | `/chokladmousse/` | Chokladmousse |
| 470 | `/recipe/recept-tonfisk-sous-vide/` | `/tonfisk-sous-vide/` | Saftig tonfisk – Sous Vide |
| 455 | `/recipe/kalkonbrost-sous-vide/` | `/kalkonbrost-sous-vide/` | Kalkonbröst – Sous Vide |
| 443 | `/recipe/recept-lax-sous-vide/` | `/lax-sous-vide/` | Saftig lax – Sous Vide |
| 429 | `/recipe/lammracks-sous-vide/` | `/lammracks-sous-vide/` | Lammracks – Sous Vide |
| 423 | `/recipe/recept-majs-sous-vide/` | `/majs-sous-vide/` | Majs – Sous Vide |
| 396 | `/recipe/recept-bearnaisesas/` | `/bearnaisesas/` | Bearnaisesås |
| 389 | `/recipe/recept_hollandaisesas/` | `/hollandaisesas/` | Hollandaisesås |
| 372 | `/recipe/recept-graddsas-brunsas/` | `/graddsas-brunsas/` | Riktig gräddsås (brunsås) |
| 324 | `/recipe/recept-inlagd-rodlok/` | `/inlagd-rodlok/` | Picklad rödlök |
| 284 | `/recipe/pulled-pork-sous-vide/` | `/pulled-pork-sous-vide/` | Pulled Pork – Sous Vide |
| 222 | `/recipe/recept-kimchi/` | `/kimchi/` | Kimchi |
| 156 | `/recipe/recept-inlagd-jalapeno/` | `/inlagd-jalapeno/` | Inlagd jalapeno |
| 148 | `/recipe/pulled-pork/` | `/pulled-pork/` | Pulled Pork |
| 130 | `/recipe/recept-pico-de-gallo/` | `/pico-de-gallo/` | Pico de Gallo |
| 125 | `/recipe/mangosalsa/` | `/mangosalsa/` | Mangosalsa |
| 112 | `/recipe/recept-ceviche/` | `/ceviche/` | Ceviche |
| 7 | `/recipe/recept-citron-och-ingefaravatten/` | `/citron-och-ingefaravatten/` | Citron- och ingefäravatten |
| 219 | `/recipe/recept-inlagd-vitlok/` | `/inlagd-vitlok/` | Picklad eller inlagd vitlök |
