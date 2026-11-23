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
4. Receptkort med portionsjustering, grupperade ingredienser, numrerade steg. Ingen separat utskriftsknapp.
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
- Receptkort i samtliga grids: bild, namn, kort ingress, tid samt verkligt betyg och antal röster när sådana finns.
- Filter efter metod och råvara först; tidsfilter senare med begriplig hantering av lång passiv tillagning.
- Separera valda indexerbara ämnessidor från tillfälliga filter/sökresultat; utred canonical/noindex innan filter lanseras.
- Bevara fungerande befintliga URL:er tills den separata URL-migreringen nedan är kartlagd, testad och redo.

### Prioritering och verifiering
P1: huvudbild, betyg utan kommentarer, tidsöversikt, receptkort och relevanta internlänkar.
P2: startsida med receptfokus, sökning, tre ämnessidor och kopplade guider.
P3: avprickning, matlagningsläge och senare koppling till familjeappen.
- Före lansering: kontrollera båda receptformaten, bildladdning, mobil, portionsjustering, betyg och Recipe-data.
- Verifiera att bara avsedd publik version indexeras och följ upp landningssidor, klick och sökfrågor i Search Console efter lansering.

## Genomfört i testmiljön 2026-11-22 – tema 0.2.0
- Huvudbild och fungerande Gå till recept-länk för båda receptformaten.
- Upp till fyra serverrenderade relaterade recept, rangordnade efter gemensamma etiketter och kategorier. Utesluter aktuellt recept, utkast, lösenordsskyddade sidor och interna receptposter.
- Manuell stagingpublicering lyckades: Actions 45042846690, kod 2074ef89dd9d7578a8af005bc18964f25adf22b6. PHP-kontroll ingick.
- Verifierat Beef Jerky och Inlagd jalapeno: huvudbild laddas, genväg når rätt receptkort, relaterade länkar visas. Vid 390 px: innehållsbredd 375 px, ingen horisontell överströmning.
- Beef Jerky visar fortsatt 4.84/6 röster och samma aggregateRating. Inga testbetyg har skickats. Staging har fortsatt noindex.
- Kvar: betyg utan kommentarer (nuvarande WPRM-inställning öppnar recensionsmodal, ej färdig lösning), betyg/upphovsperson vid rubriken, kort ingress och tidsöversikt. Befintliga betygsinställningar lämnades oförändrade.
- Kvar: finslipa matchningen av relaterade recept; breda kategorier ger ibland svaga samband. Se över dubblett av Gå till recept i WPRM-innehåll och små bildoriginal.
- Ingen produktionspublicering gjord i detta steg.

## Beslut 2026-11-22 – receptkort och utskrift
Planerat efter Christophers önskemål; ännu inte implementerat.
- Ta bort alla knappar/länkar för ”Printa recept” och ”Skriv ut receptet”, både före receptet och inne i receptkortet, i båda receptformaten. Detta ersätter det tidigare kravet på en utskriftsknapp.
- Använd samma informationsstruktur för varje receptkort i alla grids: startsida, alla recept, ämnes-/kategorisidor, sökresultat och relaterade recept.
- Under bilden och receptnamnet: kort ingress på cirka 1–2 rader, betyg med antal röster samt tydligt märkt tillagningstid/total tid. Uppgifterna hör till varje receptkort, inte till hela griden.
- Visa ”Inga betyg ännu” när röster saknas. Hämta befintliga riktiga betyg från respektive receptformat; fabricera inga värden.
- För sous vide, BBQ och rökning: prioritera total tid för planering och skilj den från aktiv arbetstid där uppgifterna finns. Märk aldrig enbart aktiv tid som total tid.
- Saknad tid ska inte visas som 0 minuter. Saknad ingress behöver kompletteras redaktionellt; använd inte orelaterad eller avklippt ingredienstext.
- Säkerställ läsbar och kompakt utformning i mobil och konsekvent presentation mellan båda receptformaten.

