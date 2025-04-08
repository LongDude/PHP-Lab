SET NAMES utf8;

INSERT INTO tariffs (name, base_price, base_dist, base_time, dist_cost, time_cost) VALUES
    ('Эконом',     100, 2, 5, 20, 5),    -- Base: 2km/5min included
    ('Комфорт',    150, 3, 7, 18, 4.5),  -- Higher base price, lower per-km
    ('Бизнес',     200, 4, 10, 15, 4),   -- Premium service with wait time
    ('Премиум',    300, 5, 15, 12, 3.5); -- Luxury class with max inclusions

INSERT INTO drivers (name, phone, email, intership, car_license, car_brand, tariff_id, rating) VALUES
-- Tariff 1 (Эконом)
('Иванов Иван Иванович', '+7 (912) 345-67-81', 'ivanov.ivan1@example.com', 12, 'А123 БВ78', 'Lada Granta', 1, 4.5),
('Петров Пётр Петрович', '+7 (912) 345-67-82', 'petrov.petr1@example.com', 8, 'В234 ЦВ77', 'Hyundai Solaris', 1, 4.2),
('Сидорова Анна Михайловна', '+7 (912) 345-67-83', 'sidorova.anna@example.com', 18, 'Е345 КХ76', 'Kia Rio', 1, 4.8),
('Кузнецов Дмитрий Сергеевич', '+7 (912) 345-67-84', 'kuznetsov.d@example.com', 6, 'О456 МР75', 'Volkswagen Polo', 1, 4.0),
('Смирнова Ольга Викторовна', '+7 (912) 345-67-85', 'smirnova.olga@example.com', 24, 'Р567 СА74', 'Skoda Rapid', 1, 4.6),

-- Tariff 2 (Комфорт)
('Николаев Андрей Павлович', '+7 (912) 345-67-86', 'nikolaev.a@example.com', 14, 'Т678 УК73', 'Toyota Camry', 2, 4.7),
('Фёдорова Екатерина Игоревна', '+7 (912) 345-67-87', 'fedorova.e@example.com', 9, 'У789 ХМ72', 'Hyundai Creta', 2, 4.9),
('Волков Артём Александрович', '+7 (912) 345-67-88', 'volkov.a@example.com', 11, 'Ф890 ОС71', 'Kia Sportage', 2, 4.5),
('Алексеева Мария Дмитриевна', '+7 (912) 345-67-89', 'alekseeva.m@example.com', 7, 'Х901 ТР70', 'Volkswagen Tiguan', 2, 4.3),
('Соколов Павел Олегович', '+7 (912) 345-67-10', 'sokolov.p@example.com', 22, 'Ц012 АВ69', 'Skoda Octavia', 2, 4.6),

-- Tariff 3 (Бизнес)
('Лебедева Анастасия Сергеевна', '+7 (912) 345-67-11', 'lebedeva.a@example.com', 5, 'М123 ЛО68', 'Mercedes E-Class', 3, 4.9),
('Козлов Игорь Вадимович', '+7 (912) 345-67-12', 'kozlov.i@example.com', 16, 'Н234 ПА67', 'BMW 5 Series', 3, 4.8),
('Новикова Виктория Андреевна', '+7 (912) 345-67-13', 'novikova.v@example.com', 10, 'П345 СТ66', 'Audi A6', 3, 4.7),
('Морозов Алексей Николаевич', '+7 (912) 345-67-14', 'morozov.a@example.com', 3, 'Р456 МУ65', 'Lexus ES', 3, 4.9),
('Егорова Дарья Павловна', '+7 (912) 345-67-15', 'egorova.d@example.com', 19, 'С567 ХА64', 'Volvo S90', 3, 4.6),

-- Tariff 4 (Премиум)
('Дмитриев Константин Ильич', '+7 (912) 345-67-16', 'dmitriev.k@example.com', 25, 'У678 ЦП63', 'Mercedes S-Class', 4, 4.9),
('Орлова Алина Максимовна', '+7 (912) 345-67-17', 'orlova.a@example.com', 2, 'Ф789 ШЛ62', 'BMW 7 Series', 4, 5.0),
('Гусев Михаил Андреевич', '+7 (912) 345-67-18', 'gusev.m@example.com', 8, 'Х890 ЩР61', 'Audi A8', 4, 4.8),
('Тихонова Елена Владимировна', '+7 (912) 345-67-19', 'tihonova.e@example.com', 13, 'Ц901 ЭТ60', 'Porsche Panamera', 4, 4.9),
('Фролов Артур Романович', '+7 (912) 345-67-20', 'frolov.a@example.com', 6, 'Щ012 ЮН59', 'Tesla Model S', 4, 4.7);