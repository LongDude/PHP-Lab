SET GLOBAL time_zone = '+8:00';

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
    name varchar(50) not null,
    phone varchar(20) not null unique,
    email varchar(100) not null unique,
    intership smallint unsigned default 0 not null check (intership>=0 and intership<=80),
    car_license varchar(15) not null,
    car_brand varchar(50) not null, 
    tariff_id bigint unsigned null,
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id)    
);

create table if not exists orders (
    id serial primary key,
    from_loc varchar(21) not null, -- Пара (-xxx.xxxxxx;-xxx.xxxxxx) -21 символ
    dest_loc varchar(21) not null,
    distance float default 0 not null,
    price float default 0 not null,
    phone varchar(20) not null,
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