## Receptkort genomförda i staging 2026-11-22 – tema 0.3.0, plugin 0.2.0
- Gemensam visning av kort ingress, verkligt betyg/antal röster och tider i receptgrids, relaterade recept och recept i arkiv/sökresultat.
- Ingress hämtas från receptets sammanfattning/beskrivning, annars manuellt utdrag. Saknad ingress behöver kompletteras; 8 av första sidans 20 recept saknar sådan text.
- WPRM visar befintlig total tid. Äldre recept behåller separata etiketter för förberedelse, tillagning och väntetid eftersom fritexten inte säkert kan summeras. Saknade tider visas inte som noll.
- Utskriftsknappar dolda i båda receptformaten. Betygsinmatning utan kommentarer är fortfarande en separat kvarstående punkt.
- Actions 45045100185 lyckades (0368552d87e91c5775249c5aead56e2a3cda4858); PHP-kontroll godkänd. Verifierat 20/20 kort med detaljblock på första listsidan, 4 relaterade detaljblock på både Beef Jerky och Inlagd jalapeno, inga synliga utskriftsknappar på dessa. Mobil 390 px: innehåll 375 px, ingen horisontell överströmning.
- Driftfynd: SFTP-publicering byter filer via symlänkar men PHP körde cachad äldre kod. One.coms knapp för cacherensning misslyckades. Att spara exakt den redan publicerade koden via WordPress filredigerare för real-food.php, functions.php och index.php löste detta genom kodcache-invalidering. Förbättra deployflödet med verifiering av körd kodversion och säker cacheinvalidering innan produktion; inga automatiska körningar ska införas.

## Enhetliga receptadresser och titlar – plan 2026-11-22
- Christopher vill ta bort onödigt ”Recept” ur recepttitlar och förenkla blandningen av adresser direkt under domänen och under /recipe/.
- Rekommenderad målstruktur: https://www.axenhorn.se/<receptnamn>/, exempel /julskinka-sous-vide/. Behåll fungerande korta adresser som /beef-jerky/.
- Titel och URL-slug hanteras separat: en titeländring på publicerat innehåll ska inte automatiskt utlösa adressbyte. Uppdatera även receptpluginets namn om det annars ger avvikande rubriker i kort och receptdata.
- Först inventera samtliga publika receptadresser, befintliga omdirigeringar, internlänkar och Search Console-data. Ta fram en explicit gammal → slutlig ny URL-tabell och kontrollera kollisioner med sidor, guider, kategorier och andra recept.
- Genomför URL-migrering som ett avgränsat steg, skilt från temalanseringen så effekter kan följas upp. Ingen URL-migrering genomförd ännu.
- Varje ändrad adress ska ge permanent serverbaserad 301 direkt till motsvarande slutliga receptadress. Äldre redirect-källor ska också peka direkt till slutmålet, utan kedjor, loopar eller generell omdirigering till startsidan.
- Uppdatera interna länkar, canonical, sitemap, Recipe-data och delningsmetadata till slutadresserna. Kontrollera gamla URL:ers 301 och nya URL:ers 200 samt att innehåll och betyg bevaras.
- Behåll omdirigeringarna långsiktigt, minst ett år och gärna permanent. Följ 404, indexering och organisk trafik i Search Console. URL-byte kan ge tillfälliga positionsförändringar.
- Rotadresser väljs för enkelhet och enhetlighet; de ger inte i sig en garanterad rankingfördel mot /recipe/.
- Referenser: https://developers.google.com/search/docs/crawling-indexing/url-structure och https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes

### URL-inventering klar 2026-11-22
Underlag finns i [docs/url-migration/README.md](docs/url-migration/README.md): 32 recept, 26 adressbyten, 18 publika titeländringar och 1 separat WPRM-namn. 40 explicita 301-regler omfattar även äldre alternativa rotadresser. Alla 25 äldre recept visas redan på två URL-varianter med samma recept-ID och canonical till /recipe/. Inga kollisioner mot 11 publicerade sidor eller mellan målen. Befintliga redirect-regler, utkast och Search Console återstår att kontrollera. Ingen titel eller URL ändrad ännu. Testmiljön behöver permalänkar före realistiska migreringstester.

