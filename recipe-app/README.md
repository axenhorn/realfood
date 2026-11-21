# Familjens receptbok — första utvecklingsversion 0.1

En mobilanpassad, privat receptbok för familjen. Byggd för PHP 8.5 och MySQL på One.com, med vanlig HTML, CSS och JavaScript. Inga byggberoenden eller AI-nycklar krävs i den här versionen.

## Status

Källkod framtagen, inte publicerad. JavaScripts syntaxkontroll och 13 tester av portionsomräkningen har körts och godkänts. PHP, MySQL, webbläsarflöden och verkliga receptlänkar har **inte körtestats** i utvecklingsmiljön eftersom PHP och en testdatabas saknas. PHP-tester och GitHub Actions-kontroller medföljer. Detta är en utvecklingsversion som ska verifieras innan familjen börjar använda den som enda receptarkiv.

## Implementerat

- Gemensam receptsamling i MySQL, tillgänglig för familjemedlemmar med ett delat lösenord.
- Import via länk från sidor som exponerar Schema.org Recipe som JSON-LD.
- Redigerbar förhandsgranskning av namn, beskrivning, ingredienser, steg, bildlänk, källa och portioner.
- Bild visas via originalets URL. Bildfilen kopieras inte; originalet kan sluta fungera. Egna fotouppladdningar är nästa steg.
- Formulär för egna recept och en yta att klistra in recepttext som stöd. Texten fördelas manuellt i fälten; ingen automatisk AI-tolkning ännu.
- Sökning i namn, beskrivning, ingredienser och kategorier.
- Flera fria kategorier per recept. Filter byggs från kategorierna i sparade recept.
- Favoriter, egna anteckningar, redigering och radering.
- Portionsomräkning av tydliga mängder i början av ingrediensrader: decimaler, bråk, blandade tal och intervall. Oklara uttryck som "2 x 400 g" lämnas oförändrade. Tider och temperaturer i instruktionerna ändras inte. Grundreceptet skrivs aldrig över av portionsväljaren.
- Avbockning av ingredienser och steg under pågående visning. Markeringarna återställs när receptet öppnas igen.
- Utskrift och JSON-export av samlingen. Återimport av exporten är ännu inte implementerad.
- Skydd mot överskrivning när samma recept redigeras samtidigt på olika enheter.
- Sessionsinloggning, CSRF-skydd, begränsade inloggningsförsök, parametriserade databasfrågor och HTML-escaping.
- URL-import begränsad till HTTP(S), publika IPv4-adresser och normalportar. IP låses per hämtning, inklusive omdirigeringar. Storlek och tid begränsas. Ingen inloggnings- eller betalvägg kringgås.

## Begränsningar i importen

En länk är ingen garanti för ett komplett recept. Instagram, Facebook, TikTok, inloggningsskyddade sidor och JavaScript-renderade sidor kan sakna åtkomlig recepttext. Version 0.1 har ingen särskild social integration och transkriberar inte video. Klistra i dessa fall in text som stöd och fyll i fälten manuellt. Portioner som exempelvis "12 kakor" behöver bekräftas manuellt. Importen väljer det första Recipe-objektet på sidan.

Källänk behålls. Kontrollera alltid importerade mängder och portioner. Håll familjens samling privat.

## Installation på One.com

1. Skapa ett **privat GitHub-repo**, exempelvis `axenhorn/receptbok`, och lägg in projektet där. Den tillgängliga GitHub-anslutningen kunde inte skapa repo, så detta är ännu inte gjort.
2. Skapa önskad subdomän, exempelvis `recept.axenhorn.se`. Adressen är ett förslag och är inte registrerad eller konfigurerad av projektet.
3. Skapa en separat MySQL-databas och kör `schema.sql` i phpMyAdmin.
4. Lägg projektets `src/` och en egen `config.php` **utanför den publika webbroten**. `public/` ska vara subdomänens webbrot. Om One.com kräver fast webbrotsmapp: placera `src/` och `config.php` i dess överordnade mapp och kopiera innehållet i `public/` till webbroten. Kontrollera att `api.php` hittar dem via `../`.
5. Kopiera `config.example.php` till `config.php`, ange databasvärd, databasnamn, användare och lösenord. Filen ignoreras av Git.
6. Generera ett hashat familjelösenord i en PHP-miljö:

   ```sh
   php -r 'echo password_hash(readline("Familjelösenord: "), PASSWORD_DEFAULT), PHP_EOL;'
   ```

   Lägg resultatet i `family_password_hash`. Lägg aldrig databaslösenord eller familjens klartextlösenord i repo, chatt eller publik webbkod.
