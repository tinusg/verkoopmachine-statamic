# Verkoopmachine voor Statamic

Toon actueel aanbod uit [Verkoopmachine](https://www.verkoopmachine.nl) in een
Statamic-site. De add-on haalt de inhoud op aan de hand van één ingestelde
**koppelcode** en rendert die als een toegankelijk, gegroepeerd overzicht met
deeplinks naar [Koemarkt](https://www.koemarkt.nl).

De eerste versie ondersteunt:

- Vee-advertenties;
- aankomende Rundveeveilingen waarbij de ingestelde client betrokken is.

De opzet is bewust uitbreidbaar: de API retourneert groepen met items. Nieuwe
advertentiesoorten kunnen daardoor worden toegevoegd zonder dat een bestaande
Statamic-template of tag hoeft te veranderen.

## Vereisten

- PHP 8.3 of hoger;
- Statamic 6;
- een bereikbare Verkoopmachine API met de `v1` client-content endpoints.

Voor productie gebruikt de add-on standaard
`https://www.verkoopmachine.nl/api/v1`.

## Installatie

Installeer de package via Packagist:

```bash
composer require tinusg/verkoopmachine-statamic
```

Publiceer daarna de stylesheet:

```bash
php artisan vendor:publish --tag=verkoopmachine-statamic
```

De CSS komt terecht op:

```text
public/vendor/verkoopmachine-statamic/css/verkoopmachine.css
```

### Lokale ontwikkeling

Bij lokale ontwikkeling kan de package als Composer path-repository worden
gekoppeld. Vervang het pad door de locatie van de package naast de Statamic-site.

```bash
composer config repositories.verkoopmachine-statamic path ../verkoopmachine-statamic
composer require tinusg/verkoopmachine-statamic:'*@dev'
```

Publiceer de assets opnieuw nadat de CSS van de package is gewijzigd:

```bash
php artisan vendor:publish --tag=verkoopmachine-statamic --force
```

## Configuratie

Open in het Statamic-control-panel de instellingen van de add-on en vul de
**Koppelcode** in. Je vindt deze in Verkoopmachine onder
**Instellingen > Bedrijfsgegevens**. Bijvoorbeeld:

```text
middelveld-machines
```

De koppelcode verbindt de Statamic-site met het juiste Verkoopmachine-bedrijf.
Bij het opslaan controleert de add-on via de API of de koppelcode geldig is.

- Bestaat de koppelcode niet, dan wordt de instelling niet opgeslagen en
  verschijnt een melding dat de koppelcode ongeldig is.
- Is de API tijdelijk onbereikbaar, dan wordt ook niet opgeslagen. De melding
  maakt onderscheid tussen een onbekende koppelcode en een niet-uitvoerbare
  controle.

De instelling wordt door Statamic opgeslagen in:

```text
resources/addons/verkoopmachine-statamic.yaml
```

Een handmatig voorbeeld (de technische configuratiesleutel blijft
`client_slug`):

```yaml
client_slug: middelveld-machines
```

Gebruik bij voorkeur het control panel. Daarmee blijft de server-side validatie
van de koppelcode altijd actief.

### Omgevingsvariabelen

Deze variabelen zijn optioneel. De getoonde waarden zijn de standaardwaarden.

```dotenv
VERKOOPMACHINE_API_URL=https://www.verkoopmachine.nl/api/v1
VERKOOPMACHINE_CONNECT_TIMEOUT=3
VERKOOPMACHINE_TIMEOUT=8
VERKOOPMACHINE_CACHE_SECONDS=300
VERKOOPMACHINE_STALE_CACHE_SECONDS=86400
```

Voor een lokale Verkoopmachine-installatie kan bijvoorbeeld dit worden gebruikt:

```dotenv
VERKOOPMACHINE_API_URL=https://www.verkoopmachine.test/api/v1
```

Maak na een wijziging aan `.env` zo nodig de Laravel-configcache leeg:

```bash
php artisan config:clear
```

## Weergave op een pagina

Laad de stylesheet één keer in de `<head>` van de globale Statamic-layout:

```antlers
{{ verkoopmachine:styles }}
```

Plaats de stylesheet vóór de eigen site-CSS of Vite-bundel. Daardoor heeft
site-eigen CSS bij gelijke specificiteit voorrang.

Gebruik vervolgens de overzichtstag waar het aanbod moet verschijnen:

```antlers
{{ verkoopmachine:overview }}
```

Standaard toont de tag maximaal zes items per groep, voor zowel Vee als
Rundveeveilingen.

### Parameters

`types` is een pipe-gescheiden lijst met gewenste groepen. `limit` geldt per
groep en ligt altijd tussen 1 en 24.

```antlers
{{ verkoopmachine:overview types="vee" limit="3" }}

{{ verkoopmachine:overview types="vee|rundveeveilingen" limit="12" }}
```

Voor losse overzichtspagina's gebruik je per Statamic-template één type:

```antlers
{{# Pagina /vee #}}
{{ verkoopmachine:overview types="vee" limit="24" }}

{{# Pagina /rundveeveilingen #}}
{{ verkoopmachine:overview types="rundveeveilingen" limit="24" }}
```

Beschikbare typen in deze versie:

| Type | Inhoud | Deeplink |
| --- | --- | --- |
| `vee` | Publiek zichtbare Vee-advertenties van de client | Koemarkt-advertentie |
| `rundveeveilingen` | Publiek zichtbare, toekomstige Rundveeveilingen van de client | Koemarkt-veiling |

Als er geen actuele inhoud beschikbaar is, toont de add-on een neutrale
lege-status. Bij een ontbrekende configuratie of niet beschikbare API toont hij
een korte foutmelding, zonder technische foutdetails aan bezoekers bloot te
stellen.

## Opbouw en styling

Alle markup van de add-on staat binnen:

```html
<section data-verkoopmachine>
    <!-- add-on inhoud -->
</section>
```

Alle meegeleverde selectors zijn hierop gescoped met `:where(...)`. De add-on
legt dus geen globale reset, headings of linkstijl op aan de rest van de site.

### CSS-variabelen

Pas de uitstraling bij voorkeur aan via de variabelen op de wrapper:

```css
[data-verkoopmachine] {
    --vm-brand: #f0a17d;
    --vm-card-accent: #0f766e;
    --vm-border: #99f6e4;
    --vm-muted: #475569;
    --vm-surface: #f8fafc;
    --vm-text: #172033;
}
```

### Gerichte overrides

Omdat de basisselectors met `:where()` een lage specificiteit hebben, zijn
gerichte overrides overzichtelijk:

```css
[data-verkoopmachine] .vm-grid--vee {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

[data-verkoopmachine] .vm-card--vee {
    border-radius: 0;
}

[data-verkoopmachine] .vm-auction__date {
    border-radius: 0.25rem;
}
```

Plaats deze regels in de eigen stylesheet van de Statamic-site, ná
`{{ verkoopmachine:styles }}`. Pas de gepubliceerde CSS in `public/vendor` niet
rechtstreeks aan: een volgende `vendor:publish --force` overschrijft die.

### Beschikbare CSS-hooks

| Hook | Functie |
| --- | --- |
| `[data-verkoopmachine]` | Wrapper en CSS-variabelen |
| `.vm-group` | Eén inhoudsgroep |
| `.vm-group__heading` | Kop met groepsnaam en totaal |
| `.vm-grid` | Responsive kaartgrid |
| `.vm-grid--vee` | Grid voor Vee-advertenties |
| `.vm-grid--rundveeveilingen` | Grid voor Rundveeveilingen |
| `.vm-card` | Klikbare kaart/deeplink |
| `.vm-card--vee` | Beeldkaart voor een Vee-advertentie |
| `.vm-card--auction` | Horizontale regel voor een Rundveeveiling |
| `.vm-card__media` | Beeldvlak en badges van een Vee-advertentie |
| `.vm-card__image` | Afbeelding van een advertentie |
| `.vm-card__image--placeholder` | Koemarkt-rasplaceholder van een Vee-advertentie |
| `.vm-card__overlay` | Extra placeholder-overlay voor embryo en sperma |
| `.vm-card__badge--photos` | Indicator met het aantal beschikbare foto's |
| `.vm-card__body` | Tekstgedeelte van een kaart |
| `.vm-card__seller` | Badge met de naam van de aanbieder |
| `.vm-auction__date` | Datumblok van een Rundveeveiling |
| `.vm-auction__location` | Locatie van een Rundveeveiling |
| `.vm-message` | Lege status of foutmelding |
| `.vm-message--error` | Foutmelding |

## Verkoopmachine API

De add-on gebruikt de volgende read-only endpoints:

```text
GET /api/v1/clients/{koppelcode}
GET /api/v1/clients/{koppelcode}/content?types=vee,rundveeveilingen&limit=6&locale=nl
```

De eerste endpoint valideert de instelling. De tweede retourneert een client en
een lijst groepen. Iedere groep bevat een `key`, `label`, `total` en `items`.
Een item bevat onder meer `title`, `summary`, `image_url`, `display` en `url`.
De URL is een volledige deeplink naar de passende Koemarkt-pagina.

De API geeft uitsluitend publieke, zichtbare inhoud terug. De add-on verstuurt
geen API-sleutel en verwacht geen persoonlijke of contactgegevens in de
response.

## Caching en beschikbaarheid

Elke unieke combinatie van koppelcode, typen, limiet en taal wordt vijf minuten
gecached. Dat beperkt HTTP-verzoeken tijdens drukbezochte pagina's.

Na een succesvolle response bewaart de add-on daarnaast een fallback-versie,
standaard 24 uur. Is de API tijdelijk niet beschikbaar, dan toont de add-on de
laatst bekende inhoud. Alleen wanneer er geen bruikbare cache is, verschijnt de
foutmelding op de pagina.

## Problemen oplossen

### De koppelcode wordt afgewezen

Controleer of de koppelcode precies overeenkomt met de code onder
**Instellingen > Bedrijfsgegevens** en of `VERKOOPMACHINE_API_URL` naar de
juiste omgeving wijst. Test desgewenst:

```bash
curl -i "https://www.verkoopmachine.nl/api/v1/clients/jouw-koppelcode"
```

Een `404` betekent dat de koppelcode niet bestaat of niet actief beschikbaar is.

### Geen aanbod zichtbaar

Controleer achtereenvolgens:

1. Of de koppelcode is opgeslagen in de add-oninstellingen.
2. Of de client publiek zichtbare Vee-advertenties of toekomstige
   Rundveeveilingen heeft.
3. Of de gewenste `types` niet zijn beperkt in de tag.
4. Of de API bereikbaar is vanaf de Statamic-server.

Leeg aanbod is geen fout: de tag toont dan de lege-status.

### De layout bevat geen styling

Controleer of `{{ verkoopmachine:styles }}` in de globale layout staat en of
het bestand bestaat in:

```text
public/vendor/verkoopmachine-statamic/css/verkoopmachine.css
```

Publiceer de assets opnieuw wanneer het bestand ontbreekt.

## Ontwikkeling en tests

Installeer afhankelijkheden in de package en voer de testset uit:

```bash
composer install
vendor/bin/phpunit --do-not-cache-result
```

De tests controleren onder andere de validatie van de koppelcode, de API-aanroep
en de fallback naar verouderde cache. Test daarnaast in de consumerende
Statamic-site
dat een pagina met `{{ verkoopmachine:overview }}` de verwachte inhoud en
deeplinks rendert.

## Uitbreiden met nieuwe typen

Nieuwe advertentiesoorten horen als nieuwe groepen in de Verkoopmachine API
beschikbaar te komen. Houd daarbij dezelfde groeps- en itemstructuur aan. Zodra
de API het type accepteert, kan de tag het doorgeven via `types`, bijvoorbeeld:

```antlers
{{ verkoopmachine:overview types="vee|rundveeveilingen|nieuw-type" }}
```

Voeg voor een nieuw type ook een gerichte API-test, voorbeelddata en eventueel
een `.vm-grid--nieuw-type` CSS-override toe.
