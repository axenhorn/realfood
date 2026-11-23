# Stagingresultat 2026-11-23

Status: aktiverat endast på https://www.axenhorn.se/stg_45384. Produktion oförändrad.

Slutlig HTTP-kontroll efter rättning av Redirections omvända julskinkeregel: 32/32 recept svarar 200, har rätt publik titel, canonical till slutadressen, noindex och Recipe-schema. 47/47 gamla sökvägar svarar 301 direkt till respektive slutadress. Se staging-verification-2026-11-23.json.

Listningen verifierades tidigare med 20 + 12 recept över två sidor. Beef Jerky behåller 4.84/6 betyg, samma ingredienser, instruktionstexter och bild; instruktionernas schema-länkar följer den nya permalinkstrukturen. WPRM-julskinka och det separata äldre lammracksnamnet är rensade.

Återställningsprov återställde 32 titlar/slugs, båda bildbilagsslugs och den tidigare enkla permalinkstrukturen. Provet utlöste Redirections automatiska inläggsbevakning och skapade en felaktig omvänd regel för julskinka. Den är nu inaktiv och bevakning av inlägg är avstängd i staging. Aktivera inte bevakningen före ytterligare migrations-/återställningsprov utan ett skydd mot detta. Den sparade återställningen omfattar inte hela databasen eller Redirections regelhistorik.

Koden är medvetet begränsad till den fasta listan och stagingadressen. Den är inte en färdig generell produktionsmigrering. Full sitemap-/internlänksgranskning, Search Console, aktuell backup, cachehantering och stöd för framtida recept återstår före produktion. Det äldre migrationsunderlaget beskriver läget före detta test.
