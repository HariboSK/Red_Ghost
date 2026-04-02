SPRAVIT
- [ ] Admin panel na správu produktov
- [ ] Nákupný košík s uložením do databázy
- [ ] Checkout a platby
- [ ] Užívateľské konta
- [ ] Potvrdenie objednávky emailom
- [ ] pridanie prepinanie light a dark mode 
- [ ] pridanie toggle button na zmenu jazyka
- [ ] uprava css login aj php 


cart.php 
shop.js


Pozriem sa na váš add-to-cart endpoint a navrhnem presnú server-side kontrolu skladu priamo pre váš projekt, aby to sedelo na existujúci kód a nie len všeobecný príklad.

V tvojom prípade to znamená: pred pridaním do košíka si na serveri načítaš aj stock z databázy a porovnáš ho s množstvom v košíku. Teraz to v cart.php:21 vracia len id, name, price, takže stock sa zatiaľ nekontroluje.

Postup je tento:

V SELECTe pridaj stock
V funkcii get_product_by_id uprav dotaz tak, aby čítal aj stock z products.

Vráť stock z funkcie
Do poľa, ktoré funkcia vracia, pridaj aj stock.

Pred pridaním položky over dostupnosť
V časti action = add skontroluj, či už nemáš v košíku viac kusov, než je na sklade.

Logika pre add:

načítaj produkt z DB
zober currentQty z session košíka
ak currentQty + 1 > stock, vráť chybu
inak zvýš quantity
Princíp:

ak stock = 3 a v košíku sú 2 kusy, ďalší add ešte prejde
ak stock = 3 a v košíku sú 3 kusy, add sa zastaví
To isté sprav aj pri update
V časti update nesmieš dovoliť quantity vyššie než stock.
Príklad logiky, ktorú máš doplniť do cart.php:97:

pri add:
ak stock <= existujúce množstvo v košíku, vráť 409 a hlášku typu „Na sklade nie je dosť kusov“

pri update:
ak quantity > stock, tiež vráť 409

Ak chceš, toto je presne ten kus logiky v slovách:

SELECT id, name, price, stock FROM products WHERE id = ?
return aj stock
v add:
currentQty = $_SESSION['cart'][$productId]['quantity'] ?? 0
if currentQty + 1 > product['stock'] => chyba
v update:
if quantity > product['stock'] => chyba
Ak chceš, v ďalšej správe ti napíšem priamo hotový blok pre add a update do tvojho súboru tak, aby si to len vložil.


CTRL CSS PEEK