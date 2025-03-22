-- FUN FACT: обычному пользователю прав не хватит новую БД инициализировать. Считай скрипт вручную надо запускать от админа
create table if not exists drivers (
    id serial primary key,
    name varchar(100) not null,
    phone varchar(20) not null,
    email varchar(100) not null,
    sex varchar(30) not null,
    intership varchar(3) not null,
    car_registration varchar(13) not null,
    tariffs integer not null
);