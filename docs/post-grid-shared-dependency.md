# Fortsatt stagingkontroll 2026-11-22

Sida 91 i staging använder nu [real_food_recipes collection="sous-vide"] i stället för [post_grid id="433"]. Övrigt sidinnehåll bevarades. Publik kontroll visar 12 nya receptkort utan gamla grid 433.

Alla recept kontrollerades i 390×844 viewport: en kolumn med läsbara kort och ingen uppmätt horisontell överbredd. Befintliga cookie- och administratörsnotiser överlappar nederdelen; fullständig besökarinteraktion återstår.

En kontroll av hela HTML-sidor, inklusive mallinnehåll, visar grid 339 på startsidan, övriga publicerade sidor och recept. Detta beroende syntes inte vid första inventeringen av enbart sidinnehåll.

Grid 339 i stagingadmin: posttyper post, wprm_recipe och recipe; slumpmässig ordning; 12 poster; ingen paginering. Taxonomifilter och exakt placering behöver granskas innan ersättning. Att inkludera både post och interna WPRM-poster kan ge samma recept via två poster; det måste kontrolleras vid migrationen.

Post Grid ska fortsatt vara aktivt tills den gemensamma listan och eventuella övriga beroenden är ersatta och testade. Ingen produktionsändring gjord.
