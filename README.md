# Real Food by Axenhorn

Testsida: https://www.axenhorn.se/stg_45384/ · Produktion: https://www.axenhorn.se/

Planerade fördjupningar finns i [innehållsbackloggen](BACKLOG.md).

Alla körningar startas manuellt i Actions → Real Food till One.com → Run workflow. Välj main och staging eller production. Inga körningar startas av push, pull request eller schema. Varje körning kontrollerar koden före publicering. Testa först i staging och starta därefter en separat produktionskörning. Endast main får publicera.

## Engångsinställning

Staging använder https://www.axenhorn.se/stg_45384 och /webroots/www/stg_45384. Produktion använder som standard https://www.axenhorn.se och /webroots/www. Produktionsvärden kan ersättas med miljövariablerna WP_ROOT och WP_URL. Byggs One.com-testmiljön om med en ny adress måste staginginställningarna uppdateras.

Lägg in SFTP_HOST, SFTP_USER och SFTP_PASSWORD som Actions secrets. Publiceringsskriptet kontrollerar One.com-serverns ED25519-nyckel mot samma SHA256-avtryck som Studio. SFTP-kontot behöver kunna skriva i wp-content, skapa symboliska länkar och göra atomiskt namnbyte med OpenSSH POSIX rename. Webbservern måste kunna följa dessa länkar och läsa versionsmarkören via HTTPS. En stagingmiljö med HTTP-inloggning behöver anpassad autentisering för kontrollen innan första körningen.

## Vad publiceras?

Första versionen publicerar logotyp och favicon till wp-content/real-food-assets. design/strukturskiss är referensmaterial. När WordPress-temat eller projektets eget tillägg är färdiga läggs de i wp-content/themes/real-food respektive wp-content/plugins/real-food. Aktivering sker separat i WordPress. WordPress, WP Recipe Maker, uploads och databasen hanteras av den befintliga installationen.

Filer laddas upp i en ny versionsmapp före bytet. Flödet kontrollerar installationsadress, versionsmarkör och startsidans HTTP-svar. Vid ett fångat fel efter bytet försöker flödet återställa föregående länkar. En avbruten runner kan kräva omkörning. Äldre versionsmappar behålls. En redan existerande vanlig mapp på en hanterad målsökväg får inte ersättas automatiskt. Tidigare utgåvor kan också återpubliceras genom en revert till main. Både staging och produktion verifierades den 21 november 2026: [staging #8](https://github.com/axenhorn/realfood/actions/runs/44933416409) och [produktion #9](https://github.com/axenhorn/realfood/actions/runs/44933566473). Relativa symboliska länkar används eftersom SFTP och webbservern har olika absoluta rotkataloger.

## Inför ombyggnaden

En One.com-testkopia har skapats och dess startsida har verifierad noindex/nofollow. Detta hindrar indexering men är inte åtkomstskydd. En permanent databasbackup har sparats hos One.com. WordPress-filbackup (181 MB) med isolerad uppackning, jämförelse av innehåll/rättigheter och SHA256-kontroll verifierades den 21 november 2026: [backup #3, försök 2](https://github.com/axenhorn/realfood/actions/runs/44939918703). Arkiv och uppackad kontrollkopia ligger under SSH-kontots privata hemkatalog, realfood-backups, utanför webbrot. Flödet startas manuellt via Actions → WordPress filbackup och omfattar WordPress-kataloger samt konfiguration och vanliga rotfiler, inte andra webbplatser. Fullständigt återställningsprov med databas och start av applikationen återstår före större migreringar. Testkopian ersätter inte en oberoende backup.

Publiceringsflödet överför endast projektets komponenter, inte hela testmiljön eller databasen. Använd inte One.coms fullständiga staging-till-live-kopiering för rutinpublicering: den kan skriva över innehåll som tillkommit på livesidan. Befintligt tema och recept är ännu inte migrerade. UserFeedback Lite uppdaterades till 1.12.0 och WPForms Lite till 2.0.5.1, först i staging och sedan produktion den 21 november 2026. Startsidan, /beef-jerky/ och /recipe/recept-inlagd-jalapeno/ verifierades med HTTP 200 och oförändrade titlar, canonical-adresser och förekomst av Recipe-schema. One.com visar därefter en kvarvarande sårbarhet: Post Grid 2.3.24, medelrisk och ingen fixversion angiven. Post Grid används av receptlistningen och behöver ersättas och testas i staging innan det tas ur drift.