7. Aktivera HTTPS. Behåll `secure_cookies => true` för drift.
8. Kontrollera att PHP har `curl`, `dom` och `pdo_mysql`. Kör tester och verifieringslistan nedan.
9. Koppla SFTP-publicering till GitHub Actions först när exakt webbrotsökväg och secrets är bestämda. Ingen deploy-workflow som kan skriva till fel mapp ingår. Befintliga appar berörs inte.

Den gemensamma inloggningen är avsedd för en enda familj. Byt lösenord vid behov. Separata familjer och individuella konton ingår inte i denna version. Lösenordsbyte återkallar inte redan inloggade sessioner; rensa även serverns sessioner om omedelbar återkallelse behövs.

## Tester

```sh
node --test tests/quantities.test.mjs
node --check public/app.mjs
php tests/import.test.php
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

GitHub Actions kör syntax- och enhetstester vid push/PR. Ingen automatisk publicering sker.

### Verifiera i testmiljö innan drift

- Logga in, prova fel lösenord, logga ut och kontrollera att API:t inte läcker recept utan inloggning.
- Lägg till ett manuellt recept, ladda om och öppna samma samling på en andra enhet.
- Importera tre riktiga recept från olika webbsidor; kontrollera bild, portioner, ingredienser och alla steg mot originalet.
- Prova en Instagramlänk som inte går att läsa: reservflödet ska fungera och inte ge ett påhittat recept.
- Ändra 4 portioner till 2 och 6; kontrollera decimaler, bråk och intervall samt att tillagningsstegen är oförändrade.
- Skapa kategorier, filtrera, sök, markera favorit, redigera och radera ett testrecept.
- Redigera samma recept på två enheter. Den andra sparningen ska ge ett tydligt konfliktmeddelande.
- Kontrollera mobilvisning, tangentbordsnavigering, dialoger, felmeddelanden och utskrift.
- Exportera och kontrollera att samtliga sparade recept finns med i JSON-filen.
- Ta databasbackup innan verkliga familjerecept läggs in.

## Förslag till nästa iterationer

1. **Enkel bildhantering:** ladda upp ett eget foto, spara en lokal kopia av importerad bild där det är tillåtet och hitta en alternativ bild om originalet saknas.
2. **Social import:** testa riktiga familjelänkar från Instagram. Komplettera med AI-tolkning av kopierad bildtext, skärmbild och foto av pappersrecept. Saknade uppgifter ska markeras, inte gissas. AI-tjänst och kostnad bestäms separat.
3. **Familjens inköpslista:** välj recept och portioner, slå ihop kompatibla ingredienser, bocka av det som finns hemma och synka mellan enheter. Behåll olika enheter om säker konvertering saknas.
4. **Veckoplanering:** dra favoriter till veckans dagar och skapa inköpslistan från planen.
5. **Lagat & omtyckt:** personliga betyg, senast lagat och "vill laga". Familjens justeringar hålls separat från originalet.
6. **Köksläge/PWA:** större text, skärmen hålls vaken när enheten stöder det, timer och tillgång till valda recept offline.
7. **Trygg hantering:** återimport av backup, dubblettkontroll, individuella inbjudna familjekonton och autosparade formulärutkast.

### Kategorier som startförslag

Vardagsmat, Helgmat, Pasta, Vegetariskt, Fisk, Kyckling, Kött, Grill & BBQ, Sous vide, Tillbehör, Såser, Bakning och Dessert. Appen tvingar inte fram dessa: kategorier uppstår när de skrivs på recepten.

### Inspiration och tekniska källor

- Handlis: receptsamling, veckoplanering och inköpslista enligt utvecklarens App Store-beskrivning: https://apps.apple.com/se/app/handlis/id6819080577
- Schema.org Recipe: https://schema.org/Recipe
- Googles dokumentation om receptdata: https://developers.google.com/search/docs/appearance/structured-data/recipe
