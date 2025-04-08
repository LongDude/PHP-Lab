SET GLOBAL time_zone = 'Europe/Moscow';
SET NAMES utf8;
ALTER DATABASE php_taxi_service CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
create table if not exists tariffs (
    id serial primary key,
    name varchar(20) not null,
    base_price float default 0 not null,
    base_dist float default 0 not null,
    dist_cost float default 0 not null
);

create table if not exists users (
    id serial primary key,
    name varchar(100) not null,
    phone varchar(20) null unique,
    email varchar(100) not null unique,
    password char(32) not null, -- md 5 at application level
    role varchar(10) default 'client' not null
);

create table if not exists drivers (
    id serial primary key,
    user_id bigint unsigned not null unique,
    intership smallint unsigned default 0 not null check (intership>=0 and intership<=80),
    car_license varchar(15) not null,
    car_brand varchar(50) not null, 
    tariff_id bigint unsigned null,
    rating float default 0 not null check (rating >= 0 and rating <= 5),
    FOREIGN KEY (user_id) REFERENCES users(id),    
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id)    
);

create table if not exists orders (
    id serial primary key,
    from_loc varchar(24) not null, -- Пара (-xxx.xxxxxx;-xxx.xxxxxx) -21 символ
    dest_loc varchar(24) not null,
    distance float default 0 not null,
    price float default 0 not null,
    orderedAt datetime default CURRENT_TIMESTAMP,
    user_id bigint unsigned null,
    driver_id bigint unsigned null,
    tariff_id bigint unsigned null,
    FOREIGN KEY (user_id) references users(id),
    FOREIGN KEY (driver_id) references drivers(id),
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id)
);

-- TRIGGER - new order
DELIMITER ;;
create trigger `new_order` BEFORE INSERT ON `orders` FOR EACH ROW
BEGIN
    SET NEW.tariff_id = (SELECT d.tariff_id from drivers d where d.id = NEW.driver_id LIMIT 1);
    SET NEW.orderedAt = NOW();
    SET NEW.price = ( SELECT (t.base_price + GREATEST(0, NEW.distance - t.base_dist) * t.dist_cost) FROM tariffs t where t.id = NEW.tariff_id );
END ;;
DELIMITER ; 

DELIMITER ;;
CREATE TRIGGER `validate_user` BEFORE INSERT ON drivers FOR EACH ROW
BEGIN
    IF (SELECT (name = '' OR phone = '' OR email = '') FROM users WHERE id = NEW.user_id) 
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'User must have all fields (name, phone, email) set.';
    END IF;
END;;
DELIMITER ;