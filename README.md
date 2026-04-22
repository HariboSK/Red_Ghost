# Red Ghost E-shop

Red Ghost je PHP + MySQL projekt pre predaj chilli produktov. Pouziva vlastny router, oddelene partialy, samostatnu API vrstvu pre kosik a postupne rastie z jednoducheho shopu na plnohodnotny administracny panel.

## Co pribudlo dnes

- kompletnejsi admin dashboard v slovencine
- vyclenenie dashboard logiky do [app/core/dashboard_helper.php](app/core/dashboard_helper.php)
- prehlad adminov, pouzivatelov, objednavok, prijatych sprav, produktov a zlavovych kodov
- CRUD pre produkty v dashboarde
- CRUD pre zlavove kody v dashboarde
- kontaktne spravy ulozene do databazy a zobrazene v admin paneli
- e-shop layout upraveny tak, aby sedel do tmaveho ramca stranky
- filter produktov presunuty nalavo ako samostatny panel
- triedenie a filtrovanie produktov cez JS
- produktovy grid nastaveny na 3 karty v riadku namiesto 4
- banner v posuvniku zmenseny a upraveny na kompaktnejsi layout
- databazova kompatibilita pre chybajuci stlpec `discount_percent`

## Aktualny stav projektu

- verejna domovska stranka
- e-shop s produktami, detailom produktu a bannerom
- kosik cez API endpointy
- login, registracia a uzivatelsky profil
- admin dashboard s viac sekciami
- kontakt form, ktory uklada spravy do `contact_messages`

## Hlavne funkcie

- vlastny routing bez frameworku
- session-based autentifikacia
- profilove popup menu v hlave e-shopu
- body vernostneho programu z `users.loayalty_points`
- produkty so zlavou, skladom a hodnotenim
- admin prehlad s poslednou aktivitou
- zlavove kody s platnostou, limitom pouziti a stavom aktivacie
- product listing s triedenim podla ceny, hodnotenia a skladu

## Pouzite technologie

- Backend: PHP 8+
- Databaza: MySQL / MariaDB
- Frontend: HTML, CSS, JavaScript
- Knihovny: Swiper, Font Awesome, PHPMailer
- Prostredie: XAMPP

## Kde co je

- routing: [app/core/router.php](app/core/router.php)
- shop logika: [app/core/shopService.php](app/core/shopService.php)
- dashboard logika: [app/core/dashboard_helper.php](app/core/dashboard_helper.php)
- e-shop view: [app/views/e_shop.php](app/views/e_shop.php)
- dashboard view: [app/views/dashboard.php](app/views/dashboard.php)
- hlavny shop CSS: [public/assets/css/style2.css](public/assets/css/style2.css)
- dashboard CSS: [public/assets/css/dashboard.css](public/assets/css/dashboard.css)
- shop JS: [public/assets/js/shop.js](public/assets/js/shop.js)
- dashboard JS: [public/assets/js/script.js](public/assets/js/script.js)
- databaza: [database.sql](database.sql)

## E-shop dnes

E-shop bol upraveny tak, aby vizualne nepreteka do celej sirky obrazovky. Banner je kompaktny, produkty maju tri stlpce na sirokem displeji a filter je v lavom paneli. Triedenie ostava zachovane cez `sortProducts()` a filter ceny cez `filterProducts()`.

## Dashboard dnes

Dashboard uz nie je len jednoduchy prehlad. Ma samostatnu helper triedu, navigaciu po sekciach, prehladove karty, inbox, objednavky, produkty a zlavove kody. Vsetko je po slovensky a pripraveno na dalsie rozsirovanie.

## Databaza a kompatibilita

Projekt teraz pocita aj s tym, ze live databaza sa moze lisit od `database.sql`. Najma shop service kontroluje existenciu `discount_percent`, aby shop nespadol na chybe pri starsej schema.

## Poznamky

- `contact_messages` pouziva polia `sender_name`, `sender_email`, `subject`, `message_text`, `status`
- produkty podporuju `discount_percent`, ale kod je kompatibilny aj bez neho
- admin dashboard a shop su stale server-renderovane PHP casti, nie SPA

## Struktura Projektu

```text
Red_Ghost/
|-- app/
|   |-- core/
|   |-- library/
|   |-- models/
|   |-- services/
|   |-- utils/
|   |-- views/
|
|-- config/
|-- public/
|-- storage/
|-- database.sql
|-- README.md
```

## Autor

Projekt: Red Ghost
Fokus: chilli e-shop, cisty PHP routing, modularna struktura a admin panel s postupnym rozsirovanim.

