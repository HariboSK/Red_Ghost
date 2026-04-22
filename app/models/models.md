Model vrstva (OOP)

Base model:

- base.model.php

Entity modely:

- product.model.php (CRUD pre products)
- contact_message.model.php (správy z kontaktného formulára)
- discount_code.model.php (CRUD pre discount_codes)
- user.model.php (admin a registrovaní users)
- order.model.php (sumáre objednávok)

Pravidlá:

- Modely obsahujú SQL dotazy, validáciu payloadu a prácu s PDO.
- View súbory neobsahujú SQL logiku.
- Dashboard helper orchestruje flow a používa modely.
