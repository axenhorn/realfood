# Kontroll av receptlistan 2026-11-22

Publika HTTP-anrop utan administratörsinloggning användes.

- Den nya listan på staging-sida 465 visar 32 unika måladresser över två sidor (20 + 12).
- Alla 32 måladresser kunde hämtas utan HTTP-fel.
- Alla 32 svar innehöll sidtitel och Recipe-typ i strukturerade data.
- Originalets två listsidor gav också 32 receptlänkar i den avgränsade extraktionen.

Kontrollen visar att alla länkade testrecept svarar och innehåller receptschema. Den utgör inte validering av alla schemafält eller fullständig jämförelse av receptinnehåll, bilder och URL-mappning. Mobilkontroll, återstående Post Grid-beroenden och test med Post Grid avaktiverat återstår. Ingen ändring har gjorts i produktion.
