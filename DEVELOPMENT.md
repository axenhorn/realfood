# Real Food – utvecklingsläge

## Mål och beslut
Organisk trafik till axenhorn.se har högst prioritet. Publik webbplats och privat familjeapp hör till samma projekt. WordPress/WP Recipe Maker behålls tills migreringsplan och befintliga URL:er är verifierade.

Design: varm benvit #F7F2E8, kolgrå #2F2F2B och terrakotta #B55336, luftig receptlayout och redaktionella rubriker. Arbetslogga: stiliserad grill med två rökslingor. Slutliga SVG/PNG/ICO-filer och test i 16/32 px återstår; appens nuvarande tecken är endast en platshållare.

## Levererat i denna utvecklingsgren
- Tidigare receptbok v0.1 samlad i recipe-app, inklusive import och befintliga tester.
- Lokala utkast för nya och befintliga recept, återställning vid nästa öppning, rensning efter sparande/utloggning. Revision bevaras så äldre utkast inte tyst skriver över nyare recept.
- Matlagningsläge med större text och Screen Wake Lock där enheten stöder det. Låset släpps när receptet stängs.
- Första färganpassning och utökat stöd i anteckningsfältet för provlagning.
- Befintliga favoriter, ingredienssökning, kategorier och portionsanpassning återanvänds.

## Återstår innan fler familjer eller publik lansering
Den importerade appen använder fortfarande ett gemensamt familjelösenord. Den får INTE erbjudas som flerfamiljslösning i nuvarande skick.

1. Individuella konton, verifiering, återställning och familjer med säkra inbjudningar. Serverkontrollerad familjeavgränsning i varje fråga och mutation, även bilder och utkast. Separata tester för läckage mellan två familjer.
2. Publiceringskö: inskickat, under bearbetning, redo för granskning, publicerat, komplettering, avböjt. Endast Christophers serverkonfigurerade administratör får publicera.
3. Separat publiceringskopia. Inga privata anteckningar följer med. Inskickad version, källuppgifter, rättigheter, bearbetad text och publiceringsbild lagras separat. Ändringar privat påverkar inte liveversionen.
4. Publik receptmall med arbetslogga, mobilanpassning, Recipe-data som matchar innehållet, kanonisk adress, bildformat, interna länkar och förhandsgranskning. Befintliga URL:er inventeras före migrering.
5. Egna samlingar, strukturerade metodfält för BBQ/rökning/sous vide, bilduppladdning och provlagningshistorik. Skanning efter stabil grund.
6. Därefter delad inköpslista och veckoplanering. Näringsvärden, prisuppskattningar, butikskoppling och socialt flöde väntar.

## Kontroll och leverans
Node-syntaxkontroll och 13 befintliga mängdtester passerade lokalt. PHP saknas i utvecklingsmiljön; PHP-importtest och PHP-syntax måste köras före driftsättning. Ingen databas eller webbpublicering har gjorts. Ingen merge till main: befintligt workflow publicerar automatiskt från main. Leveransflödet behöver beslutas/anpassas till manuell publicering före merge.
