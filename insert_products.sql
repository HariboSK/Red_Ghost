
-- Pridaj test users
INSERT INTO user (name, email, password, role) VALUES
('Marko', 'marko@test.com', '$2y$10$hash1', 'customer'),
('Petra', 'petra@test.com', '$2y$10$hash2', 'customer'),
('Roman', 'roman@test.com', '$2y$10$hash3', 'customer');
INSERT INTO user (name, email, password, role) VALUES
('Admin', 'admin@test.com', '$2y$10$VygWHaBLmYW2w1dxDqVkJ.L0j6xDEyGLY5q5xyUEjpZKf6Vy9VC1i', 'admin');


-- Pridaj kategórie
INSERT INTO category (name) VALUES 
('Ostrá'),
('Mierna'),
('Extrémna');

-- Pridaj 5 produktov
INSERT INTO product (name, description, price, image, rating, featured, stock) VALUES
('Red Ghost Chilli Omacka', 'Vynikajúca chilli omacka s bohatou chutou a paprikovými nádychmi.', 12.99, '/assets/images/omacka3.webp', 5, 1, 15),
('Domaca Chilli Pasta', 'Tradičná slovenská recepta s domácimi paprikami.', 8.99, '/assets/images/omacky2.webp', 4, 1, 20),
('Susene Chilli Papriky', 'Prírodne sušené chilli papriky bez chemických prídavkov.', 14.99, '/assets/images/susene-chilli-Picsart-AiImageEnhancer.webp', 4, 0, 8),
('Hot Sauce XXL', 'Extrémna chilli omacka pre odvážnych. Intenzívna palivosť.', 16.99, '/assets/images/omacka3.webp', 5, 0, 5),
('Jemná Papriková Omacka', 'Mierna omacka vhodná pre jemnejšie chute.', 9.99, '/assets/images/omacky2.webp', 3, 0, 25);

-- Priraď kategórie (produkty budú mať ID 1-5)
INSERT INTO product_category (id_product, id_category) VALUES
(1, 2), (2, 2), (3, 2), (4, 3), (5, 1);

-- Pridaj recenzie
INSERT INTO product_review (rating, title, content, status, is_verified_purchase, id_product, id_user) VALUES
(5, 'Vyborna chut', 'Vyrazna chut paprik, dobra konzistencia. Objednam znova!', 'approved', 1, 1, 1),
(4, 'Kvalitny produkt', 'Pouzivam do varenia. Velmi doporucujem.', 'approved', 1, 1, 2),
(5, 'TOP', 'Presne to co som chcel!', 'approved', 1, 1, 3),
(4, 'Dobreee', 'Domaca omacka, super chuť.', 'approved', 1, 2, 1),
(5, 'Perfektne', 'Susene papriky su skvelé.', 'approved', 1, 3, 2),
(5, 'Extrémne dobré', 'Hot Sauce je ostry ale velmi doporucujem!', 'approved', 1, 4, 3);

-- Pridaj shop reviews (recenzie obchodu)
INSERT INTO shop_review (reviewer_name, rating, review_text, status, id_user) VALUES
('Marko', 5, 'Vynikajúci obchod! Rýchle doručenie a kvalitné produkty. Veľmi spokojný!', 'approved', 1),
('Petra', 5, 'Skvelé chilli produkty. Ceny sú fair a kvalita je top. Odporúčam!', 'approved', 2),
('Roman', 4, 'Dobré produkty, ale doručenie trvalo dlhšie. Inak všetko ok.', 'approved', 1),
('Zuzana', 5, 'Láska na prvý pohľad! Taký chutný obsah, a ešte sa mi páčia sociálne siete.', 'approved', 2),
('Miroslav', 4, 'Dobrý výber. Bol som milo prekvapený kvalitou balenia.', 'approved', 1),
('Katarina', 5, 'Najlepší chilli obchod ktorý som kedy navštívil! Všetko 5 hviezd!', 'approved', 2);