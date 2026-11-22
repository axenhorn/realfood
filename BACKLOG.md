# Planerat innehåll

## Rökning och BBQ
Bygg ut avdelningen med praktiska guider. Förslag till första ämnen är att komma igång med röken, välja rökved och planera en tillagning. Länka guiderna till relevanta recept och tillbehör.

## Sous vide
Fördjupa den befintliga introduktionen och guiden till tider och temperaturer. Utgå från frågor som uppstår vid val av råvara, önskad konsistens och avslutande stekyta. Knyt guiderna till de befintliga recepten.

Christopher och Filippa prioriterar ämnen och egna erfarenheter inför arbetet med innehållet.

## Färger och logotyp
Använd [Behance-samlingen](https://www.behance.net/gallery/145316603/56-Beautiful-Color-Palettes-For-Your-Next-Design) som färgreferens. Välj sajtens palett först och vidareutveckla sedan logotypen och faviconen utifrån de valda färgerna.

## Receptsidor – prioriterat inför lansering
Tillagt 2026-11-22 efter Christophers önskemål. Planerat, ännu inte färdigimplementerat.

### Betygsättning utan kommentarer
- Besökare ska kunna ge recept 1–5 stjärnor utan kommentarsfält.
- Visa genomsnitt och antal röster nära receptets rubrik, med tydlig möjlighet att betygsätta vid receptkortet.
- Bevara befintliga riktiga betyg vid eventuell receptmigrering. Visa ett tydligt obetygsatt läge för recept utan röster.
- Säkerställ att synliga betyg och Recipe/aggregateRating-data stämmer överens; skapa inte dubbla eller påhittade betygsdata.
- Testa tangentbord, mobil och rimligt skydd mot upprepade röster.

### Relaterade recept och internlänkning
- Visa 3–4 relevanta recept efter receptkortet, med bild, receptnamn och vanliga serverrenderade länkar.
- Prioritera gemensam råvara, tillagningsmetod och passande tillbehör; undvik slumpmässiga rekommendationer.
- Uteslut aktuellt recept, dubbletter, utkast och privata apprecept.
- Länka till den publika receptsidan, inte till interna receptposter. Stöd båda befintliga receptformaten.
- Dölj avsnittet om relevanta alternativ saknas.

### Receptbild högt upp
- Placera huvudbilden direkt efter rubrik, betyg och en kort introduktion, före längre bakgrundstext.
- Behåll en lättåtkomlig ”Gå till recept”-länk nära rubriken.
- Hämta bilden från rätt recept/huvudinlägg även när äldre och nyare receptformat skiljer sig.
- Använd responsiva bildstorlekar, angivna dimensioner och beskrivande alt-text. Undvik onödig dubbelvisning av samma stora bild.
- Prioritera laddningen av huvudbilden när den syns direkt på skärmen och kontrollera mobil beskärning och laddningsprestanda.

## Struktur och användbarhet – referensgranskning 2026-11-22
Planeringsunderlag, inte implementerat. Granskningen avser representativa recept- och kategorisidor, inte en fullständig trafik- eller UX-analys. Stora sajters struktur bevisar inte i sig vad som orsakat deras söktrafik.

### Referenser och slutsatser
- ICA: bild, betyg, tidsuppgift, ingredienser, instruktioner och liknande recept. Kategorisidan kombinerar kort introduktion med sökning, filter och relaterade kategorier.
  - https://www.ica.se/recept/grillad-kycklingfile-712727/
  - https://www.ica.se/recept/kyckling/grillad/
- Köket: tydlig upphovsperson, bild, betyg och tidsuppgift; avprickning och matlagningsläge.
  - https://www.koket.se/marinerad-och-grillad-kyckling
- Serious Eats: metodförklaringar, innehållsförteckning, tid/temperatur och vanliga frågor i en fördjupande guide. Inspiration för våra teknikguider.
  - https://www.seriouseats.com/the-food-lab-complete-guide-to-sous-vide-chicken-breast
- Google: Recipe-data och representativ bild av färdig rätt; test med Rich Results Test. ItemList kan användas på receptsamlingar. Markup innebär ingen garanti för särskild sökvisning.
  - https://developers.google.com/search/docs/appearance/structured-data/recipe

### Föreslagen receptmall, uppifrån och ned
1. Brödsmulor, tydligt receptnamn, riktiga betyg med antal röster och namngiven upphovsperson.
2. Kort introduktion, ”Gå till recept” och huvudbild nära rubriken.
3. Förberedelsetid, aktiv tid, väntetid/tillagningstid och total tid tydligt åtskilda där relevant; portioner och metod.
4. Receptkort med portionsjustering, grupperade ingredienser, numrerade steg och utskrift.
5. Egna provlagade tips, byten, förberedelse/förvaring och vanliga frågor när de faktiskt hjälper. Längre metodförklaringar läggs i länkade guider.
6. Tydlig betygsättning utan kommentarer; passande tillbehör och 3–4 relaterade recept.
- En huvudrubrik, läsbar mobiltypografi och tangentbordsåtkomst.
- Väsentligt receptinnehåll ska kunna läsas utan inloggning.
- Upphovsperson, provlagning och ändringsdatum ska beskriva verkliga förhållanden.
- Planera avprickning och ”håll skärmen tänd” som nästa förbättring efter grundmallen.

### Navigation, startsida och ämnessidor
- Huvudnavigation: Recept, Grill & BBQ, Rökning, Sous vide, Guider; tydlig receptsökning.
- Startsidan ska tidigt visa matbilder, sökning och ingångar till metoderna; minska utrymmet för den nuvarande stora textintroduktionen.
- Skapa innehållsrika ämnessidor för de tre metoderna. Bygg underkategorier efter råvara först när samlingen motiverar det.
- Exempel på innehållssamband: Sous vide → kycklingrecept → temperaturguide → passande sås/tillbehör. Länka även tillbaka från guiden.
- Receptkort: bild, namn, tid och verkligt betyg när sådant finns.
- Filter efter metod och råvara först; tidsfilter senare med begriplig hantering av lång passiv tillagning.
- Separera valda indexerbara ämnessidor från tillfälliga filter/sökresultat; utred canonical/noindex innan filter lanseras.
- Bevara fungerande befintliga URL:er även om navigationen ändras.

### Prioritering och verifiering
P1: huvudbild, betyg utan kommentarer, tidsöversikt, receptkort och relevanta internlänkar.
P2: startsida med receptfokus, sökning, tre ämnessidor och kopplade guider.
P3: avprickning, matlagningsläge och senare koppling till familjeappen.
- Före lansering: kontrollera båda receptformaten, bildladdning, mobil, portionsjustering, utskrift, betyg och Recipe-data.
- Verifiera att bara avsedd publik version indexeras och följ upp landningssidor, klick och sökfrågor i Search Console efter lansering.