## Fastställd designriktning 2026-11-23 – Varm matstudio
Christopher har bekräftat att originalförslaget **A – Varm matstudio** är målet för den fortsatta designen.
- Förlaga: [Real Food: Kyckling sous vide.png](https://chatgpt.com/library?file_id=libfile_822044f8395c8191a4617f758773f118&account_id=personal).
- Originalets palett: Ivory `#F7F2E8`, Olive `#687750`, Charcoal `#2F2F2B`.
- Utgå från originalets matbilder, luftiga receptlayout och tydliga rubriknivåer. På bred skärm visas ingredienser och instruktioner intill varandra; mobilen får en sammanhängande kolumn.
- Nuvarande stagingtema är en funktionell mellanversion. Dess terrakottaaccent behöver stämmas av mot det valda olivgröna förslaget, innan designen anses färdig.
- Bevara funktionella krav: huvudbild högt upp, riktiga betyg, tydliga tider, kort ingress i alla receptgrids, relaterade recept samt inga kommentarer eller utskriftsknappar.
- Den separata förlagan **B – Eld & råvara** får inte av misstag ersätta A som huvudriktning.
- Slutlig visuell jämförelse på dator och mobil återstår. Ingen ny designpublicering genomförd i detta steg.

## URL-test 2026-11-23
- Plugin 0.3.0 innehåller en uttrycklig stagingbegränsad, återställningsbar migration för 32 recept. 26 huvudadresser ändras; 18 publika titlar rensas.
- Separata receptnamn korrigerade för WPRM-julskinka 1749 och äldre lammracks 429.
- Bildbilagorna 471 och 1126 hade kollisioner med tonfisk- och kimchiadresserna. Deras bilagsslugs får suffixet `-bild`; bildfilerna ändras inte.
- Återställningsprov verifierade 32 ursprungliga titlar/slugs, ursprungliga enkla permalänkar och båda bilagsslugs. Migreringen aktiverades sedan igen.
- Provet visade att Redirections automatiska bevakning av inlägg skapade en omvänd regel för julskinka. Den regeln har avaktiverats via Redirection. Bevakning av **inlägg** är nu avstängd endast i staging; bevakning av sidor lämnades oförändrad. Kontrollera denna inställning före ytterligare återställningsprov.
- Koden är sparad i GitHub, senast `c7fc95964cb85a07deee81b8667686a6d2b07237`, och inlagd via WordPress filredigerare i staging med bekräftad sparning. Ingen Actions-körning eller produktionspublicering i detta steg.
- Före produktion återstår aktuell backup, Search Console-underlag, fullständig kontroll av interna länkar och sitemap, hantering av nya recept utanför den fasta testlistan och ett generellt skydd mot automatiska redirect-regler under migrering/återställning.

## Generella sidor i staging 2026-11-23 – tema 0.4.0
- Varm matstudio införd på startsida, receptöversikt, ämnessidor och sök/arkiv: benvit bakgrund, olivgrönt, mörk text, stora serifrubriker och gemensam navigation.
- Startsida med delad introduktion/matbild, receptsökning, sex utvalda recept och metodlänkar. Befintliga receptbilder och riktiga metadata används.
- Alla recept har egen introduktion, sökning och ämneslänkar. Receptkort behåller betyg, tider och ingress där källtext finns. Saknade ingresser återstår redaktionellt.
- Individuella recept behåller tidigare layout tills nästa designsteg; den nya formgivningen är avgränsad via body-klassen rf-studio.
- Källkod sparad i GitHub till och med 0f6e491a9feddae3ba283e9f859a6e8488def1c2. Tre temafiler sparade via WordPress temaredigerare med bekräftelse. Ingen Actions-körning eller produktionspublicering.
- Kontrollerat visuellt på dator: startsida, receptöversikt och Sous vide. Sökning på jalapeno gav receptresultat. Sju representativa URL:er svarar 200 med en h1 och fortsatt noindex; nya CSS-versionen laddas, individuella recept saknar avsiktligt rf-studio.
- Responsiva CSS-regler finns; separat visuell mobilgranskning återstår. Nästa steg är mobilfinjustering och individuell receptmall enligt originalförlagan.
