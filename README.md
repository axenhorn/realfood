# Real Food by Axenhorn

Testsida: https://realfood.axenhorn.se/ · Produktion: https://www.axenhorn.se/

Planerade fördjupningar finns i [innehållsbackloggen](BACKLOG.md).

GitHub Actions kontrollerar pull requests. En push till main publicerar samma commit till staging och därefter till produktion när staging har verifierats. En manuell omkörning på main stöds.

## Engångsinställning

Skapa GitHub-miljöerna staging och production under Settings → Environments. Staging använder https://realfood.axenhorn.se och /webroots/d36760fb. Lägg in WP_ROOT och WP_URL i production för den befintliga WordPress-installationen.

Lägg in SFTP_HOST, SFTP_USER, SFTP_PASSWORD och SFTP_KNOWN_HOSTS som Actions secrets. De kan läggas på reponivå om samma konto används. SFTP_KNOWN_HOSTS innehåller en verifierad OpenSSH known_hosts-rad för värden på port 22. Använd ett värdnyckelavtryck som kontrollerats mot One.com. SFTP-kontot behöver kunna skriva i wp-content, skapa symboliska länkar och göra atomiskt namnbyte med OpenSSH POSIX rename. Webbservern måste kunna följa dessa länkar och läsa versionsmarkören via HTTPS. En stagingmiljö med HTTP-inloggning behöver anpassad autentisering för kontrollen innan första körningen.

## Vad publiceras?

Första versionen publicerar logotyp och favicon till wp-content/real-food-assets. design/strukturskiss är referensmaterial. När WordPress-temat eller projektets eget tillägg är färdiga läggs de i wp-content/themes/real-food respektive wp-content/plugins/real-food. Aktivering sker separat i WordPress. WordPress, WP Recipe Maker, uploads och databasen hanteras av den befintliga installationen.

Filer laddas upp i en ny versionsmapp före bytet. Flödet kontrollerar installationsadress, versionsmarkör och startsidans HTTP-svar. Vid ett fångat fel efter bytet försöker flödet återställa föregående länkar. En avbruten runner kan kräva omkörning. Äldre versionsmappar behålls. En redan existerande vanlig mapp på en hanterad målsökväg får inte ersättas automatiskt. Tidigare utgåvor kan också återpubliceras genom en revert till main. Första Actions-körningen behöver verifieras mot båda verkliga installationerna.
