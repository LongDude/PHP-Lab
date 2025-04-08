SET GLOBAL time_zone = 'Europe/Moscow';
SET NAMES utf8;
ALTER DATABASE php_taxi_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
create table if not exists tariffs (
    id serial primary key,
    name varchar(20) not null,
    base_price float default 0 not null,
    base_dist float default 0 not null,
    base_time float default 0 not null,
    dist_cost float default 0 not null,
    time_cost float default 0 not null
);

create table if not exists drivers (
    id serial primary key,
    name varchar(100) not null,
    phone varchar(20) not null unique,
    email varchar(100) not null unique,
    intership smallint unsigned default 0 not null check (intership>=0 and intership<=80),
    car_license varchar(15) not null,
    car_brand varchar(50) not null, 
    tariff_id bigint unsigned null,
    rating float default 0 not null check (rating >= 0 and rating <= 5),
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id)    
);

create table if not exists orders (
    id serial primary key,
    from_loc varchar(24) not null, -- Пара (-xxx.xxxxxx;-xxx.xxxxxx) -21 символ
    dest_loc varchar(24) not null,
    distance float default 0 not null,
    price float default 0 not null,
    phone varchar(20) null,
    orderedAt datetime default CURRENT_TIMESTAMP,
    deportedAt datetime null,
    deliverAt datetime null,
    canceled bit default 0,
    ratedAs smallint unsigned default 0 not null,
    tariff_id bigint unsigned null,
    driver_id bigint unsigned null,
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id),
    FOREIGN KEY (driver_id) references drivers(id)
);
-- TRIGGER
-- DELIMITER ;;
-- create trigger `ordered_at` BEFORE INSERT ON `orders` FOR EACH ROW
-- BEGIN
--     SET NEW.orderedAt = NOW();
-- END ;;
-- DELIMITER ; 


-- TRIGGER - new order
DELIMITER ;;
create trigger `new_order` BEFORE INSERT ON `orders` FOR EACH ROW
BEGIN
    SET NEW.tariff_id = (SELECT d.tariff_id from drivers d where d.id = NEW.driver_id LIMIT 1);
    SET NEW.orderedAt = NOW();
    SET NEW.price = ( SELECT (t.base_price + GREATEST(0, NEW.distance - t.base_dist) * t.dist_cost) FROM tariffs t where t.id = NEW.tariff_id );
END ;;
DELIMITER ; 
