# Red Ghost E-shop

Moderny webovy projekt pre prezentaciu a predaj chilli produktov.
Projekt je postaveny na PHP + MySQL s vlastnym routerom, modulovymi partials a samostatnou API vrstvou pre kosik.

## Prehlad

Red Ghost obsahuje:

- verejnu domovsku stranku
- e-shop s produktami a detailom produktu
- kosik s API endpointmi
- login/registraciu a uzivatelsky profil
- admin vetvu (dashboard)
- kontakt form s odosielanim emailu

Projekt bezi lokalne cez XAMPP a je navrhnuty tak, aby sa dal jednoducho rozsirovat.

## Hlavne funkcie

- Vlastny routing bez frameworku
- Shop listing s kartami produktov
- Produkt detail stranka s recenziami a zoomom obrazka
- Session-based autentifikacia
- Profilove popup menu v shop headri
- Body vernostneho programu citane z DB stlpca users.loayalty_points
- Cart API endpoint pripraveny pre JS integraciu

## Tech Stack

- Backend: PHP 8+, mysqli
- Databaza: MySQL / MariaDB
- Frontend: HTML, CSS, JavaScript
- Knihovny: Swiper, Font Awesome, PHPMailer
- Prostredie: XAMPP (Apache + MySQL)

## Struktura Projektu

```text
Red_Ghost/
|-- app/
|   |-- core/
|   |   |-- router.php
|   |   |-- login-register.php
|   |   |-- session_helper.php
|   |   |-- api/cart.php
|   |
|   |-- views/partials/
|   |   |-- home.php
|   |   |-- e_shop.php
|   |   |-- product.php
|   |   |-- shopcart.php
|   |   |-- login.php
|   |   |-- userprofile.php
|   |   |-- header-shop.php
|   |   |-- footer-shop.php
|   |
|   |-- library/PHPMailer-7.0.2/
|
|-- config/
|   |-- config.php
|   |-- config.env
|
|-- public/
|   |-- index.php
|   |-- assets/
|   |   |-- css/
|   |   |-- js/
|
|-- database.sql
|-- README.md
```

## Ako Spustit Projekt Lokalene

1. Naklonuj alebo skopiruj projekt do XAMPP htdocs

```bash
cd c:/xampp/htdocs
git clone <repo-url> Red_Ghost
```

2. Spusti Apache a MySQL v XAMPP

3. Vytvor databazu a importuj schemu

```sql
CREATE DATABASE red_ghost;
USE red_ghost;
SOURCE database.sql;
```

4. Ak uz mas existujucu DB, pridaj vernostne body stlpec

```sql
ALTER TABLE users
ADD COLUMN loayalty_points INT(11) NOT NULL DEFAULT 0;
```

5. Otvor projekt v browseri

```text
http://localhost/Red_Ghost/
```

## Routing

Router je v app/core/router.php.
Priklady dostupnych rout:

- /
- /home
- /e-shop
- /product?id=ID
- /shopcart
- /login
- /userprofile
- /dashboard
- /logout
- /api/cart

## Session a Prihlasenie (Ako to funguje)

Login flow je postaveny takto:

1. Formular z login.php posle POST na /login-register
2. login-register.php overi usera v DB
3. Pri uspechu sa zavola session_regenerate_id(true)
4. Zavola sa rg_session_store_user(...)
5. Uzivatel sa presmeruje na dashboard alebo userprofile

Session helper v app/core/session_helper.php:

- zjednocuje session kluce
- cita user data z canonical aj legacy klucov
- nacitava a refreshuje body z users.loayalty_points

To zabezpeci, ze header popup, userprofile a ostatne casti citaju rovnake data konzistentne.

## Body Vernostneho Programu

Zdroj bodov: users.loayalty_points

- Pri logine sa body nacitaju a ulozia do session
- Header vie body priebezne refreshnut z DB
- Kompatibilita ostava aj pre stare kluce (loyalty_points)

Priklad update bodov:

```sql
UPDATE users
SET loayalty_points = loayalty_points + 15
WHERE email = 'user@example.com';
```

## API Kosik

Cart endpoint:

- /api/cart?action=summary
- /api/cart?action=add
- /api/cart?action=update
- /api/cart?action=clear

Poznamka: server-side validacia skladu je pripravena na doplnenie ako dalsi krok hardeningu.

## Stav Projektu

Hotovo:

- Home + E-shop layout
- Produkt detail route a UI
- Login/registracia
- User profile
- Header popup s login stavom
- Session helper a jednotne auth/session spravanie

Planovane:

- Admin panel pre CRUD produktov
- Checkout a platby
- Rozsirena historia objednavok
- Jazykovy prepinac
- Light/Dark mode prepinac

## Bezpecnostne Poznamky

Aktualne su v login/register casti priame SQL dopyty so string interpolation.
Odporucany dalsi krok:

- prejst na prepared statements pre register/login queries
- doplnit CSRF ochranu pre formulare

## Autor

Projekt: Red Ghost
Fokus: chilli e-shop, cisty PHP routing, modularna struktura, postupna evolucia do produkcnejsieho riesenia.
