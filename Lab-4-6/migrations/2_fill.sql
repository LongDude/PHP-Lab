SET NAMES utf8;

INSERT INTO tariffs (name, base_price, base_dist, dist_cost) VALUES
    ('Эконом',     100, 2, 20),
    ('Комфорт',    150, 3, 18),
    ('Бизнес',     200, 4, 15),
    ('Премиум',    300, 5, 12);

INSERT INTO users (name, phone, email, password, role) VALUES 
-- administrator
('admin', null, 'admin@xyz.com', MD5('admin'), 'admin'),
-- clients
('test', null, 'test@xyz.com', MD5('test'), 'client'),
('Ivan', '+7 (123) 456-78-90', 'ivan@mail.ru', MD5('ivan'), 'client'),
-- Drivers:
-- Tariff 1 (Эконом)
('Иванов Иван Иванович',        '+7 (912) 345-67-81', 'ivanov.ivan1@example.com',   MD5('ivanov'),    'driver'),
('Петров Пётр Петрович',        '+7 (912) 345-67-82', 'petrov.petr1@example.com',   MD5('petrov'),    'driver'),
('Сидорова Анна Михайловна',    '+7 (912) 345-67-83', 'sidorova.anna@example.com',  MD5('sidorova'),  'driver'),
('Кузнецов Дмитрий Сергеевич',  '+7 (912) 345-67-84', 'kuznetsov.d@example.com',    MD5('kuznetsov'), 'driver'),
('Смирнова Ольга Викторовна',   '+7 (912) 345-67-85', 'smirnova.olga@example.com',  MD5('smirnova'),  'driver'),
-- Tariff 2 (Комфорт)
('Николаев Андрей Павлович',    '+7 (912) 345-67-86', 'nikolaev.a@example.com',  MD5('nikolaev'),  'driver'),
('Фёдорова Екатерина Игоревна', '+7 (912) 345-67-87', 'fedorova.e@example.com',  MD5('fedorova'),  'driver'),
('Волков Артём Александрович',  '+7 (912) 345-67-88', 'volkov.a@example.com',    MD5('volkov'),    'driver'),
('Алексеева Мария Дмитриевна',  '+7 (912) 345-67-89', 'alekseeva.m@example.com', MD5('alekseeva'), 'driver'),
('Соколов Павел Олегович',      '+7 (912) 345-67-10', 'sokolov.p@example.com',   MD5('sokolov'),   'driver'),
-- Tariff 3 (Бизнес)
('Лебедева Анастасия Сергеевна','+7 (912) 345-67-11', 'lebedeva.a@example.com', MD5('lebedeva'), 'driver'),
('Козлов Игорь Вадимович',      '+7 (912) 345-67-12', 'kozlov.i@example.com',   MD5('kozlov'),   'driver'),
('Новикова Виктория Андреевна', '+7 (912) 345-67-13', 'novikova.v@example.com', MD5('novikova'), 'driver'),
('Морозов Алексей Николаевич',  '+7 (912) 345-67-14', 'morozov.a@example.com',  MD5('morozov'),  'driver'),
('Егорова Дарья Павловна',      '+7 (912) 345-67-15', 'egorova.d@example.com',  MD5('egorova'),  'driver'),
-- Tariff 4 (Премиум)
('Дмитриев Константин Ильич',   '+7 (912) 345-67-16', 'dmitriev.k@example.com', MD5('dmitriev'), 'driver'),
('Орлова Алина Максимовна',     '+7 (912) 345-67-17', 'orlova.a@example.com',   MD5('orlova'),   'driver'),
('Гусев Михаил Андреевич',      '+7 (912) 345-67-18', 'gusev.m@example.com',    MD5('gusev'),    'driver'),
('Тихонова Елена Владимировна', '+7 (912) 345-67-19', 'tihonova.e@example.com', MD5('tihonova'), 'driver'),
('Фролов Артур Романович',      '+7 (912) 345-67-20', 'frolov.a@example.com',   MD5('frolov'),   'driver');


INSERT INTO drivers (user_id, intership, car_license, car_brand, tariff_id, rating) VALUES
-- Tariff 1 (Эконом)
(4, 12, 'А123 БВ78', 'Lada Granta', 1, 4.5),
(5, 8, 'В234 ЦВ77', 'Hyundai Solaris', 1, 4.2),
(6, 18, 'Е345 КХ76', 'Kia Rio', 1, 4.8),
(7, 6, 'О456 МР75', 'Volkswagen Polo', 1, 4.0),
(8, 24, 'Р567 СА74', 'Skoda Rapid', 1, 4.6),

-- Tariff 2 (Комфорт)
(9,  14, 'Т678 УК73', 'Toyota Camry', 2, 4.7),
(10, 9, 'У789 ХМ72', 'Hyundai Creta', 2, 4.9),
(11, 11, 'Ф890 ОС71', 'Kia Sportage', 2, 4.5),
(12, 7, 'Х901 ТР70', 'Volkswagen Tiguan', 2, 4.3),
(13, 22, 'Ц012 АВ69', 'Skoda Octavia', 2, 4.6),

-- Tariff 3 (Бизнес)
(14, 5, 'М123 ЛО68', 'Mercedes E-Class', 3, 4.9),
(15, 16, 'Н234 ПА67', 'BMW 5 Series', 3, 4.8),
(16, 10, 'П345 СТ66', 'Audi A6', 3, 4.7),
(17, 3, 'Р456 МУ65', 'Lexus ES', 3, 4.9),
(18, 19, 'С567 ХА64', 'Volvo S90', 3, 4.6),

-- Tariff 4 (Премиум)
(19, 25, 'У678 ЦП63', 'Mercedes S-Class', 4, 4.9),
(20, 2, 'Ф789 ШЛ62', 'BMW 7 Series', 4, 5.0),
(21, 8, 'Х890 ЩР61', 'Audi A8', 4, 4.8),
(22, 13, 'Ц901 ЭТ60', 'Porsche Panamera', 4, 4.9),
(23, 6, 'Щ012 ЮН59', 'Tesla Model S', 4, 4.7);